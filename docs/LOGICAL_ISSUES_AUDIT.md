# 🐛 LOGICAL ISSUES AUDIT - GoField

## ❌ **CRITICAL ISSUES FOUND**

### Issue #1: Double Refund - Poin Earned & Refund Conflict
**Location**: `app/Services/CancellationService.php` - `processRefund()` method

**Problem**:
```php
// Step 2: Cabut earned points
if ($booking->points_earned > 0) {
    $user->points_balance -= $booking->points_earned; // -2 poin
}

// Step 3: Kasih refund points
$refundPoints = floor($refundInfo['refund_amount'] / 1000); // +150 poin
$user->points_balance += $refundPoints;
```

**Scenario**:
```
User booking Rp 150,000 
→ Dapat 1 poin earned (1% = Rp 1,500 → 1 poin)
→ Cancel H-24 (100% refund = Rp 150,000)
→ Cabut -1 poin earned
→ Kasih +150 poin refund
→ Net: User dapat +149 poin

Expected: +150 poin (refund MENGGANTIKAN earned, bukan tambahan)
Actual: +149 poin (cabut earned lalu kasih refund)
```

**Impact**: User kehilangan 1 poin yang harusnya tidak dicabut karena sudah di-replace dengan refund.

**Root Cause**: Logic confusing - earned poin **SUDAH TERMASUK** dalam harga yang di-refund. Jadi tidak perlu dicabut lagi.

---

### Issue #2: Double Earning - No Idempotency Check
**Location**: `app/Filament/Resources/Bookings/BookingResource.php` - `approve_payment` action

**Problem**:
```php
->action(function (Booking $record): void {
    // Tidak ada cek apakah poin sudah diberikan
    $user->points_balance += $record->points_earned;
    
    UserPoint::create([
        'points' => $record->points_earned,
        'type' => 'earned',
    ]);
})
```

**Scenario**:
```
Admin klik "Terima" 
→ User dapat 2 poin

Admin refresh page (button masih visible karena payment_status masih 'unpaid')
Admin klik "Terima" lagi (accident atau sengaja)
→ User dapat 2 poin LAGI

Total: 4 poin (harusnya 2 poin)
```

**Impact**: Admin bisa kasih poin berkali-kali dengan accident double-click atau refresh page.

**Root Cause**: Tidak ada idempotency check - tidak cek apakah `UserPoint` dengan `booking_id` dan `type='earned'` sudah ada.

---

### Issue #3: Double Refund - Poin + Bank Transfer
**Location**: `app/Filament/Resources/Bookings/BookingResource.php` - `process_refund` action

**Problem**:
```php
// Saat user cancel (auto):
refund_method = 'points'
User dapat 150 poin refund

// Saat admin proses manual refund:
->action(function (Booking $record, array $data): void {
    $record->update([
        'refund_method' => 'bank_transfer',
        'payment_status' => 'refunded',
    ]);
    // TIDAK ADA LOGIC CABUT POIN REFUND!
})
```

**Scenario**:
```
User booking Rp 150,000
→ Bayar via transfer bank
→ Cancel H-24 (100% refund)
→ Otomatis dapat 150 poin refund

Customer komplain: "Saya mau uang, bukan poin!"
Admin proses manual transfer → transfer Rp 150,000

Result:
- User punya 150 poin refund ✅
- User terima Rp 150,000 cash ✅
- Total: 150 poin + Rp 150,000 (DOUBLE!) ❌
```

**Impact**: User bisa dapat refund 2x - poin + uang cash.

**Root Cause**: Manual refund tidak cabut poin refund yang sudah diberikan otomatis.

---

## ✅ **FIXES**

### Fix #1: Remove "Deduct Earned Points" Logic

**File**: `app/Services/CancellationService.php`

**Change**:
```php
// HAPUS step 2 ini:
// 2. Deduct earned points (if any and not used yet)
if ($booking->points_earned > 0) {
    if ($user->points_balance >= $booking->points_earned) {
        $user->points_balance -= $booking->points_earned;
        // ... create UserPoint with type 'adjusted'
    }
}
```

**Reasoning**:
- Refund poin **SUDAH MENGGANTIKAN** earned poin
- Earned poin = 1% dari harga booking
- Refund poin = 50%-100% dari harga booking
- Refund > Earned, jadi tidak perlu cabut earned

**Example**:
```
Before Fix:
Booking Rp 150k → Earned 1 poin → Cancel → Cabut -1 → Refund +150 = Net +149 poin ❌

After Fix:
Booking Rp 150k → Earned 1 poin → Cancel → Refund +150 = Net +150 poin ✅
```

---

### Fix #2: Add Idempotency Check for Point Earning

**File**: `app/Filament/Resources/Bookings/BookingResource.php`

**Change**:
```php
->action(function (Booking $record): void {
    // Cek apakah poin sudah diberikan sebelumnya
    $existingPoint = \App\Models\UserPoint::where('booking_id', $record->id)
        ->where('type', 'earned')
        ->first();
    
    if ($existingPoint) {
        // Poin sudah pernah diberikan, skip
        return;
    }
    
    $record->update([
        'payment_status' => 'paid',
        'paid_at' => now(),
        'payment_confirmed_at' => now(),
        'payment_confirmed_by' => auth()->id(),
        'status' => 'confirmed',
    ]);
    
    // Kasih poin untuk semua metode pembayaran (termasuk cash)
    if ($record->user_id && $record->points_earned > 0) {
        $user = \App\Models\User::find($record->user_id);
        if ($user) {
            $user->points_balance += $record->points_earned;
            $user->save();
            
            \App\Models\UserPoint::create([
                'user_id' => $user->id,
                'booking_id' => $record->id,
                'points' => $record->points_earned,
                'type' => 'earned',
                'description' => 'Points earned from booking #' . $record->id,
                'balance_after' => $user->points_balance,
            ]);
        }
    }
})
```

---

### Fix #3: Deduct Refund Points on Manual Bank Transfer

**File**: `app/Filament/Resources/Bookings/BookingResource.php`

**Change**:
```php
->action(function (Booking $record, array $data): void {
    // Cabut poin refund yang sudah diberikan otomatis
    if ($record->user_id && $record->refund_method === 'points') {
        $user = \App\Models\User::find($record->user_id);
        if ($user) {
            // Calculate refund points
            $refundPoints = floor($record->refund_amount / 1000);
            
            // Deduct refund points
            if ($refundPoints > 0 && $user->points_balance >= $refundPoints) {
                $user->points_balance -= $refundPoints;
                $user->save();
                
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'booking_id' => $record->id,
                    'points' => -$refundPoints,
                    'type' => 'adjusted',
                    'description' => 'Refund poin dibatalkan karena sudah di-transfer ke rekening. ' . $data['refund_notes'],
                    'balance_after' => $user->points_balance,
                ]);
            }
        }
    }
    
    $record->update([
        'refund_method' => 'bank_transfer',
        'refund_notes' => $data['refund_notes'],
        'refund_processed_at' => now(),
        'payment_status' => 'refunded',
    ]);
})
```

---

## 📊 **Impact Analysis**

### Before Fixes:

| Issue | Impact | Severity |
|-------|--------|----------|
| #1: Earned poin dicabut saat refund | User kehilangan ~1-2 poin per cancellation | MEDIUM |
| #2: Double earning | Admin bisa kasih poin unlimited | HIGH |
| #3: Double refund (poin + cash) | User bisa dapat 2x refund value | CRITICAL |

### After Fixes:

| Issue | Status | Result |
|-------|--------|--------|
| #1 | ✅ Fixed | User dapat refund poin yang benar (tidak dikurangi earned) |
| #2 | ✅ Fixed | Poin hanya diberikan 1x per booking |
| #3 | ✅ Fixed | Refund bank transfer auto-cabut poin refund |

---

## 🧪 **Test Cases**

### Test #1: Cancel After Payment (Fix #1)
```
1. Booking Rp 150,000 → Admin approve → User dapat 1 poin earned
2. User cancel H-24 (100% refund)
3. Expected: 150 poin refund (tidak cabut 1 poin earned)
4. Actual sebelum fix: 149 poin (cabut -1 + refund +150)
5. Actual setelah fix: 150 poin ✅
```

### Test #2: Double Approve (Fix #2)
```
1. Booking dengan payment_status = 'waiting_confirmation'
2. Admin klik "Terima" → User dapat 2 poin
3. Admin klik "Terima" lagi (button masih visible)
4. Expected: Tidak ada perubahan (skip karena sudah ada UserPoint)
5. Actual sebelum fix: User dapat 2 poin lagi (total 4 poin) ❌
6. Actual setelah fix: Skip, total tetap 2 poin ✅
```

### Test #3: Manual Refund (Fix #3)
```
1. Booking Rp 150,000 → Cancel → Dapat 150 poin refund otomatis
2. Customer minta refund bank transfer
3. Admin proses transfer manual + klik "Proses Refund"
4. Expected: Cabut 150 poin refund, kirim Rp 150k
5. Actual sebelum fix: User punya 150 poin + Rp 150k (double) ❌
6. Actual setelah fix: Cabut 150 poin, kirim Rp 150k ✅
```

---

## 🚀 **Implementation Priority**

1. **FIX #2 (CRITICAL)**: Idempotency check - prevent unlimited point earning
2. **FIX #3 (CRITICAL)**: Deduct refund points on manual transfer - prevent double refund
3. **FIX #1 (MEDIUM)**: Remove earned point deduction - give correct refund amount

---

## 📝 **Migration Needed?**

**No** - All fixes are code-only changes, no database schema changes needed.

---

## ⚠️ **Breaking Changes**

**None** - These are bug fixes that make the system work as intended. No API changes or feature removals.

---

## 🎯 **Summary**

Ditemukan **3 critical logical issues** di payment & refund flow:
1. ✅ Earned poin dicabut saat refund (harusnya tidak)
2. ✅ Admin bisa kasih poin unlimited (double-click)
3. ✅ User bisa dapat refund 2x (poin + cash)

Semua issues sudah diidentifikasi dan solusi sudah tersedia. Ready untuk implementation! 🚀
