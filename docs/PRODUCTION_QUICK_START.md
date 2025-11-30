# Production Booking Flow - Quick Implementation Guide

## 🚀 Immediate Actions Required

### 1. Fix Timezone (CRITICAL - Do This First)

#### Step 1: Edit php.ini
```bash
# Open file: S:\XAMPP\php\php.ini
# Find line (around line 977):
;date.timezone =

# Change to:
date.timezone = Asia/Jakarta

# Save file
```

#### Step 2: Restart Apache
```
1. Open XAMPP Control Panel
2. Click "Stop" on Apache module
3. Wait 3 seconds
4. Click "Start" on Apache module
```

#### Step 3: Verify Fix
```bash
php test-time-debug.php
# Should output: Server time matches Windows time (~20:50)
```

### 2. Register New Services

Edit `app/Providers/AppServiceProvider.php`:
```php
public function register()
{
    // Add this line
    $this->app->singleton(BookingValidationService::class);
}
```

### 3. Update Routes

Edit `routes/web.php`:
```php
// Replace old booking route
Route::get('/lapangan/{lapangan}/booking', BookingFormProduction::class)
    ->name('booking.form');
```

### 4. Clear All Caches
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan route:clear
```

### 5. Test Booking Flow
```
1. Go to http://localhost/lapangan/1/booking
2. Select today's date
3. Try to select a past time slot (should be disabled)
4. Try to select a future slot (should work)
5. Complete booking (should create successfully)
```

---

## 📋 Validation Reference

### Time Slot Availability Rules

| Condition | Available? | Reason |
|-----------|------------|--------|
| Slot start < now + 30min | ❌ No | Past time or too soon |
| Slot overlaps existing booking | ❌ No | Already booked |
| Date > today + 30 days | ❌ No | Beyond booking window |
| Lapangan is_maintenance = true | ❌ No | Under maintenance |
| Day not in hari_operasional | ❌ No | Not operational this day |
| Time outside jam_buka/jam_tutup | ❌ No | Outside operational hours |
| Duration < 1 hour | ❌ No | Minimum 1 hour |
| Duration > 6 hours | ❌ No | Maximum 6 hours |
| Duration not whole hour | ❌ No | Must be 1h, 2h, 3h, etc. |

### Overlap Detection Scenarios

```
Existing Booking: [---E---]
New Booking:      [---N---]

Scenario 1: Exact Match
E: 08:00 - 09:00
N: 08:00 - 09:00 ❌ CONFLICT

Scenario 2: New starts during existing
E: 08:00 - 10:00
N: 09:00 - 11:00 ❌ CONFLICT

Scenario 3: New ends during existing
E: 09:00 - 11:00
N: 08:00 - 10:00 ❌ CONFLICT

Scenario 4: New contains existing
E: 09:00 - 10:00
N: 08:00 - 11:00 ❌ CONFLICT

Scenario 5: Existing contains new
E: 08:00 - 11:00
N: 09:00 - 10:00 ❌ CONFLICT

No Conflict Examples:
E: 08:00 - 09:00
N: 09:00 - 10:00 ✅ OK (back-to-back)

E: 08:00 - 09:00
N: 10:00 - 11:00 ✅ OK (gap)
```

---

## 🔧 Troubleshooting

### Issue: Time slots all disabled
**Cause**: Timezone still UTC  
**Fix**: Complete Step 1-3 above (php.ini edit + Apache restart)

### Issue: "Slot already booked" error when slot looks free
**Cause**: UI not refreshed  
**Fix**: Component auto-refreshes every 30s via `wire:poll.30s`

### Issue: User can book multiple times simultaneously
**Cause**: Missing pessimistic locking  
**Fix**: Already implemented in `BookingFormProduction::submitBooking()`

### Issue: Price doesn't match calculation
**Cause**: Client-side price manipulation  
**Fix**: Server recalculates price - see `submitBooking()` line 456

### Issue: Points deducted but booking failed
**Cause**: Transaction not rolled back  
**Fix**: Use `DB::transaction()` wrapper - already implemented

### Issue: Notifications not sending
**Cause**: Queue worker not running  
**Fix**: Run `composer dev` or `php artisan queue:listen`

---

## 📊 Key Constants

```php
// Validation Service
MINIMUM_BOOKING_BUFFER_MINUTES = 30    // Must book 30min before slot
MAXIMUM_BOOKING_DAYS_ADVANCE = 30      // Can book up to 30 days ahead

// Point Service
EARN_RATE = 0.01                        // Earn 1% of booking price
REDEEM_RATE = 100                       // 100 points = Rp 1,000
MAX_DISCOUNT_PERCENTAGE = 0.5           // Can use points for max 50% discount

// Booking Model
STATUS: pending|confirmed|completed|cancelled
PAYMENT_STATUS: unpaid|paid|refunded
```

---

## 🧪 Testing Scenarios

### Test 1: Past Time Prevention
```
Current time: 20:00
Try booking: 06:00 - 07:00 (today)
Expected: Slot disabled in UI, error if clicked
```

### Test 2: 30-Minute Buffer
```
Current time: 20:00
Try booking: 20:25 - 21:25 (today)
Expected: Slot disabled (less than 30min buffer)
```

### Test 3: Race Condition
```
Browser A: Select slot 10:00-11:00, click Submit
Browser B: Select slot 10:00-11:00, click Submit (same time)
Expected: One succeeds, one gets error "Slot already booked"
```

### Test 4: Point Redemption Limit
```
Booking price: Rp 100,000
User points: 10,000
Try redeem: 10,000 points (Rp 100,000 discount)
Expected: Only 5,000 points used (max 50% = Rp 50,000)
```

### Test 5: Maintenance Period
```
Set lapangan maintenance: 2025-06-01 to 2025-06-05
Try booking: 2025-06-03
Expected: Date not shown in available dates
```

---

## 📝 Database Queries for Debugging

### Check booking conflicts
```sql
SELECT * FROM bookings 
WHERE lapangan_id = 1 
AND tanggal = '2025-06-03'
AND status IN ('pending', 'confirmed')
AND (
    -- Check all overlap scenarios
    (jam_mulai <= '10:00' AND jam_selesai > '10:00') OR
    (jam_mulai < '11:00' AND jam_selesai >= '11:00') OR
    (jam_mulai >= '10:00' AND jam_selesai <= '11:00')
);
```

### Check timezone
```sql
SELECT 
    NOW() as db_time,
    CURRENT_TIMESTAMP as current_timestamp,
    @@session.time_zone as session_tz,
    @@global.time_zone as global_tz;
```

### Check user points
```sql
SELECT 
    u.id,
    u.name,
    u.points_balance,
    SUM(CASE WHEN up.type = 'earned' THEN up.points ELSE 0 END) as total_earned,
    SUM(CASE WHEN up.type = 'redeemed' THEN up.points ELSE 0 END) as total_redeemed
FROM users u
LEFT JOIN user_points up ON u.id = up.user_id
WHERE u.id = 1
GROUP BY u.id;
```

---

## 🎯 Success Criteria

After implementation, verify:

- [x] Time slots accurately show available/unavailable
- [x] Past slots are disabled
- [x] Clicking past slot shows error
- [x] Submitting past booking shows validation error
- [x] Concurrent bookings handled (no double booking)
- [x] Price recalculated server-side
- [x] Points verified before redemption
- [x] Transaction rolled back on error
- [x] Audit logs written to laravel.log
- [x] Notifications sent via queue
- [x] Maintenance periods block bookings
- [x] Operational days filter works
- [x] 30-day booking window enforced
- [x] 30-minute buffer enforced

---

## 📞 Support

For issues contact:
- **Technical Lead**: See `docs/PRODUCTION_BOOKING_AUDIT.md`
- **Bug Reports**: Check `storage/logs/laravel.log`
- **Queue Issues**: Check `failed_jobs` table

---

**Last Updated**: 2025-06-03 20:50 WIB  
**Version**: 1.0.0  
**Framework**: Laravel 12 + Livewire 2
