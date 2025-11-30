# 🧪 GoField Testing Guide

Complete guide for testing the GoField sports field booking system with authentication and point rewards.

---

## 📋 Table of Contents

1. [Test Accounts](#test-accounts)
2. [Testing User Features](#testing-user-features)
3. [Testing Admin Features](#testing-admin-features)
4. [Testing Point System](#testing-point-system)
5. [Common Test Scenarios](#common-test-scenarios)

---

## 🔐 Test Accounts

### Admin Account
```
Email: admin@admin.com
Password: admin123
Access: /admin
```

### Test User Accounts

#### 1. New User (No Points, No Bookings)
```
Email: user@test.com
Password: password
Points: 0
Bookings: None
Status: Perfect for testing new user registration flow
```

#### 2. Regular User (Active Customer)
```
Email: regular@test.com
Password: password
Points: 500
Bookings: 3 (1 completed, 1 upcoming, 1 cancelled)
Status: Test point earning, redemption, and cancellation
```

#### 3. VIP User (Loyal Customer)
```
Email: vip@test.com
Password: password
Points: 2000
Bookings: 4 (3 completed, 1 upcoming)
Status: Test large point redemptions and history
```

---

## 👤 Testing User Features

### 1. Registration & Login

#### Test New User Registration
1. Navigate to `/register`
2. Fill form:
   - Name: Test User
   - Email: newuser@test.com
   - Password: password
   - Confirm Password: password
   - Phone: 081234567890
   - Address: Test Address
3. Submit form
4. ✅ Should redirect to `/dashboard`
5. ✅ Points balance should be 0
6. ✅ Email should show "Not Verified" badge

#### Test Login
1. Navigate to `/login`
2. Use any test account credentials
3. ✅ Should redirect to `/dashboard`
4. ✅ Should see welcome message with user name

#### Test Logout
1. Click profile dropdown (top right)
2. Click "Logout"
3. ✅ Should redirect to home page
4. ✅ Should not have access to `/dashboard`

---

### 2. Browsing & Booking

#### Test Browse Lapangan (Guest)
1. Navigate to `/` (home page)
2. Scroll to "Lapangan Tersedia" section
3. ✅ Should see 6 lapangan cards (pagination: 6 per page)
4. ✅ Each card shows: image, category badge, title, price
5. Click "Lihat Detail" on any lapangan
6. ✅ Should redirect to `/detail/{id}`

#### Test Booking as Guest
1. On detail page, select:
   - Date: Tomorrow
   - Time Slot: Any available (green)
2. Fill booking form:
   - Name: Guest User
   - Phone: 081999999999
   - Email: guest@example.com
3. Click "Konfirmasi Booking"
4. ✅ Should see success message
5. ✅ Should NOT see point redemption option
6. ✅ Should NOT earn points (guest booking)

#### Test Booking as Authenticated User
1. **Login** as `regular@test.com`
2. Navigate to home → Click any lapangan
3. **Auto-fill Check**:
   - ✅ Name field should be pre-filled
   - ✅ Phone field should be pre-filled
   - ✅ Email field should be pre-filled
4. Select date and time slot
5. **Point Redemption Check**:
   - ✅ Should see "💎 Gunakan Poin" section
   - ✅ Shows current balance: 500 poin
   - ✅ Toggle switch to enable point redemption
   - Enter points to redeem: 100
   - ✅ Should see discount calculation (100 pts = Rp 1,000)
   - ✅ Should see updated total price
   - ✅ Should see points will be earned (1% of price)
6. Submit booking
7. ✅ Success notification should appear
8. ✅ Redirect to `/dashboard`

---

### 3. Dashboard Features

#### Test Dashboard Overview
1. Login as `regular@test.com`
2. Navigate to `/dashboard`
3. **Points Balance Card Check**:
   - ✅ Shows current points balance
   - ✅ Shows rupiah equivalent
   - ✅ Has "Booking Sekarang" button
   - ✅ Has "Lihat Profil" button

#### Test Dashboard Tabs
1. **Upcoming Tab** (default):
   - ✅ Shows confirmed bookings with future dates
   - ✅ Each card shows: lapangan name, category, date, time, price
   - ✅ Shows points earned badge (if any)
   - ✅ Shows points redeemed badge (if any)
   - ✅ Has "Batalkan Booking" button

2. **Riwayat Tab**:
   - Click "Riwayat" tab
   - ✅ Shows completed bookings
   - ✅ Status badge shows "✓ Selesai"
   - ✅ Shows points earned

3. **Dibatalkan Tab**:
   - Click "Dibatalkan" tab
   - ✅ Shows cancelled bookings
   - ✅ Status badge shows "✗ Dibatalkan"
   - ✅ No action buttons

#### Test Cancel Booking
1. On "Mendatang" tab
2. Click "Batalkan Booking" on any confirmed booking
3. Confirm cancellation dialog
4. ✅ Booking status should change to cancelled
5. ✅ If points were redeemed, they should be refunded
6. ✅ Success notification appears
7. ✅ Booking moves to "Dibatalkan" tab

---

### 4. Profile Features

#### Test Profile View
1. Login and navigate to `/profile`
2. **Points Balance Card**:
   - ✅ Shows total points
   - ✅ Shows rupiah equivalent

#### Test Profile Update
1. On profile page, update:
   - Name: Updated Name
   - Phone: 089999999999
   - Address: New Address
2. Click "Save"
3. ✅ Success message appears
4. ✅ Changes reflected in navbar
5. ✅ Changes reflected in booking form auto-fill

#### Test Point History
1. Scroll to "Riwayat Poin" section
2. ✅ Should show all point transactions
3. Each transaction shows:
   - ✅ Icon (up arrow for earned, down arrow for redeemed)
   - ✅ Description
   - ✅ Points amount (+/-)
   - ✅ Balance after
   - ✅ Timestamp

---

## 🔧 Testing Admin Features

### 1. Admin Login
1. Navigate to `/admin`
2. Login with `admin@admin.com` / `admin123`
3. ✅ Should see Filament dashboard
4. ✅ Navigation shows: Dashboard, Bookings, Lapangan, Settings, Users

---

### 2. User Management

#### View Users List
1. Click "Users" in navigation
2. ✅ Should see all users in table
3. Table columns:
   - ✅ Name (searchable)
   - ✅ Email (copyable)
   - ✅ Phone
   - ✅ Points Balance (badge with "pts" suffix)
   - ✅ Total Bookings (count badge)
   - ✅ Email Verified status
   - ✅ Joined date

#### Test User Filters
1. **Email Verified Filter**:
   - Select "Verified"
   - ✅ Shows only verified users
   - Select "Not Verified"
   - ✅ Shows only unverified users

2. **Booking Status Filter**:
   - Select "Has Bookings"
   - ✅ Shows only users with bookings
   - Select "No Bookings"
   - ✅ Shows only users without bookings

#### Test Search
1. Type email in search box
2. ✅ Results filter in real-time
3. Type name in search box
4. ✅ Results filter in real-time

#### Test Adjust Points (Manual)
1. Click "Adjust Points" button on any user row
2. Enter points amount:
   - **Positive** (e.g., 500): Add bonus points
   - **Negative** (e.g., -100): Deduct points
3. Enter reason: "Testing point adjustment"
4. Click "Save"
5. ✅ Success notification
6. ✅ User's point balance updated
7. ✅ Transaction recorded in point history

#### View User Details
1. Click "Edit" on any user
2. **User Info Tab**:
   - ✅ Shows all user fields
   - ✅ Points Balance is read-only
   - ✅ Can update name, email, phone, address
   - ✅ Can change password

3. **Bookings Tab**:
   - ✅ Shows all user bookings
   - ✅ Displays: lapangan, date, time, price, status
   - ✅ Shows points earned/redeemed badges
   - ✅ Click to view booking details

4. **Point History Tab**:
   - ✅ Shows all point transactions
   - ✅ Color-coded badges (green=earned, red=redeemed, yellow=adjusted)
   - ✅ Shows booking link (if applicable)
   - ✅ Shows balance after each transaction
   - ✅ Filter by transaction type

---

### 3. Booking Management

#### View Bookings List
1. Click "Bookings" in navigation
2. ✅ Table shows all bookings
3. Columns:
   - ID, User, Lapangan, Category, Date, Time
   - Status, Points Earned/Redeemed, Created At

#### Test Booking Filters
1. **Status Filter**:
   - Select "Confirmed"
   - ✅ Shows only confirmed bookings
   
2. **Date Range Filter**:
   - Select date range
   - ✅ Shows bookings within range

3. **User Filter**:
   - Search by user name
   - ✅ Shows only that user's bookings

#### Test Cancel Booking (Admin)
1. Click "Cancel" on any booking
2. Enter cancellation reason
3. ✅ Booking status changes to cancelled
4. ✅ If points redeemed, they are refunded to user
5. ✅ Notification sent to user (email + WhatsApp)

#### View Booking Details
1. Click "Edit" on any booking
2. ✅ Shows full booking information
3. ✅ Can see linked user (if authenticated booking)
4. ✅ Can view point transaction history

---

### 4. Lapangan Management

#### Create New Lapangan
1. Click "Lapangan" → "Create"
2. Fill form:
   - Title: Test Lapangan
   - Category: Select from dropdown
   - Price: 250000
   - Description: Rich text editor
   - Images: Upload 1-3 images
   - Status: Active
3. ✅ Lapangan created successfully
4. ✅ Appears on public website

#### Edit Lapangan
1. Click "Edit" on any lapangan
2. Update fields
3. ✅ Changes reflected immediately on website

---

### 5. Settings Management

#### Update Operating Hours
1. Click "Settings"
2. Edit "jam_buka": 06:00
3. Edit "jam_tutup": 22:00
4. ✅ Time slots on booking form update accordingly

---

## 🎯 Testing Point System

### Point Earning Flow
1. **Login as** `user@test.com` (0 points)
2. **Create booking** for Rp 300,000 lapangan
3. ✅ After booking confirmed, points remain 0 (not earned yet)
4. **Admin**: Change booking status to "completed"
5. ✅ User should receive 3,000 points (1% of 300,000)
6. Check user's point history:
   - ✅ Transaction shows: "Earned from booking #X"
   - ✅ Balance after: 3,000

### Point Redemption Flow
1. **Login as** `regular@test.com` (500 points)
2. **Create booking** for Rp 200,000 lapangan
3. **Enable point redemption** toggle
4. **Enter** 100 points
5. ✅ Discount shown: Rp 1,000
6. ✅ New total: Rp 199,000
7. ✅ Points after: 400 (500 - 100)
8. Submit booking
9. Check dashboard:
   - ✅ Booking shows "-100" points badge
   - ✅ Points balance updated to 400
10. Check point history:
    - ✅ Transaction shows: "Redeemed for booking #X"
    - ✅ Points: -100
    - ✅ Balance after: 400

### Point Refund Flow
1. **Login as** `regular@test.com`
2. **Cancel** a booking that redeemed 200 points
3. ✅ Points should be refunded immediately
4. ✅ Balance increases by 200
5. Check point history:
   - ✅ Transaction shows: "Refund from cancelled booking #X"
   - ✅ Points: +200
   - ✅ Balance after: updated

### Manual Point Adjustment (Admin)
1. **Admin Login**
2. Go to Users → Select user
3. **Add Points**:
   - Click "Adjust Points"
   - Enter: 1000
   - Reason: "Loyalty bonus"
   - ✅ User balance increases by 1000
4. **Deduct Points**:
   - Click "Adjust Points"
   - Enter: -500
   - Reason: "Admin correction"
   - ✅ User balance decreases by 500
5. Check point history:
   - ✅ Both transactions recorded with type "adjusted"

---

## 🧩 Common Test Scenarios

### Scenario 1: New User Journey
```
1. Register new account → ✅ 0 points
2. Browse lapangan → ✅ See all facilities
3. Make first booking → ✅ Auto-fill enabled, no points to redeem
4. Admin completes booking → ✅ Earn first points
5. Make second booking with point redemption → ✅ Get discount
6. View dashboard → ✅ See booking history
7. View profile → ✅ See point transaction history
```

### Scenario 2: VIP User Journey
```
1. Login as vip@test.com → ✅ 2000 points available
2. Browse premium lapangan (Rp 400,000)
3. Enable point redemption → ✅ Max 50% discount (2000 points max)
4. Redeem 1750 points → ✅ Rp 17,500 discount
5. Submit booking → ✅ New balance: 250 points
6. View dashboard → ✅ See upcoming booking with large redemption badge
7. Admin completes booking → ✅ Earn 4000 points
8. Final balance: 250 + 4000 = 4250 points
```

### Scenario 3: Cancellation & Refund
```
1. Login as regular@test.com
2. Create booking with 500 points redeemed
3. Balance: 0 points
4. Cancel booking within 24 hours
5. ✅ Points refunded: 500
6. ✅ Balance restored to 500
7. ✅ Transaction history shows both redemption and refund
```

### Scenario 4: Admin Point Management
```
1. Admin reviews user loyalty
2. VIP user has 5000 points
3. Admin adds 500 bonus points: "Loyal customer reward"
4. ✅ User balance: 5500 points
5. ✅ Notification sent to user
6. User sees bonus in point history
7. User redeems points on next booking
```

---

## 🐛 Known Behaviors to Test

### Guest vs Authenticated Booking
- **Guest**: No points, no history, fills all fields manually
- **Authenticated**: Auto-fill, point redemption, history tracking

### Point Calculation
- **Earn Rate**: 1% of booking price
- **Redeem Rate**: 100 points = Rp 1,000
- **Max Redemption**: 50% of booking price

### Booking Status Flow
```
pending → confirmed → completed (points earned)
         ↓
      cancelled (points refunded if redeemed)
```

### Email Notifications
Test notifications are sent for:
- ✅ Booking confirmed
- ✅ Booking cancelled (with reason)
- ✅ H-24 reminder (scheduled)

### WhatsApp Notifications
Test Fonnte integration:
- ✅ Phone number formatting (0xxx → 62xxx)
- ✅ Template messages sent
- ✅ Error handling doesn't block booking

---

## 📊 Quick Test Checklist

### User Features
- [ ] Registration works
- [ ] Login/logout works
- [ ] Profile update works
- [ ] Browse lapangan works
- [ ] Guest booking works
- [ ] Auth booking with auto-fill works
- [ ] Point redemption works
- [ ] Discount calculation correct
- [ ] Dashboard tabs work
- [ ] Cancel booking works
- [ ] Point refund works
- [ ] Point history displays correctly

### Admin Features
- [ ] Admin login works
- [ ] View all users
- [ ] Search/filter users works
- [ ] Adjust points manually works
- [ ] View user bookings
- [ ] View point transactions
- [ ] Manage lapangan works
- [ ] Manage bookings works
- [ ] Cancel booking with reason works
- [ ] Update settings works

### Point System
- [ ] Points earned on completed bookings
- [ ] Points redeemed correctly
- [ ] Discount calculated accurately
- [ ] Points refunded on cancellation
- [ ] Manual adjustments work
- [ ] Transaction history accurate
- [ ] Balance always correct

---

## 🎓 Testing Tips

1. **Test with Multiple Users**: Login as different users to see different point balances and booking histories
2. **Test Edge Cases**: 
   - Try redeeming more points than available
   - Try booking slots that overlap
   - Try cancelling past bookings
3. **Test Notifications**: Check email and WhatsApp logs
4. **Test Filters**: Use admin filters to verify data integrity
5. **Test Calculations**: Manually verify point calculations
6. **Test Permissions**: Try accessing admin routes as regular user (should fail)

---

## 🚀 Quick Start Testing Commands

```bash
# Reset database and seed test data
php artisan migrate:fresh --seed

# Clear all caches
php artisan config:clear && php artisan view:clear && php artisan route:clear

# Start dev server
php artisan serve

# Run queue worker (for notifications)
php artisan queue:listen

# Check routes
php artisan route:list
```

---

## 📝 Notes

- All test user passwords are: `password`
- Admin password is: `admin123`
- Database is seeded with realistic data
- Point balances are pre-configured for testing different scenarios
- Booking dates are relative to seeding time (past/future bookings)

---

**Happy Testing! 🎉**

For issues or questions, check the application logs in `storage/logs/`.
