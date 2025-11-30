# 🧪 Authentication & Point System Testing Guide

**Version:** 1.0  
**Feature:** User Authentication with Loyalty Point System  
**Last Updated:** January 2025

## 📋 Overview

This guide provides comprehensive instructions for testing the newly implemented user authentication and point reward system in the SportBooking application.

## 🎯 Test Objectives

1. Verify Laravel Breeze authentication flow
2. Test point earning mechanism (1% of booking price)
3. Test point redemption (100 pts = Rp 1,000, max 50% discount)
4. Validate user dashboard functionality
5. Test profile management with point history
6. Verify admin user management in Filament
7. Ensure guest booking still works (backward compatibility)

---

## 🚀 Prerequisites

### 1. Fresh Database Setup

```powershell
# From project root: s:\xampp\htdocs\BookingLapang
php artisan migrate:fresh --seed
php artisan storage:link
```

**Expected Output:**
```
✅ Database seeding completed successfully!
📊 Created:
   - 1 Admin user (admin@admin.com / admin123)
   - 3 Test users:
     • user@test.com (0 points, no bookings)
     • regular@test.com (500 points, 3 bookings)
     • vip@test.com (2000 points, 4 bookings)
   - 6 Sports facilities (Futsal, Basket, Volly, Badminton, Tennis)
   - 8 Sample bookings (past, upcoming, cancelled)
   - Point transaction history with earn/redeem/adjust examples
```

### 2. Start Development Server

```powershell
composer dev
# OR manually:
php artisan serve
php artisan queue:listen
npm run dev
```

### 3. Test User Credentials

| Email | Password | Points | Purpose |
|-------|----------|--------|---------|
| `admin@admin.com` | `admin123` | 0 | Admin panel testing |
| `user@test.com` | `password` | 0 | New user flow |
| `regular@test.com` | `password` | 500 | Regular user with history |
| `vip@test.com` | `password` | 2000 | VIP user with max redemption |

---

## 🧪 Test Cases

### Test Suite 1: Guest User Experience (Backward Compatibility)

**Purpose:** Ensure existing guest booking functionality still works

#### TC1.1: Guest Booking Creation
1. **Navigate** to homepage: `http://localhost:8000`
2. **Verify** navbar shows "Login" and "Register" buttons
3. **Click** any lapangan card → Detail page
4. **Fill booking form** (guest data):
   - Pilih Tanggal: Tomorrow
   - Pilih Jam: 10:00 - 11:00
   - Nama: "Guest Tester"
   - Email: "guest.test@example.com"
   - Nomor HP: "081234567890"
5. **Verify** points section is NOT visible
6. **Click** "Booking Sekarang"

**Expected Results:**
- ✅ Booking created with status "confirmed"
- ✅ No points earned/redeemed (both = 0)
- ✅ `user_id` is NULL
- ✅ Notification sent to email and WhatsApp
- ✅ Success message displayed

---

### Test Suite 2: Authentication Flow

#### TC2.1: User Registration
1. **Click** "Register" button in navbar
2. **Fill registration form**:
   - Name: "Test New User"
   - Email: "newuser@test.com"
   - Password: "password"
   - Confirm Password: "password"
3. **Submit** form

**Expected Results:**
- ✅ User created with `points_balance = 0`
- ✅ Automatically logged in
- ✅ Redirected to homepage
- ✅ Navbar shows user avatar with name
- ✅ Avatar displays initials "TN"

#### TC2.2: User Login
1. **Logout** (dropdown menu → Logout)
2. **Click** "Login" button
3. **Enter credentials**:
   - Email: "regular@test.com"
   - Password: "password"
4. **Check** "Remember Me"
5. **Submit**

**Expected Results:**
- ✅ Successfully logged in
- ✅ Redirected to homepage
- ✅ Navbar shows "Regular User" with "500 poin"
- ✅ Dropdown menu shows: Dashboard, Profile, Logout

#### TC2.3: Password Reset
1. **Logout** and go to login page
2. **Click** "Forgot your password?"
3. **Enter** email: "regular@test.com"
4. **Check** mailbox (or logs if using log driver)

**Expected Results:**
- ✅ Password reset email sent
- ✅ Email contains reset link
- ✅ Link expires after 60 minutes

---

### Test Suite 3: Point Earning System

#### TC3.1: New Booking Without Point Redemption
1. **Login** as `user@test.com` (0 points)
2. **Navigate** to Futsal Premium A (Rp 300,000)
3. **Fill booking form**:
   - Tanggal: 2 days from now
   - Jam: 14:00 - 15:00
4. **Verify** booking summary shows:
   - Harga: Rp 300,000
   - No discount line
   - Total Bayar: Rp 300,000
   - "**+3.000 poin** dari booking ini!" (1% of 300,000)
5. **Submit** booking

**Expected Results:**
- ✅ Booking created with `points_earned = 3000`
- ✅ User's `points_balance` updated to 3000
- ✅ `user_points` record created:
  - `type = 'earned'`
  - `points = 3000`
  - `balance_after = 3000`
  - `description = "Points earned from booking X"`

#### TC3.2: Verify Point Calculation Accuracy
Test different price points to verify 1% calculation:

| Lapangan | Price | Expected Points | Formula |
|----------|-------|-----------------|---------|
| Futsal Premium A | Rp 300,000 | 3,000 | floor(300000 * 0.01) |
| Basket Indoor | Rp 350,000 | 3,500 | floor(350000 * 0.01) |
| Volly Outdoor | Rp 150,000 | 1,500 | floor(150000 * 0.01) |
| Badminton Premium | Rp 100,000 | 1,000 | floor(100000 * 0.01) |

**Test Method:** Create bookings for each lapangan, check `user_points` table

---

### Test Suite 4: Point Redemption System

#### TC4.1: Basic Point Redemption
1. **Login** as `regular@test.com` (500 points)
2. **Book** Badminton Premium (Rp 100,000)
3. **Toggle** "Gunakan Poin untuk Diskon"
4. **Verify** points section shows:
   - "Poin Tersedia: **500 poin**"
   - Slider max = 500 (not 5000, limited by balance)
5. **Enter** 200 points
6. **Verify** real-time updates:
   - Discount display: "**-Rp 2.000**" (200/100 * 1000)
   - Booking summary:
     - Harga: Rp 100,000
     - Diskon Poin: -Rp 2,000
     - Total Bayar: **Rp 98,000**
     - Points to earn: +1,000 (1% of original price)
7. **Submit** booking

**Expected Results:**
- ✅ Booking created with:
  - `points_redeemed = 200`
  - `points_earned = 1000`
- ✅ User's `points_balance` = 500 - 200 = 300 (immediately)
- ✅ `user_points` records created:
  1. Redemption: `type='redeemed'`, `points=-200`, `balance_after=300`
  2. Earning: `type='earned'`, `points=1000`, `balance_after=1300` (after completion)
- ✅ Notification mentions discounted price

#### TC4.2: Maximum Redemption Limit (50% Rule)
1. **Login** as `vip@test.com` (2000 points = Rp 20,000 value)
2. **Book** Basket Indoor (Rp 350,000)
3. **Toggle** "Gunakan Poin untuk Diskon"
4. **Click** "Gunakan Maksimal" button

**Expected Results:**
- ✅ `pointsToRedeem` auto-set to **1750 points**
- ✅ Calculation: 50% of 350,000 = 175,000 → need 17,500 points, but user only has 2000
- ✅ Actually: 50% of 350,000 = 175,000 / 1000 * 100 = **1750 points** (17.5% of price)
- ✅ Discount: Rp 17,500
- ✅ Final price: Rp 332,500
- ✅ Cannot manually enter > 1750 points

#### TC4.3: Insufficient Points Validation
1. **Login** as `user@test.com` (3000 points from TC3.1)
2. **Book** Futsal Premium A (Rp 300,000)
3. **Try to redeem** 5000 points (more than balance)

**Expected Results:**
- ✅ Input capped at 3000 (user's max balance)
- ✅ Slider max attribute = 3000
- ✅ Cannot type value > 3000

---

### Test Suite 5: User Dashboard

#### TC5.1: Dashboard Tabs - Upcoming Bookings
1. **Login** as `regular@test.com`
2. **Navigate** to Dashboard (navbar dropdown → Dashboard)
3. **Verify** "Mendatang" tab is active by default
4. **Check** displayed bookings:
   - Should show bookings with `status='confirmed'` AND date >= today

**Expected Data (from seeder):**
- ✅ Basket Indoor booking (3 days from today, 15:00-16:00)
  - Points redeemed: 200 pts
  - Points earned: 3,500 pts
  - Cancel button visible

**UI Checks:**
- ✅ Points balance card shows "500 poin = Rp 5.000"
- ✅ Booking card has orange "confirmed" badge
- ✅ Shows lapangan title, date, time, price
- ✅ "Batalkan Booking" button present

#### TC5.2: Dashboard Tabs - Past Bookings
1. **Click** "Selesai" tab
2. **Verify** shows bookings with:
   - `status='completed'` OR
   - Past date/time with `status='confirmed'`

**Expected Data:**
- ✅ Futsal Premium A booking (7 days ago, 10:00-11:00)
  - Status: green "completed" badge
  - Points earned: 3,000 pts displayed
  - No cancel button

#### TC5.3: Dashboard Tabs - Cancelled Bookings
1. **Click** "Dibatalkan" tab
2. **Verify** shows bookings with `status='cancelled'`

**Expected Data:**
- ✅ Badminton Premium booking (5 days from today)
  - Status: red "cancelled" badge
  - Shows refunded points indicator
  - No cancel button

#### TC5.4: User-Initiated Cancellation
1. **Go to** "Mendatang" tab
2. **Click** "Batalkan Booking" on upcoming booking
3. **Confirm** cancellation modal

**Expected Results:**
- ✅ Booking status changed to `cancelled`
- ✅ Points refunded:
  - If 200 points were redeemed → `user_points` record created:
    - `type='earned'`, `points=200`, `description='Points refunded from cancelled booking X'`
  - `points_balance` updated: 500 + 200 = 700
- ✅ Cancellation notification sent (email + WhatsApp)
- ✅ Success message: "Booking berhasil dibatalkan"
- ✅ Booking moved to "Dibatalkan" tab

---

### Test Suite 6: Profile & Point History

#### TC6.1: Profile Page View
1. **Navigate** to Profile (navbar dropdown → Profile)
2. **Verify** sections present:
   - Points balance card (gradient orange)
   - Points history table
   - Personal information form
   - Password update form
   - Account deletion section

**Points Balance Card:**
- ✅ Shows large point number
- ✅ Shows rupiah equivalent
- ✅ Gradient background
- ✅ Star icon

#### TC6.2: Points History Table
1. **Scroll to** "Riwayat Poin" section
2. **Verify** table columns:
   - Tanggal (date formatted)
   - Deskripsi (transaction description)
   - Poin (signed number with color)
   - Saldo (balance after transaction)

**For regular@test.com, expected transactions (last 10):**
1. Admin adjustment: -2,300 pts (red, negative)
2. Booking X earned: +3,500 pts (green, positive)
3. Booking X redeemed: -200 pts (red, negative)
4. Booking Y refund: +300 pts (green, positive)
5. Booking Y redeemed: -300 pts (red, negative)
6. Booking Z earned: +3,000 pts (green, positive)

**UI Checks:**
- ✅ Positive points in green text
- ✅ Negative points in red text
- ✅ Badge indicators: "Dapat" (earned), "Digunakan" (redeemed), "Disesuaikan" (adjusted)
- ✅ Descriptions link to booking IDs

#### TC6.3: Profile Update
1. **Update** phone number: "081999888777"
2. **Update** address: "Jl. Test Update No. 99, Jakarta"
3. **Click** "Save"

**Expected Results:**
- ✅ Success notification: "Profile updated"
- ✅ Changes persisted in database
- ✅ Page reloads with updated data

---

### Test Suite 7: Admin User Management (Filament)

#### TC7.1: User List View
1. **Login** to admin panel: `http://localhost:8000/admin`
   - Email: admin@admin.com
   - Password: admin123
2. **Navigate** to "Users" menu
3. **Verify** table displays:

| Name | Email | Phone | Points | Bookings | Verified | Joined |
|------|-------|-------|--------|----------|----------|--------|
| VIP User | vip@test.com | 081234567893 | 2000 pts | 4 | Yes | [date] |
| Regular User | regular@test.com | 081234567892 | 500 pts | 3 | Yes | [date] |
| New User | user@test.com | 081234567891 | 0 pts | 0 | Yes | [date] |
| Administrator | admin@admin.com | 081234567890 | 0 pts | 0 | Yes | [date] |

**UI Checks:**
- ✅ Points shown as yellow badges with star icon
- ✅ Bookings count as green badges with calendar icon
- ✅ Email is copyable (clipboard icon)
- ✅ Verified status as colored badges (green=Yes, red=No)

#### TC7.2: Search & Filter
1. **Search** for "regular"
2. **Verify** only "Regular User" shown
3. **Clear search**
4. **Sort by** "Points" column (descending)
5. **Verify** order: VIP User (2000) → Regular User (500) → others (0)

#### TC7.3: View User Details
1. **Click** "VIP User" row (or edit icon)
2. **Verify** form displays all data:
   - Name: VIP User
   - Email: vip@test.com
   - Phone: 081234567893
   - Points Balance: 2000 (readonly field)
   - Address: Jl. Pelanggan Setia No. 10, Yogyakarta
   - Email Verified At: [timestamp]

**UI Checks:**
- ✅ Points field is readonly with helper text: "Use table action to adjust points"
- ✅ Password field is empty (never shows existing hash)
- ✅ All fields pre-filled correctly

#### TC7.4: Edit User Information
1. **Update** address: "Jl. Updated Address No. 100"
2. **Leave** points field unchanged
3. **Click** "Save"

**Expected Results:**
- ✅ User updated successfully
- ✅ Points balance unchanged (2000)
- ✅ Address updated in database
- ✅ Redirected to user list

#### TC7.5: Manual Point Adjustment (Add Points)
1. **Click** "⋮" (actions) on "Regular User" row
2. **Select** "Adjust Points" action
3. **Fill form**:
   - Points: **500** (positive number to add)
   - Reason: "Loyalty bonus for frequent bookings"
4. **Submit**

**Expected Results:**
- ✅ Success notification: "Points adjusted successfully"
- ✅ User's `points_balance` updated: 500 + 500 = 1000
- ✅ `user_points` record created:
  - `type = 'adjusted'`
  - `points = 500`
  - `balance_after = 1000`
  - `description = "Loyalty bonus for frequent bookings"`
  - `booking_id = null`
- ✅ Table refreshes showing new balance

#### TC7.6: Manual Point Deduction
1. **Click** "Adjust Points" on "Regular User" again
2. **Fill form**:
   - Points: **-200** (negative number to deduct)
   - Reason: "Penalty for no-show"
3. **Submit**

**Expected Results:**
- ✅ User's `points_balance` updated: 1000 - 200 = 800
- ✅ `user_points` record created with `points = -200`
- ✅ Balance can go negative if admin forces it (no validation)

#### TC7.7: Create New User
1. **Click** "New User" button
2. **Fill form**:
   - Name: "Admin Created User"
   - Email: "admincreated@test.com"
   - Phone: "081555666777"
   - Address: "Jl. Admin Created No. 1"
   - Password: "password123"
   - Email Verified At: [current datetime]
3. **Submit**

**Expected Results:**
- ✅ User created with `points_balance = 0`
- ✅ Password hashed correctly
- ✅ User appears in list

---

### Test Suite 8: Booking Form Integration

#### TC8.1: Auto-Fill for Authenticated Users
1. **Logout** from admin, login as `vip@test.com`
2. **Navigate** to any lapangan detail page
3. **Verify** booking form:
   - Nama field pre-filled: "VIP User"
   - Email field pre-filled: "vip@test.com"
   - Nomor HP pre-filled: "081234567893"
   - Fields are still editable (not disabled)

**Expected Results:**
- ✅ Auto-fill happens on page load via Livewire `mount()`
- ✅ User can still change values if needed

#### TC8.2: Points Section Visibility
1. **Still on** booking form as authenticated user
2. **Verify** points redemption section is visible:
   - Yellow/orange gradient card
   - "Poin Tersedia: 2000 poin"
   - Toggle switch for "Gunakan Poin untuk Diskon"
   - Helper text: "100 poin = Rp 1.000 diskon"

3. **Logout** and return to same page
4. **Verify** points section is completely hidden (not just disabled)

#### TC8.3: Live Price Calculation
1. **Login** as `vip@test.com`
2. **Book** Futsal Premium A (Rp 300,000)
3. **Select** date and time (e.g., tomorrow 16:00-17:00)
4. **Verify** booking summary shows:
   - Harga: Rp 300.000
   - Total Bayar: Rp 300.000
   - +3.000 poin
5. **Toggle** "Gunakan Poin"
6. **Move slider** to 1000 points
7. **Verify** live updates (no page refresh):
   - Discount display: "-Rp 10.000" (green text)
   - Booking summary:
     - Harga: Rp 300.000
     - Diskon Poin: -Rp 10.000 (new line appears)
     - Total Bayar: Rp 290.000 (updated)
   - Points to earn still: +3.000 (unchanged, based on original price)

**Expected Results:**
- ✅ All updates happen via Livewire (wire:model.live)
- ✅ No page refresh
- ✅ Calculations accurate
- ✅ UI smooth without flicker

#### TC8.4: Max Redemption Button
1. **Continue** from TC8.3
2. **Click** "Gunakan Maksimal" button

**Expected Results:**
- ✅ Points input filled with: min(2000, floor(300000 * 0.5 / 1000 * 100))
- ✅ Calculation: 50% of 300,000 = 150,000 → need 15,000 points
- ✅ User only has 2000 points → use 2000
- ✅ Discount: Rp 20,000
- ✅ Final price: Rp 280,000

---

### Test Suite 9: Queue & Notification Integration

#### TC9.1: Point Earning After Booking
1. **Create** a booking as authenticated user
2. **Check** `jobs` table:
   - Should have job for `BookingConfirmed` notification

**Database Checks:**
```sql
SELECT * FROM jobs WHERE queue = 'default' ORDER BY id DESC LIMIT 1;
```

**Expected Results:**
- ✅ Job payload contains notification data
- ✅ Queue worker processes job
- ✅ Email sent
- ✅ WhatsApp notification sent via Fonnte

#### TC9.2: Point Refund on Cancellation
1. **Login** as regular user with upcoming booking
2. **Cancel** booking from dashboard
3. **Verify** in database:

```sql
SELECT * FROM user_points WHERE user_id = [regular_user_id] ORDER BY id DESC LIMIT 1;
```

**Expected Results:**
- ✅ Latest record has `type = 'earned'` (refund)
- ✅ `points` = positive value (redeemed amount)
- ✅ `description` contains "refunded"
- ✅ `balance_after` = old balance + refunded points

---

### Test Suite 10: Edge Cases & Validation

#### TC10.1: Concurrent Booking Protection
**Note:** This is a known limitation (no pessimistic locking)

1. **Open** two browser windows
2. **Login** same user in both
3. **Select** same lapangan, date, time
4. **Submit** both forms simultaneously

**Current Behavior:**
- ⚠️ Both bookings may succeed (race condition)
- ⚠️ Points may be incorrectly calculated

**Future Fix:** Add DB transactions with row locking in `BookingForm::submitBooking()`

#### TC10.2: Zero Price Lapangan
1. **Admin panel** → Edit lapangan
2. **Set** price to 0
3. **Try booking** as user

**Expected Results:**
- ✅ Booking succeeds
- ✅ Points earned = 0 (floor(0 * 0.01))
- ✅ No errors

#### TC10.3: Negative Points Balance (Admin Force)
1. **Admin panel** → Adjust points on user with 100 points
2. **Deduct** -500 points
3. **Submit**

**Expected Results:**
- ✅ Balance becomes -400 (no validation prevents this)
- ⚠️ User cannot redeem points when balance < 0
- ⚠️ This is admin responsibility, no automatic checks

#### TC10.4: Email Verification Flow
1. **Register** new user: "unverified@test.com"
2. **Check** email (or logs)
3. **Click** verification link
4. **Verify** `email_verified_at` is set

**Expected Results:**
- ✅ Email sent on registration
- ✅ Link expires after configured time
- ✅ Timestamp updated on verification
- ✅ User can book before verifying (no middleware enforced)

---

## 📊 Test Data Summary

### Seeded Users

| User | Email | Password | Points | Bookings | Notes |
|------|-------|----------|--------|----------|-------|
| Admin | admin@admin.com | admin123 | 0 | 0 | Admin panel access |
| New User | user@test.com | password | 0 | 0 | Clean slate testing |
| Regular User | regular@test.com | password | 500 | 3 | Past, upcoming, cancelled |
| VIP User | vip@test.com | password | 2000 | 4 | High balance, max redemption |

### Seeded Bookings

**Regular User:**
1. ✅ Past (7 days ago): Futsal, completed, +3000 pts
2. 📅 Upcoming (in 3 days): Basket, confirmed, -200 pts redeemed
3. ❌ Cancelled (future): Badminton, -300 pts redeemed then refunded

**VIP User:**
1. ✅ Past (30 days ago): Futsal, completed, +3000 pts
2. ✅ Past (20 days ago): Basket, completed, +3500 pts
3. ✅ Past (10 days ago): Futsal, completed, +3000 pts
4. 📅 Upcoming (in 2 days): Basket, confirmed, -1750 pts (50% discount)

**Guest:**
1. 📅 Upcoming (in 4 days): Futsal, confirmed, no points

---

## 🔍 Database Verification Queries

### Check Points Balance
```sql
SELECT name, email, points_balance FROM users WHERE email LIKE '%test%';
```

### Check Point Transactions
```sql
SELECT 
    u.name,
    up.type,
    up.points,
    up.balance_after,
    up.description,
    up.created_at
FROM user_points up
JOIN users u ON up.user_id = u.id
WHERE u.email = 'regular@test.com'
ORDER BY up.id DESC
LIMIT 10;
```

### Check Booking Points
```sql
SELECT 
    b.id,
    l.title AS lapangan,
    b.tanggal,
    b.status,
    b.points_earned,
    b.points_redeemed,
    u.name AS user_name
FROM bookings b
JOIN lapangan l ON b.lapangan_id = l.id
LEFT JOIN users u ON b.user_id = u.id
ORDER BY b.created_at DESC;
```

### Verify Point Audit Trail
```sql
SELECT 
    booking_id,
    SUM(CASE WHEN type = 'earned' THEN points ELSE 0 END) AS total_earned,
    SUM(CASE WHEN type = 'redeemed' THEN points ELSE 0 END) AS total_redeemed,
    SUM(CASE WHEN type = 'adjusted' THEN points ELSE 0 END) AS total_adjusted
FROM user_points
WHERE user_id = (SELECT id FROM users WHERE email = 'regular@test.com')
GROUP BY booking_id;
```

---

## ✅ Success Criteria

The authentication and point system passes testing if:

- [x] All 4 test users can login/logout successfully
- [x] New users can register with email verification
- [x] Points earned correctly (1% of booking price)
- [x] Points redeemed correctly (100 pts = Rp 1,000)
- [x] Maximum 50% discount enforced
- [x] Dashboard tabs filter correctly (upcoming/past/cancelled)
- [x] User can cancel upcoming bookings
- [x] Points refunded on cancellation
- [x] Profile shows accurate point history
- [x] Admin can view all users with point balances
- [x] Admin can manually adjust points with reason
- [x] Guest bookings still work (user_id = null)
- [x] Booking form auto-fills for authenticated users
- [x] Live point calculation works without page refresh
- [x] All point transactions logged in `user_points` table
- [x] Notifications include point information

---

## 🐛 Known Issues & Limitations

### 1. Race Condition in Concurrent Bookings
**Status:** Not fixed  
**Impact:** Two users can book same slot simultaneously  
**Workaround:** Low probability in current usage  
**Fix:** Add DB transaction with `lockForUpdate()` on slot check

### 2. No Pessimistic Locking on Point Balance
**Status:** Not fixed  
**Impact:** Concurrent redemptions might cause negative balance  
**Mitigation:** PointService uses DB transactions, but not row-level locks  
**Fix:** Add `SELECT ... FOR UPDATE` in `redeemPoints()` method

### 3. Admin Can Force Negative Balance
**Status:** By design  
**Impact:** Admin can deduct more points than user has  
**Reason:** Admin override capability for penalties  
**Note:** User cannot redeem when balance < 0 (frontend validation)

### 4. Guest Email Not Verified
**Status:** Expected behavior  
**Impact:** Guest bookings don't require email verification  
**Reason:** Simplified guest flow, verification only for registered users  
**Note:** Notifications still sent to unverified guest emails

---

## 📞 Support

**Seeding Issues:**
```powershell
# If seeding fails, try:
php artisan db:wipe
php artisan migrate
php artisan db:seed
```

**Queue Not Processing:**
```powershell
# Restart queue worker:
php artisan queue:restart
php artisan queue:listen
```

**Livewire Not Updating:**
```powershell
# Clear Livewire temp files:
php artisan livewire:delete-stubs
```

**Vite Assets Not Loading:**
```powershell
# Rebuild assets:
npm run build
# Or run dev server:
npm run dev
```

---

**Testing Completed By:** _________________  
**Date:** _________________  
**Pass/Fail:** _________________  
**Notes:** _________________
