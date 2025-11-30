# 💳 Payment System Documentation

## Overview
Sistem pembayaran manual multi-metode untuk SportBooking tanpa integrasi payment gateway pihak ketiga. User dapat memilih metode pembayaran dan upload bukti, kemudian admin verifikasi melalui Filament Admin Panel.

---

## 🎯 Features

### User-Facing Features
- **4 Metode Pembayaran**:
  - 💵 **Bayar di Tempat** (Cash on Arrival)
  - 🏦 **Transfer Bank** (BCA, Mandiri)
  - 📱 **QRIS** (Scan & Pay)
  - 💳 **E-Wallet** (Dana, OVO, GoPay)

- **Payment Flow**:
  1. User membuat booking → Status: `pending`, Payment: `unpaid`
  2. User klik "Bayar Sekarang" di dashboard
  3. Pilih metode pembayaran
  4. **Jika Cash**: Langsung confirmed (bayar saat kedatangan)
  5. **Jika Transfer/QRIS/E-Wallet**: Upload bukti pembayaran
  6. Admin verifikasi → Status: `paid`

- **Upload Bukti Pembayaran**:
  - Format: Image (JPG, PNG, etc.)
  - Max size: 2MB
  - Auto-delete old proof when re-upload
  - Optional payment notes

### Admin Features (Filament)
- **Payment Verification**:
  - View payment proof in modal (full size image)
  - Approve payment → Set status `paid` + confirm booking
  - Reject payment → Return to `unpaid` with rejection reason
  - Track payment confirmation timestamp & admin who confirmed

- **Payment Columns in Table**:
  - Payment Status (badge dengan warna)
  - Payment Method (dengan emoji)
  - Total Amount (formatted Rupiah)
  
- **Filters**:
  - Filter by payment status
  - Filter by payment method
  - Filter by booking status

- **Actions**:
  - 👁️ **Lihat Bukti** - View payment proof modal
  - ✅ **Terima** - Approve payment (only for `waiting_confirmation`)
  - ❌ **Tolak** - Reject payment with reason

---

## 🗄️ Database Schema

### Added Columns to `bookings` table:

```php
$table->enum('payment_method', ['cash', 'bank_transfer', 'qris', 'e_wallet'])->nullable();
$table->enum('payment_status', ['unpaid', 'waiting_confirmation', 'paid', 'refunded'])->default('unpaid');
$table->string('payment_proof')->nullable(); // Path to uploaded image
$table->timestamp('paid_at')->nullable(); // When user uploaded proof
$table->timestamp('payment_confirmed_at')->nullable(); // When admin confirmed
$table->foreignId('payment_confirmed_by')->nullable()->constrained('users'); // Admin who confirmed
$table->text('payment_notes')->nullable(); // User notes or admin rejection reason
```

---

## 📋 Payment Status Flow

### Status Transitions:

```
┌─────────────────────────────────────────────────────────────┐
│ BOOKING CREATED                                             │
│ Status: pending                                             │
│ Payment: unpaid                                             │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
         ┌─────────────────┐
         │  User chooses   │
         │ payment method  │
         └────┬────────┬───┘
              │        │
      ┌───────┘        └──────┐
      │                       │
      ▼                       ▼
┌──────────┐           ┌────────────────┐
│   CASH   │           │ TRANSFER/QRIS  │
│          │           │   /E-WALLET    │
└────┬─────┘           └────┬───────────┘
     │                      │
     │ Auto-confirm         │ Upload proof
     │                      │
     ▼                      ▼
┌──────────┐           ┌────────────────────┐
│   PAID   │           │ WAITING_CONFIRMATION│
│(no proof)│           │  (with proof)      │
└──────────┘           └────┬───────────────┘
                            │
                   ┌────────┴────────┐
                   │                 │
              Admin Approve     Admin Reject
                   │                 │
                   ▼                 ▼
              ┌──────────┐      ┌──────────┐
              │   PAID   │      │  UNPAID  │
              │(confirmed)│      │(re-upload)│
              └──────────┘      └──────────┘
```

### Status Descriptions:

| Status | Description | User Action | Admin Action |
|--------|-------------|-------------|--------------|
| `unpaid` | Belum dibayar | Upload bukti pembayaran | - |
| `waiting_confirmation` | Bukti sudah diupload | Wait for verification | Approve/Reject |
| `paid` | Sudah dibayar & confirmed | - | - |
| `refunded` | Booking dibatalkan & refund processed | - | - |

---

## 🔧 Technical Implementation

### Livewire Component: `PaymentForm`

**Location**: `app/Livewire/PaymentForm.php`

**Key Methods**:
- `openModal()` - Check if booking can be paid
- `submitPayment()` - Handle payment submission
- `updatedPaymentMethod()` - Clear proof when selecting cash

**Validation**:
```php
'paymentMethod' => 'required|in:cash,bank_transfer,qris,e_wallet'
'paymentProof' => 'required_unless:paymentMethod,cash|image|max:2048'
'paymentNotes' => 'nullable|string|max:500'
```

**Auto-Confirmation Logic**:
```php
if ($this->paymentMethod === 'cash') {
    // Auto-confirm for cash
    $booking->payment_status = 'paid';
    $booking->payment_confirmed_at = now();
    $booking->status = 'confirmed';
} else {
    // Wait for admin verification
    $booking->payment_status = 'waiting_confirmation';
    $booking->paid_at = now();
}
```

### Filament Admin Actions

**Location**: `app/Filament/Resources/Bookings/BookingResource.php`

#### 1. View Payment Proof
```php
Action::make('view_payment')
    ->modalWidth(MaxWidth::TwoExtraLarge)
    ->infolist([
        ImageEntry::make('payment_proof')
            ->disk('public')
            ->height(500)
    ])
    ->visible(fn ($record) => $record->payment_proof !== null)
```

#### 2. Approve Payment
```php
Action::make('approve_payment')
    ->action(function (Booking $record): void {
        $record->update([
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
            'payment_confirmed_by' => auth()->id(),
            'status' => 'confirmed',
        ]);
    })
    ->visible(fn ($record) => $record->payment_status === 'waiting_confirmation')
```

#### 3. Reject Payment
```php
Action::make('reject_payment')
    ->form([
        Textarea::make('reject_reason')
            ->required()
    ])
    ->action(function (Booking $record, array $data): void {
        $record->update([
            'payment_status' => 'unpaid',
            'payment_notes' => 'DITOLAK: ' . $data['reject_reason'],
        ]);
    })
```

---

## 🎨 UI Components

### Dashboard Card - Payment Status

**Status Badge Colors**:
- ❌ **Unpaid**: Red background - Alert user to pay
- ⏳ **Waiting Confirmation**: Yellow background - Proof uploaded
- ✅ **Paid**: Green background - Payment confirmed

**Payment Button**:
```html
@if($booking->payment_status === 'unpaid')
    <!-- Tombol "Bayar Sekarang" -->
@elseif($booking->payment_status === 'waiting_confirmation')
    <!-- Tombol "Ubah Bukti Pembayaran" -->
@endif
```

### Payment Modal

**Sections**:
1. **Booking Summary** - Lapangan, tanggal, waktu, total harga
2. **Payment Method Selection** - 4 radio buttons dengan emoji
3. **Payment Instructions** - Dynamic berdasarkan metode dipilih
4. **Upload Proof** - File input (only for non-cash)
5. **Payment Notes** - Optional textarea

**Modal Features**:
- Full screen overlay with backdrop blur
- Teleport to body (z-index 99999)
- Scrollable content
- Loading states for file upload
- Real-time validation

---

## 📸 Screenshots Expected

### User Flow:
1. Dashboard with "Bayar Sekarang" button
2. Payment modal with method selection
3. Bank transfer instructions + upload form
4. "Menunggu Konfirmasi" status card

### Admin Flow:
1. Filament table with payment status badges
2. "Lihat Bukti" modal dengan full image
3. Approve/Reject buttons
4. Payment confirmed notification

---

## 🧪 Testing Guide

### User Testing:

#### Test 1: Cash Payment
```
1. Create booking
2. Open dashboard
3. Click "Bayar Sekarang"
4. Select "Bayar di Tempat"
5. Click "Konfirmasi Pembayaran"
✅ Expected: Status → "Paid", no proof required
```

#### Test 2: Bank Transfer Payment
```
1. Create booking
2. Click "Bayar Sekarang"
3. Select "Transfer Bank"
4. Upload bukti transfer (image < 2MB)
5. Add notes (optional)
6. Click "Konfirmasi Pembayaran"
✅ Expected: Status → "Waiting Confirmation", proof uploaded
```

#### Test 3: Re-upload Proof
```
1. Booking with status "Waiting Confirmation"
2. Click "Ubah Bukti Pembayaran"
3. Select different method
4. Upload new proof
5. Submit
✅ Expected: Old proof deleted, new proof uploaded
```

### Admin Testing:

#### Test 4: Approve Payment
```
1. Login as admin (/admin)
2. Go to Pemesanan
3. Filter: Payment Status → "Menunggu Konfirmasi"
4. Click "Lihat Bukti" on a booking
5. Verify image is clear
6. Click "Terima"
7. Confirm modal
✅ Expected: 
   - Status → "Paid"
   - payment_confirmed_at set
   - payment_confirmed_by = admin ID
```

#### Test 5: Reject Payment
```
1. Find booking with "Menunggu Konfirmasi"
2. Click "Tolak"
3. Enter rejection reason: "Bukti tidak jelas"
4. Confirm
✅ Expected:
   - Status → "Unpaid"
   - payment_notes contains "DITOLAK: Bukti tidak jelas"
   - User can re-upload
```

#### Test 6: View Filters
```
1. Apply filter: Payment Status → "Belum Bayar"
2. Apply filter: Payment Method → "Transfer Bank"
3. Apply filter: Status → "Menunggu"
✅ Expected: Table shows only matching records
```

---

## 🔒 Security Considerations

### File Upload Security:
- ✅ File type validation (only images)
- ✅ File size limit (2MB)
- ✅ Stored in `storage/app/public/payment-proofs`
- ✅ Auto-delete old files when re-upload
- ⚠️ Consider adding: Image dimension validation, virus scan

### Access Control:
- ✅ User can only pay their own bookings
- ✅ Admin-only actions for approve/reject
- ✅ Payment confirmed by tracked (audit log)

### Data Integrity:
- ✅ Payment status transitions validated
- ✅ Cannot approve already paid bookings
- ✅ Cannot delete payment proof if status is `paid`

---

## 🚀 Future Enhancements

### Potential Features:
1. **Email Notifications**:
   - Send receipt after payment confirmed
   - Notify user when payment rejected
   
2. **Payment Receipts**:
   - Generate PDF invoice
   - QR code for verification
   
3. **Partial Payments**:
   - Allow down payment (DP)
   - Track remaining balance
   
4. **Payment Reminders**:
   - Auto-reminder H-1 before booking if unpaid
   - Cancel auto if unpaid H-24
   
5. **Admin Dashboard**:
   - Payment statistics widget
   - Daily revenue chart
   - Pending payments count badge

6. **Bulk Actions**:
   - Approve multiple payments at once
   - Export payment reports

7. **Payment History**:
   - Log all status changes
   - Track who rejected/approved

---

## 📞 Support & Troubleshooting

### Common Issues:

**Issue**: Upload gagal terus
- **Solution**: Check file size < 2MB, format image valid

**Issue**: Tombol "Bayar Sekarang" tidak muncul
- **Solution**: Check booking status `pending`/`confirmed` dan payment status `unpaid`/`waiting_confirmation`

**Issue**: Admin tidak bisa approve
- **Solution**: Check payment status must be `waiting_confirmation`

**Issue**: Image tidak tampil di modal admin
- **Solution**: Verify storage symlink: `php artisan storage:link --force`

### Debug Commands:
```bash
# Clear all cache
php artisan optimize:clear

# Check storage symlink
ls -l public/storage

# View payment proofs directory
ls storage/app/public/payment-proofs/

# Check Filament permissions
php artisan shield:generate --option=policies
```

---

## 📝 Configuration

### Payment Method Details
Update instructions di `resources/views/livewire/payment-form.blade.php`:

```php
// Bank accounts
Bank BCA: 1234567890 a.n. GoField
Bank Mandiri: 0987654321 a.n. GoField

// E-Wallet numbers
Dana: 08123456789 a.n. GoField
OVO: 08123456789
GoPay: 08123456789
```

### QRIS Code
Replace placeholder dengan actual QRIS image:
```html
<img src="{{ asset('images/qris-code.png') }}" alt="QRIS Code">
```

---

## ✅ Checklist - System Complete

- [x] Database migration untuk payment fields
- [x] Booking model updated dengan payment fillable & casts
- [x] PaymentForm Livewire component
- [x] Payment modal UI dengan 4 metode
- [x] File upload dengan validation
- [x] Auto-confirmation untuk cash payment
- [x] Dashboard integration dengan payment buttons
- [x] Payment status cards di dashboard
- [x] Filament admin actions (view/approve/reject)
- [x] Payment status & method columns
- [x] Payment filters di Filament
- [x] Image viewer modal untuk bukti pembayaran
- [x] Audit trail (confirmed_by & confirmed_at)
- [x] Documentation complete

---

## 🎉 Conclusion

Sistem payment manual sudah **production-ready** dengan fitur:
- Multi-metode pembayaran
- Upload & verify bukti pembayaran
- Admin approval workflow
- Audit trail lengkap
- User-friendly UI
- Security best practices

**Next Steps**: Testing menyeluruh, kemudian deploy! 🚀
