# Database Refactoring Summary
**Date**: December 3, 2025  
**Status**: ✅ COMPLETED

## Overview
Refactored GoField database architecture from enum-based columns to relational master tables for multi-tenant scalability as required by instructor.

---

## Architecture Changes

### Before (Enum-Based)
- `lapangan.category` → ENUM ('Futsal', 'Badminton', 'Basket', 'Volly', 'Tennis')
- `bookings.payment_method` → ENUM ('cash', 'bank_transfer', 'qris', 'e_wallet', 'credit_card')
- ❌ Not scalable for multi-tenant (every tenant would need code changes to add new sports/payment methods)

### After (Relational Master Tables - Opsi A)
- `sport_types` table → Master data for sport categories
- `payment_methods` table → Master data for payment methods with fee configuration
- `transactions` table → Payment transaction tracking with full audit trail
- `lapangan.sport_type_id` → Foreign key to sport_types
- `bookings.payment_method_id` → Foreign key to payment_methods
- ✅ Scalable: Each tenant can manage their own sports and payment methods via admin panel

---

## Database Schema

### New Tables

#### 1. sport_types
```sql
- id (PK)
- code (unique, e.g., 'futsal', 'basketball')
- name (e.g., 'Futsal', 'Basketball')
- description
- icon (heroicon class)
- is_active (boolean)
- sort_order
- timestamps
```

**Relationships:**
- `hasMany` lapangans

**Seeded Data:**
- Futsal, Basketball, Volleyball, Badminton, Tennis (5 sport types)

---

#### 2. payment_methods
```sql
- id (PK)
- code (unique, e.g., 'cash', 'bank_transfer')
- name (e.g., 'Tunai', 'Transfer Bank')
- description
- logo (image path)
- is_active (boolean)
- config (JSON, e.g., bank account numbers, QRIS path)
- admin_fee (flat fee in Rp)
- admin_fee_percentage (percentage fee, e.g., 0.7 for 0.7%)
- sort_order
- timestamps
```

**Relationships:**
- `hasMany` bookings
- `hasMany` transactions

**Seeded Data:**
- Cash (0% fee)
- Bank Transfer (0% fee, with BCA account config)
- QRIS (0.7% fee, with QR image path)
- E-Wallet (1% fee, with GoPay/OVO/DANA config)
- Credit Card (2.9%+Rp5,000 fee, INACTIVE)

**Methods:**
- `calculateAdminFee($amount)` → Returns calculated fee based on flat + percentage
- `calculateTotalAmount($amount)` → Returns amount + admin fee

---

#### 3. transactions
```sql
- id (PK)
- transaction_code (unique, auto-generated: TRX-YYYYMMDD-XXXXX)
- booking_id (FK to bookings)
- payment_method_id (FK to payment_methods)
- amount (booking price)
- admin_fee (calculated fee)
- total_amount (amount + admin_fee)
- status (pending, waiting_confirmation, paid, failed, refunded)
- payment_proof (image path)
- notes (customer notes)
- admin_notes (internal notes)
- paid_at
- confirmed_at
- confirmed_by (FK to users - admin who confirmed)
- refunded_at
- refund_amount
- timestamps
```

**Relationships:**
- `belongsTo` booking
- `belongsTo` paymentMethod
- `belongsTo` confirmedBy (User model)

**Methods:**
- `generateTransactionCode()` → Auto-generates TRX-YYYYMMDD-XXXXX format
- `markAsPaid()` → Sets status to paid with timestamp
- `markAsFailed()` → Sets status to failed
- `processRefund($amount)` → Processes refund with amount and timestamp

---

### Modified Tables

#### lapangan
**Removed:**
- `category` (enum)

**Added:**
- `sport_type_id` (FK to sport_types, nullable, onDelete cascade)

**Relationships:**
- `belongsTo` sportType

---

#### bookings
**Removed:**
- `payment_method` (enum)

**Added:**
- `payment_method_id` (FK to payment_methods, nullable, onDelete set null)

**Relationships:**
- `belongsTo` paymentMethod
- `hasMany` transactions

---

## Models Created/Updated

### New Models
1. **SportType** (`app/Models/SportType.php`)
   - Fillable: code, name, description, icon, is_active, sort_order
   - Relationships: `hasMany` lapangans
   - Scopes: `scopeActive()`, `scopeOrdered()`

2. **PaymentMethod** (`app/Models/PaymentMethod.php`)
   - Fillable: code, name, description, logo, is_active, config (casted to array), admin_fee, admin_fee_percentage, sort_order
   - Relationships: `hasMany` bookings, `hasMany` transactions
   - Methods: `calculateAdminFee()`, `calculateTotalAmount()`
   - Scopes: `scopeActive()`, `scopeOrdered()`

3. **Transaction** (`app/Models/Transaction.php`)
   - Fillable: booking_id, payment_method_id, amount, admin_fee, total_amount, status, payment_proof, notes, admin_notes, paid_at, confirmed_at, confirmed_by, refunded_at, refund_amount
   - Auto-generates: transaction_code (TRX-YYYYMMDD-XXXXX)
   - Relationships: `belongsTo` booking, `belongsTo` paymentMethod, `belongsTo` confirmedBy
   - Methods: `generateTransactionCode()`, `markAsPaid()`, `markAsFailed()`, `processRefund()`
   - Scopes: `scopePending()`, `scopePaid()`, `scopeWaitingConfirmation()`

### Updated Models
1. **Lapangan** (`app/Models/Lapangan.php`)
   - Added: sport_type_id to fillable
   - Removed: category from fillable
   - Added relationship: `belongsTo` sportType

2. **Booking** (`app/Models/Booking.php`)
   - Added: payment_method_id to fillable
   - Removed: payment_method from fillable
   - Added relationships: `belongsTo` paymentMethod, `hasMany` transactions

---

## Filament Admin Panel

### New Resources

#### 1. SportTypeResource
**Location:** `app/Filament/Resources/SportTypes/`
- **Navigation:** Master Data group, icon trophy, sort order 1
- **Form Fields:**
  - Code (unique, lowercase, auto-generates slug)
  - Name (Indonesian)
  - Description
  - Icon (heroicon class selector)
  - Sort Order (numeric)
  - Is Active (toggle)
- **Table Columns:**
  - Name (searchable)
  - Code (badge)
  - Description
  - Lapangans Count (relationship count)
  - Sort Order (badge)
  - Is Active (icon boolean)
  - Timestamps (hidden by default)
- **Filters:** None (simple master data)

---

#### 2. PaymentMethodResource
**Location:** `app/Filament/Resources/PaymentMethods/`
- **Navigation:** Master Data group, icon credit-card, sort order 2
- **Form Fields:**
  - Code (unique)
  - Name (Indonesian)
  - Description
  - Logo (file path input)
  - Admin Fee (Rp prefix)
  - Admin Fee Percentage (% suffix)
  - Sort Order (numeric)
  - Is Active (toggle)
- **Table Columns:**
  - Name (searchable)
  - Code (badge)
  - Description
  - Admin Fee (money IDR format)
  - Admin Fee Percentage (% display)
  - Bookings Count (relationship count)
  - Sort Order (badge)
  - Is Active (icon boolean)
  - Timestamps (hidden by default)
- **Filters:** None (simple master data)

---

#### 3. TransactionResource
**Location:** `app/Filament/Resources/Transactions/`
- **Navigation:** Pembayaran group, icon banknotes, sort order 3
- **Form Sections:**
  1. **Informasi Transaksi:**
     - Transaction Code (disabled, auto-generated, placeholder)
     - Booking (relationship select, searchable, disabled after creation)
     - Payment Method (relationship select, searchable, disabled after creation)
  
  2. **Detail Pembayaran:**
     - Amount (Rp prefix, disabled after creation)
     - Admin Fee (Rp prefix, disabled after creation)
     - Total Amount (Rp prefix, disabled after creation)
     - Status (select: pending/waiting_confirmation/paid/failed/refunded)
  
  3. **Bukti Pembayaran:**
     - Payment Proof (image upload, public disk, max 2MB)
     - Customer Notes (disabled after creation)
  
  4. **Konfirmasi & Refund:** (collapsible)
     - Paid At (disabled, auto-set)
     - Confirmed At (disabled, auto-set)
     - Confirmed By (relationship select, disabled, auto-set)
     - Admin Notes (editable)
     - Refunded At (visible only if refunded, disabled)
     - Refund Amount (visible only if refunded, disabled)

- **Table Columns:**
  - Transaction Code (badge, primary color, searchable, sortable, copyable)
  - Booking Code (badge, info color, clickable link to booking, searchable, sortable)
  - Customer Name (from booking.nama_pemesan, searchable, sortable, wrap text)
  - Payment Method (badge, success color, searchable, sortable)
  - Amount (money IDR format, sortable)
  - Admin Fee (money IDR format, sortable, toggleable)
  - Total Amount (money IDR format, sortable, bold)
  - Status (badge with colors):
    - pending → warning (yellow)
    - waiting_confirmation → info (blue)
    - paid → success (green)
    - failed → danger (red)
    - refunded → gray
  - Payment Proof (image column, 60px size, toggleable)
  - Paid At (datetime, sortable, toggleable)
  - Confirmed At (datetime, sortable, toggleable)
  - Confirmed By (hidden by default, searchable, toggleable)
  - Refunded At (hidden by default, datetime, sortable, toggleable)
  - Refund Amount (hidden by default, money IDR, sortable, toggleable)
  - Created At (hidden by default, datetime, sortable, toggleable)

- **Filters:**
  - Status (multiple select with Indonesian labels)
  - Payment Method (relationship select, multiple, preloaded)

- **Actions:**
  - View (primary)
  - Edit (secondary)
  - Bulk Delete (toolbar)

- **Default Sort:** created_at DESC (newest first)

---

### Updated Resources

#### 1. LapanganResource
**Changes:**
- **Form:** Replaced category enum Select with sport_type_id relationship Select
  - Added relationship: `->relationship('sportType', 'name')`
  - Added searchable & preload for better UX
  - Added createOptionForm for quick-create SportType inline

- **Table:** Replaced category column with sportType.name
  - Column now shows relationship data: `TextColumn::make('sportType.name')`
  - Searchable & sortable maintained

---

#### 2. BookingResource
**Changes:**
- **Form:** Replaced payment_method enum Select with payment_method_id relationship Select
  - Added relationship: `->relationship('paymentMethod', 'name')`
  - Added searchable & preload for better UX
  - Added createOptionForm for quick-create PaymentMethod inline

- **Table:** Updated lapangan column to show sportType
  - Column now includes: `lapangan.title (sportType.name)`
  - Maintained all existing columns and functionality

---

## Seeders

### New Seeders
1. **SportTypeSeeder** (`database/seeders/SportTypeSeeder.php`)
   - Seeds 5 default sport types with Indonesian names and icons
   - Called first in DatabaseSeeder

2. **PaymentMethodSeeder** (`database/seeders/PaymentMethodSeeder.php`)
   - Seeds 5 default payment methods with fee configurations
   - Called second in DatabaseSeeder

### Updated Seeders
1. **DatabaseSeeder** (`database/seeders/DatabaseSeeder.php`)
   - Calls SportTypeSeeder and PaymentMethodSeeder first
   - Updated lapangan seeding to get sport_type_id from SportType::where('code', ...)
   - Updated booking queries to use sport_type_id instead of category

---

## Frontend Updates

### Controllers

#### HomeController (`app/Http/Controllers/HomeController.php`)
**Changes:**
- `index()`: 
  - Added `->with('sportType')` for eager loading
  - Replaced `'category'` in select() with `'sport_type_id'`
- `detail()`:
  - Added `->with('sportType')` in cached query

---

### Views (Blade Templates)

#### 1. home.blade.php
**Changes:**
- Icon mapping: Changed `$categoryIcons[$item->category]` to `$categoryIcons[$item->sportType?->name ?? '']`
- Badge display: Changed `{{ $item->category }}` to `{{ $item->sportType?->name ?? 'Sport' }}`

---

#### 2. dashboard/index.blade.php
**Changes:**
- Category display: Changed `{{ $booking->lapangan->category }}` to `{{ $booking->lapangan->sportType?->name ?? 'Sport' }}`

---

#### 3. livewire/booking-form-new.blade.php
**Changes:**
- Info badge: Changed `{{ $lapangan->category }}` to `{{ $lapangan->sportType?->name ?? 'Sport' }}`

---

#### 4. livewire/payment-form.blade.php
**Changes:**
- Kategori row: Changed `{{ $booking->lapangan->category }}` to `{{ $booking->lapangan->sportType?->name ?? 'Sport' }}`

---

#### 5. invoices/view.blade.php
**Changes:**
- Condition: Changed `@if($invoice->booking->lapangan->category)` to `@if($invoice->booking->lapangan->sportType)`
- Display: Changed `{{ $invoice->booking->lapangan->category }}` to `{{ $invoice->booking->lapangan->sportType->name }}`

---

#### 6. invoices/pdf.blade.php
**Changes:**
- Condition: Changed `@if($invoice->booking->lapangan->category)` to `@if($invoice->booking->lapangan->sportType)`
- Display: Changed `{{ $invoice->booking->lapangan->category }}` to `{{ $invoice->booking->lapangan->sportType->name }}`

---

### Livewire Components
**Status:** No changes needed
- Checked all Livewire component files (BookingForm.php, BookingFormNew.php, etc.)
- No references to payment_method enum found
- All booking logic already uses database fields, no hardcoded enum handling

---

## Migration Execution

### Commands Run
```powershell
php artisan migrate:fresh --seed
```

### Migrations Executed (28 total)
**New Migrations:**
1. `2025_12_03_215137_create_sport_types_table`
2. `2025_12_03_215145_create_payment_methods_table`
3. `2025_12_03_215257_create_transactions_table`
4. `2025_12_03_215430_update_lapangan_table_use_sport_type_relation`
5. `2025_12_03_215521_update_bookings_table_use_payment_method_relation`

**All Previous Migrations:** Maintained and re-executed successfully

### Seeders Executed
1. ✅ SportTypeSeeder (12ms)
2. ✅ PaymentMethodSeeder (10ms)
3. ✅ DatabaseSeeder (all existing seeders with updated queries)

---

## Testing Performed

### Database Tests
- ✅ All 28 migrations executed without errors
- ✅ Foreign key constraints working correctly
- ✅ Seeders populated data successfully
- ✅ Relationships can be queried (verified via tinker)

### Filament Admin Panel
- ✅ `php artisan filament:optimize` completed successfully
- ✅ SportTypeResource accessible at `/admin/sport-types`
- ✅ PaymentMethodResource accessible at `/admin/payment-methods`
- ✅ TransactionResource accessible at `/admin/transactions`
- ✅ LapanganResource form displays sport_type_id relationship select
- ✅ BookingResource form displays payment_method_id relationship select
- ✅ All table columns display correctly with relationship data

### Application Server
- ✅ `php artisan serve` started without errors
- ✅ No PHP syntax errors detected
- ✅ No runtime errors in log

---

## Benefits of New Architecture

### 1. Scalability (PRIMARY GOAL - Instructor Requirement)
- ✅ **Multi-tenant ready:** Each tenant can manage their own sport types and payment methods
- ✅ **No code changes needed:** Adding new sports/payment methods is done via admin panel
- ✅ **Flexible configuration:** Payment methods have JSON config field for custom settings

### 2. Maintainability
- ✅ **Centralized master data:** Sport types and payment methods in dedicated tables
- ✅ **Audit trail:** Transactions table provides complete payment history
- ✅ **Relationship integrity:** Foreign keys enforce data consistency

### 3. Business Features
- ✅ **Dynamic fee calculation:** Payment methods can have flat + percentage fees
- ✅ **Payment tracking:** Full transaction lifecycle from pending to paid/refunded
- ✅ **Admin oversight:** Transaction confirmations tracked with admin user
- ✅ **Proof management:** Payment proof images stored and viewable

### 4. Developer Experience
- ✅ **Type safety:** Relationship methods provide IDE autocomplete
- ✅ **Readable code:** `$lapangan->sportType->name` vs `$lapangan->category`
- ✅ **Reusable logic:** Fee calculations in PaymentMethod model

---

## Backwards Compatibility

### Breaking Changes
- ⚠️ **API:** Endpoints returning `category` field now return `sport_type_id` and `sport_type` relationship
- ⚠️ **API:** Endpoints returning `payment_method` field now return `payment_method_id` and `payment_method` relationship
- ⚠️ **Database:** Direct SQL queries using `lapangan.category` or `bookings.payment_method` will fail

### Migration Path
If you have existing data:
1. Run migrations in order (sport_types → payment_methods → update_lapangan → update_bookings)
2. Seeders will populate default values
3. Existing bookings will have NULL payment_method_id (safe, field is nullable)
4. Existing lapangan will have NULL sport_type_id (need manual assignment via admin panel)

For this project:
- Used `migrate:fresh --seed` so no data migration needed
- Started with clean database

---

## Next Steps (Optional Enhancements)

### 1. API Updates
- [ ] Update API resources to expose sport_type and payment_method relationships
- [ ] Add API endpoints for listing available sport types (for mobile app filters)
- [ ] Add API endpoints for listing active payment methods

### 2. Transaction Features
- [ ] Auto-create transaction when booking confirmed
- [ ] Send notification when payment proof uploaded (waiting confirmation)
- [ ] Admin dashboard widget for pending transaction count
- [ ] Transaction export to Excel for accounting

### 3. Payment Method Enhancements
- [ ] Add payment gateway integrations (Midtrans, Xendit)
- [ ] Auto-confirmation for certain payment methods (e-wallet callbacks)
- [ ] Payment method availability by time/date (e.g., disable cash on holidays)

### 4. Sport Type Features
- [ ] Sport-specific pricing rules (e.g., tennis courts always cost more)
- [ ] Sport-specific booking rules (e.g., badminton minimum 1 hour)
- [ ] Sport type images for better UI

---

## Files Modified/Created

### Created Files (16 files)
**Migrations:**
1. `database/migrations/2025_12_03_215137_create_sport_types_table.php`
2. `database/migrations/2025_12_03_215145_create_payment_methods_table.php`
3. `database/migrations/2025_12_03_215257_create_transactions_table.php`
4. `database/migrations/2025_12_03_215430_update_lapangan_table_use_sport_type_relation.php`
5. `database/migrations/2025_12_03_215521_update_bookings_table_use_payment_method_relation.php`

**Models:**
6. `app/Models/SportType.php`
7. `app/Models/PaymentMethod.php`
8. `app/Models/Transaction.php`

**Seeders:**
9. `database/seeders/SportTypeSeeder.php`
10. `database/seeders/PaymentMethodSeeder.php`

**Filament Resources:**
11. `app/Filament/Resources/SportTypes/SportTypeResource.php`
12. `app/Filament/Resources/SportTypes/Schemas/SportTypeForm.php`
13. `app/Filament/Resources/SportTypes/Tables/SportTypesTable.php`
14. `app/Filament/Resources/PaymentMethods/PaymentMethodResource.php`
15. `app/Filament/Resources/PaymentMethods/Schemas/PaymentMethodForm.php`
16. `app/Filament/Resources/PaymentMethods/Tables/PaymentMethodsTable.php`
17. `app/Filament/Resources/Transactions/TransactionResource.php`
18. `app/Filament/Resources/Transactions/Schemas/TransactionForm.php`
19. `app/Filament/Resources/Transactions/Tables/TransactionsTable.php`
20. `app/Filament/Resources/Transactions/Pages/CreateTransaction.php`
21. `app/Filament/Resources/Transactions/Pages/EditTransaction.php`
22. `app/Filament/Resources/Transactions/Pages/ListTransactions.php`

**Documentation:**
23. `docs/DATABASE_REFACTORING_SUMMARY.md` (this file)

### Modified Files (13 files)
**Models:**
1. `app/Models/Lapangan.php`
2. `app/Models/Booking.php`

**Seeders:**
3. `database/seeders/DatabaseSeeder.php`

**Filament Resources:**
4. `app/Filament/Resources/Lapangans/LapanganResource.php`
5. `app/Filament/Resources/Bookings/BookingResource.php`

**Controllers:**
6. `app/Http/Controllers/HomeController.php`

**Views:**
7. `resources/views/home.blade.php`
8. `resources/views/dashboard/index.blade.php`
9. `resources/views/livewire/booking-form-new.blade.php`
10. `resources/views/livewire/payment-form.blade.php`
11. `resources/views/invoices/view.blade.php`
12. `resources/views/invoices/pdf.blade.php`

**Configuration:**
13. No config changes needed (used existing database connection)

---

## Deployment Checklist

Before deploying to production:
- [ ] Backup existing database
- [ ] Run migrations in order (DO NOT use migrate:fresh on production!)
- [ ] Manually assign sport_type_id to existing lapangan records
- [ ] Verify all relationships display correctly in admin panel
- [ ] Test booking flow with new payment_method_id field
- [ ] Update API documentation with new response structures
- [ ] Clear all caches: `php artisan optimize:clear && php artisan filament:optimize`
- [ ] Test with real data (create test bookings, transactions)
- [ ] Monitor logs for any relationship query errors

---

## Support

For questions about this refactoring:
1. Check this document first
2. Review migration files for exact schema changes
3. Check model relationships for query patterns
4. Verify Filament resource forms/tables for admin UI

**Project:** GoField Multi-Sport Booking Platform  
**Framework:** Laravel 12 + Filament 4  
**Database:** MySQL (Laragon)  
**Refactoring Date:** December 3, 2025
