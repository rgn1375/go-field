# ⚡ Quick Start Guide - User Authentication & Point System

**Ready to test in 5 minutes!** 🚀

---

## 🎯 Prerequisites Checklist

```powershell
# 1. Ensure XAMPP MySQL/MariaDB is running
# 2. Navigate to project root
cd s:\xampp\htdocs\BookingLapang

# 3. Fresh database setup
php artisan migrate:fresh --seed

# 4. Start all services (single command)
composer dev

# OR manually:
# Terminal 1: php artisan serve
# Terminal 2: npm run dev  
# Terminal 3: php artisan queue:listen
```

**Expected Output After Seeding:**
```
✅ Database seeding completed successfully!
📊 Created:
   - 1 Admin user (admin@admin.com / admin123)
   - 3 Test users:
     • user@test.com (0 points, no bookings)
     • regular@test.com (500 points, 3 bookings)
     • vip@test.com (2000 points, 4 bookings)
   - 6 Sports facilities
   - 8 Sample bookings
```

---

## 🧪 5-Minute Test Flow

### Test 1: Guest Booking (2 minutes)
**Purpose:** Verify backward compatibility

1. Open: `http://localhost:8000`
2. Click any lapangan card → Detail page
3. Select tomorrow's date, time 10:00-11:00
4. Fill guest info:
   - Nama: "Test Guest"
   - Email: "guest@test.com"
   - HP: "081234567890"
5. **Verify:** No points section visible
6. Click "Booking Sekarang"
7. **Expected:** Success message, no points earned

✅ **Pass Criteria:** Guest booking works without authentication

---

### Test 2: User Registration (1 minute)
**Purpose:** Test new account creation

1. Click "Register" button (top right)
2. Fill form:
   - Name: "Quick Test"
   - Email: "quicktest@test.com"
   - Password: "password"
3. Submit
4. **Expected:** 
   - Logged in automatically
   - Redirected to homepage
   - Navbar shows "QT" avatar with "Quick Test"
   - Points display: "0 poin"

✅ **Pass Criteria:** Registration successful, auto-login works

---

### Test 3: Point Earning (1 minute)
**Purpose:** Verify 1% point calculation

1. **Logged in as** quicktest@test.com (from Test 2)
2. Book **Futsal Premium A** (Rp 300,000)
3. Select tomorrow, 14:00-15:00
4. **Verify booking summary shows:**
   - Harga: Rp 300.000
   - Total Bayar: Rp 300.000
   - **+3.000 poin** dari booking ini
5. Submit booking
6. **Check navbar:** Points updated to "3.000 poin"

✅ **Pass Criteria:** Points earned = floor(300000 * 0.01) = 3000

---

### Test 4: Point Redemption (2 minutes)
**Purpose:** Test discount calculation

1. **Login as** regular@test.com / password (500 points)
2. Book **Badminton Premium** (Rp 100,000)
3. Select tomorrow, 16:00-17:00
4. **Toggle:** "Gunakan Poin untuk Diskon" (yellow section appears)
5. **Verify:**
   - Shows "Poin Tersedia: 500 poin"
   - Slider max = 500
6. **Enter:** 200 points
7. **Verify live updates (no page refresh):**
   - Discount: "-Rp 2.000" (green text)
   - Booking Summary:
     - Harga: Rp 100.000
     - Diskon Poin: -Rp 2.000
     - Total Bayar: **Rp 98.000**
8. Submit booking
9. **Check navbar:** Points reduced to "300 poin" (500 - 200)

✅ **Pass Criteria:** 200 pts = Rp 2,000 discount, balance updated

---

### Test 5: Dashboard & Cancellation (1 minute)
**Purpose:** Test booking management

1. **Still logged in as** regular@test.com
2. **Click** avatar dropdown → "Dashboard"
3. **Verify 3 tabs:**
   - **Mendatang:** Shows upcoming bookings with Cancel button
   - **Selesai:** Shows past completed bookings
   - **Dibatalkan:** Shows cancelled bookings
4. **Click** "Batalkan Booking" on upcoming booking
5. **Confirm** cancellation
6. **Expected:**
   - Success message
   - Booking moved to "Dibatalkan" tab
   - Points refunded (check navbar)

✅ **Pass Criteria:** Cancellation works, points refunded

---

### Test 6: Admin Panel (1 minute)
**Purpose:** Verify admin user management

1. **Logout** → Login as admin@admin.com / admin123
2. **Navigate to:** `/admin` or click "Admin Panel"
3. **Click** "Users" menu
4. **Verify table shows:**
   - VIP User: 2000 pts
   - Regular User: 500 pts (or updated balance)
   - Quick Test: 3000 pts
5. **Click** "⋮" actions on Quick Test → "Adjust Points"
6. **Fill:**
   - Points: **500**
   - Reason: "Welcome bonus"
7. **Submit**
8. **Verify:** 
   - Success notification
   - Balance updated to 3500 pts in table

✅ **Pass Criteria:** Manual adjustment works, recorded in history

---

## 🎯 Quick Verification Commands

```powershell
# Check all users and points
php artisan tinker
>>> DB::table('users')->select('name', 'email', 'points_balance')->get();

# Check point transactions
>>> DB::table('user_points')->orderBy('id', 'desc')->limit(5)->get(['user_id', 'type', 'points', 'balance_after', 'description']);

# Check bookings with points
>>> DB::table('bookings')->whereNotNull('user_id')->get(['id', 'user_id', 'tanggal', 'status', 'points_earned', 'points_redeemed']);

# Exit tinker
>>> exit
```

---

## 🐛 Troubleshooting

### Issue: "Class 'PointService' not found"
```powershell
composer dump-autoload
```

### Issue: Routes not working
```powershell
php artisan route:clear
php artisan optimize:clear
```

### Issue: Livewire not updating
```powershell
php artisan livewire:delete-stubs
npm run build
```

### Issue: Migrations already exist
```powershell
php artisan migrate:fresh --seed
# This drops all tables and recreates them
```

### Issue: Queue jobs not processing
```powershell
# Terminal 3 (separate window):
php artisan queue:listen --verbose
# Keep this running during testing
```

---

## 📋 Test User Credentials

| Email | Password | Points | Purpose |
|-------|----------|--------|---------|
| `admin@admin.com` | `admin123` | 0 | Admin testing |
| `user@test.com` | `password` | 0 | New user |
| `regular@test.com` | `password` | 500 | Regular user |
| `vip@test.com` | `password` | 2000 | Max redemption |

---

## ✅ Success Criteria Summary

After completing all 6 tests, you should have:

- ✅ Guest booking working (no auth required)
- ✅ User registration and auto-login
- ✅ Points earned (1% of booking price)
- ✅ Points redeemed (100 pts = Rp 1,000)
- ✅ Live discount calculation (no page refresh)
- ✅ Dashboard with 3 tabs (upcoming/past/cancelled)
- ✅ Booking cancellation with point refund
- ✅ Admin point adjustment with reason logging
- ✅ Navbar showing real-time point balance
- ✅ All transactions logged in database

---

## 📚 Next Steps

**If all tests pass:**
1. Read full testing guide: `AUTHENTICATION_TESTING_GUIDE.md`
2. Review feature summary: `AUTHENTICATION_FEATURE_SUMMARY.md`
3. Check point history in Profile page
4. Test edge cases (insufficient points, max redemption)
5. Deploy to staging environment

**If tests fail:**
1. Check console errors (F12 in browser)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify queue worker is running
4. Ensure Vite dev server is running (`npm run dev`)
5. Check database connection in `.env`

---

## 🚀 Production Deployment Checklist

Before deploying to production:

- [ ] Change `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `npm run build` (not `npm run dev`)
- [ ] Configure queue worker as systemd service
- [ ] Set up Laravel scheduler cron job
- [ ] Configure mail driver (not `log`)
- [ ] Test notification sending (Email + WhatsApp)
- [ ] Backup database before migration
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Monitor error logs after deployment

---

**Total Testing Time:** ~7-10 minutes  
**Difficulty Level:** Easy ⭐  
**Prerequisites:** Basic Laravel knowledge

**Happy Testing!** 🎉
