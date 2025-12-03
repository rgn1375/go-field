# Cash Payment & Point System - GoField

## 💰 **Cash Payment Flow (Bayar di Tempat)**

### ❌ **SEBELUM (Bug):**
1. User pilih "Bayar di Tempat"
2. Status otomatis `payment_status = paid` ❌
3. **TIDAK DAPAT POIN** ❌
4. Kalau cancel → **DAPAT POIN REFUND** (padahal belum bayar!) ❌

### ✅ **SESUDAH (Fixed):**
1. User pilih "Bayar di Tempat"
2. Status tetap `payment_status = unpaid` ✅
3. Customer datang ke lapangan → bayar cash
4. Admin klik **"Terima"** di admin panel
5. Status → `paid`, customer **DAPAT POIN** ✅
6. Kalau cancel sebelum bayar → **TIDAK DAPAT REFUND** ✅

---

## 🎯 **Point Earning Logic (Semua Metode Pembayaran)**

### Kapan User Dapat Poin?

| Metode Pembayaran | Dapat Poin Kapan? |
|-------------------|-------------------|
| **Cash** | Setelah admin klik "Terima" (customer sudah bayar di tempat) ✅ |
| **Transfer Bank** | Setelah admin klik "Terima" (verifikasi bukti transfer) ✅ |
| **QRIS** | Setelah admin klik "Terima" (verifikasi bukti QRIS) ✅ |
| **E-Wallet** | Setelah admin klik "Terima" (verifikasi bukti e-wallet) ✅ |

**Rule**: Poin **HANYA diberikan** saat `payment_status` berubah dari `unpaid`/`waiting_confirmation` → `paid`

---

## 🔄 **Cancellation & Refund Logic**

### Skenario 1: Cancel SEBELUM Bayar

**Cash Payment (unpaid):**
```
User booking → Pilih cash → Cancel
Result:
- refund_amount = 0
- refund_method = none
- refund_notes = "Booking dibatalkan sebelum pembayaran dikonfirmasi"
- TIDAK DAPAT POIN REFUND ✅
```

**Transfer Bank (waiting_confirmation):**
```
User booking → Upload bukti → Cancel (sebelum admin approve)
Result:
- refund_amount = 0
- refund_method = none
- refund_notes = "Booking dibatalkan sebelum pembayaran dikonfirmasi"
- TIDAK DAPAT POIN REFUND ✅
```

### Skenario 2: Cancel SETELAH Bayar

**Cash Payment (paid):**
```
User booking → Datang bayar cash → Admin approve → User cancel
Result:
- refund_amount = calculated (50% atau 100%)
- refund_method = points
- refund_notes = "Otomatis dikembalikan dalam bentuk poin..."
- DAPAT POIN REFUND ✅
```

**Transfer Bank (paid):**
```
User booking → Upload bukti → Admin approve → User cancel
Result:
- refund_amount = calculated (50% atau 100%)
- refund_method = points
- refund_notes = "Otomatis dikembalikan dalam bentuk poin..."
- DAPAT POIN REFUND ✅
```

---

## 📊 **Payment Status Flow**

### Cash Payment:
```
unpaid (pilih cash)
  ↓
pending (tunggu customer datang)
  ↓
paid (admin klik "Terima" setelah customer bayar)
  ↓
[DAPAT POIN EARNED]
```

### Non-Cash Payment:
```
unpaid (default)
  ↓
waiting_confirmation (upload bukti)
  ↓
paid (admin klik "Terima" setelah verifikasi)
  ↓
[DAPAT POIN EARNED]
```

---

## 🎁 **Point Transaction Types**

| Type | Kapan Terjadi | Deskripsi |
|------|--------------|-----------|
| **earned** | Payment confirmed | Points earned from booking #123 |
| **redeemed** | Redeem poin untuk booking | Points redeemed for booking #124 |
| **adjusted** | Cancel booking (cabut earned poin) | Points removed due to cancelled booking #123 |
| **refund** | Cancel booking (kembalikan payment) | Refund 100% (Rp 150,000) from cancelled booking #123 |

---

## ✅ **Fixed Bugs:**

1. ✅ Cash payment sekarang **dapat poin** saat admin approve
2. ✅ Cancel cash booking **tidak dapat refund** kalau belum bayar
3. ✅ Cancel non-cash booking **tidak dapat refund** kalau belum approved
4. ✅ Tombol "Terima" di admin sekarang visible untuk `unpaid` (cash) DAN `waiting_confirmation` (non-cash)

---

## 🧪 **Test Cases:**

### Test 1: Cash Payment - Normal Flow
```
1. User booking lapangan → pilih "Bayar di Tempat"
   ✅ payment_status = unpaid
2. User datang ke lapangan → bayar cash Rp 150,000
3. Admin klik "Terima"
   ✅ payment_status = paid
   ✅ User dapat 1 poin (1% dari 150k = 1.5k → 1 poin)
```

### Test 2: Cash Payment - Cancel Sebelum Bayar
```
1. User booking lapangan → pilih "Bayar di Tempat"
2. User cancel (belum datang/belum bayar)
   ✅ refund_amount = 0
   ✅ refund_method = none
   ✅ TIDAK dapat poin refund
```

### Test 3: Cash Payment - Cancel Setelah Bayar
```
1. User booking lapangan → pilih "Bayar di Tempat"
2. User datang → bayar cash → admin approve
   ✅ User dapat 1 poin earned
3. User cancel (H-24)
   ✅ refund_amount = 150,000 (100%)
   ✅ refund_method = points
   ✅ User dapat 150 poin refund (150k ÷ 1k = 150)
```

### Test 4: Transfer Bank - Cancel Sebelum Approve
```
1. User booking → upload bukti transfer
   ✅ payment_status = waiting_confirmation
2. User cancel (sebelum admin approve)
   ✅ refund_amount = 0
   ✅ TIDAK dapat poin refund
```

---

## 🔧 **Admin Panel Changes:**

### Tombol "Terima" (approve_payment)

**Sebelum:**
- Visible hanya untuk `payment_status = waiting_confirmation`
- Tidak bisa approve cash payment

**Sesudah:**
- Visible untuk `payment_status IN ('waiting_confirmation', 'unpaid')`
- Bisa approve cash payment + non-cash payment
- Semua dapat poin saat approved

### Message:
"Pembayaran berhasil dikonfirmasi dan poin telah diberikan"

---

## 💡 **Summary:**

✅ **Cash payment sekarang fair**: Dapat poin setelah benar-benar bayar  
✅ **Cancel logic fixed**: Hanya refund kalau sudah bayar  
✅ **Admin workflow clear**: Approve semua metode pembayaran dengan 1 tombol  
✅ **Point system consistent**: Semua metode pembayaran dapat poin dengan cara yang sama  

**No more free points!** 🎯
