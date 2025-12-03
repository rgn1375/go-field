# Refund System - GoField

## 📋 **Refund Policy**

### Aturan Pembatalan & Refund:
1. **>24 jam sebelum booking**: 100% refund
2. **<24 jam sebelum booking**: 50% refund  
3. **Sudah lewat waktu booking**: Tidak bisa batal, no refund

---

## 💰 **Flow Refund - Otomatis (Default)**

### Step-by-Step:

**1. User Batalkan Booking**
   - User klik "Batalkan" di dashboard
   - Sistem hitung refund berdasarkan waktu:
     - H-24: 100% dari harga booking
     - <H-24: 50% dari harga booking

**2. Sistem Auto-Process Refund**
   - **Jika sudah bayar** (`payment_status = 'paid'`):
     - Status payment → `refunded`
     - `refund_method` = `points`
     - Refund otomatis jadi **POIN**
     - Konversi: **Rp 1,000 = 1 poin**
   
   - **Jika belum bayar** (`payment_status = 'unpaid'`):
     - `refund_method` = `none`
     - Tidak ada refund (tidak ada pembayaran)

**3. Point Adjustments**
   - ✅ **Kembalikan poin yang di-redeem** (jika ada)
   - ❌ **Cabut poin earned** dari booking ini (jika ada & belum dipakai)
   - ✅ **Tambah refund sebagai poin baru**

**4. Tracking**
   - `refund_amount`: Jumlah uang yang di-refund (Rp)
   - `refund_percentage`: 50% atau 100%
   - `refund_method`: `points` (otomatis)
   - `refund_processed_at`: Timestamp saat diproses
   - `refund_notes`: "Otomatis dikembalikan dalam bentuk poin. Jika ingin refund transfer bank, hubungi admin."

---

## 🏦 **Flow Refund - Manual via Transfer Bank**

### Kapan Dibutuhkan?
- Customer **TIDAK MAU POIN**, minta refund ke rekening
- Customer complaint: "Uang saya mana?"
- Admin harus manual transfer ke rekening customer

### Step-by-Step:

**1. Customer Request Refund Transfer**
   - Customer hubungi admin (WA/email)
   - Kirim data rekening:
     - Nama Bank
     - Nomor Rekening
     - Nama Pemilik
   - Sertakan booking code

**2. Admin Cek di Filament**
   - Login ke `/admin`
   - Buka menu **Pemesanan**
   - Filter status: `Cancelled`
   - Cari booking berdasarkan booking code
   - Cek kolom **Refund**: harus ada nominal + `(Poin)`

**3. Admin Proses Transfer**
   - Transfer manual ke rekening customer
   - Jumlah = sesuai `refund_amount`
   - Screenshot bukti transfer

**4. Admin Konfirmasi di Sistem**
   - Klik tombol **"Proses Refund"** di row booking
   - Isi form:
     - **Catatan Refund**: 
       ```
       Sudah transfer Rp 150,000 ke rekening:
       BCA 1234567890 a.n. John Doe
       Tanggal: 03 Des 2025
       Ref: TRF20251203001
       ```
   - Klik **Confirm**

**5. Sistem Update Status**
   - `refund_method`: `points` → `bank_transfer`
   - `payment_status`: `refunded`
   - `refund_processed_at`: Timestamp sekarang
   - `refund_notes`: Catatan admin

**6. Notification ke Customer**
   - Email/WA: "Refund Anda sudah diproses via transfer bank"
   - Sertakan detail dari `refund_notes`

---

## 🎯 **Tombol Action di Admin Panel**

### Row Actions:

| Tombol | Visible When | Action |
|--------|-------------|--------|
| **Lihat Bukti** | `payment_proof` ada & bukan cash | Modal bukti pembayaran |
| **Terima** | `payment_status = waiting_confirmation` | Approve payment, kasih poin |
| **Tolak** | `payment_status = waiting_confirmation` | Reject payment, minta upload ulang |
| **Konfirmasi** | `status = pending` | Set status → confirmed |
| **Batalkan** | `status = pending/confirmed` | Cancel booking, auto refund poin |
| **Proses Refund** | `status = cancelled` + `refund_amount > 0` + `refund_method = points` | Manual transfer refund |
| **Edit** | Always | Edit booking data |
| **Hapus** | Always | Delete booking (hard delete) |

---

## 📊 **Kolom Refund di Table**

Display kolom **Refund** untuk booking yang `status = cancelled`:
- **Format**: `Rp 150,000 (Poin)` atau `Rp 150,000 (Transfer)`
- **Badge**: Warning color (orange)
- **Icon**: heroicon-o-banknotes

---

## 🔍 **Cek Status Refund**

### Query Database:

```php
// Booking yang sudah di-refund via poin
Booking::where('status', 'cancelled')
    ->where('refund_method', 'points')
    ->whereNotNull('refund_processed_at')
    ->get();

// Booking yang perlu refund transfer manual
Booking::where('status', 'cancelled')
    ->where('refund_method', 'points')
    ->whereNull('refund_processed_at')
    ->where('refund_amount', '>', 0)
    ->get();

// Booking yang sudah di-refund via transfer
Booking::where('status', 'cancelled')
    ->where('refund_method', 'bank_transfer')
    ->whereNotNull('refund_processed_at')
    ->get();
```

---

## 💡 **Tips untuk Admin**

1. **Selalu screenshot** bukti transfer sebelum klik "Proses Refund"
2. **Catat nomor referensi** transfer di refund_notes
3. **Pastikan nama rekening** sesuai dengan nama customer di booking
4. **Jika transfer gagal**, jangan klik "Proses Refund" dulu
5. **Customer bisa pilih**: terima poin ATAU minta transfer bank

---

## 🚨 **Troubleshooting**

### Q: Customer bilang "Saya belum terima refund"
**A**: Cek di admin panel:
- Apakah `refund_processed_at` sudah terisi?
- Apakah `refund_method` = `points` atau `bank_transfer`?
- Jika `points`: cek balance poin user, harusnya sudah bertambah
- Jika `bank_transfer`: cek `refund_notes`, konfirmasi ke bank

### Q: Tombol "Proses Refund" tidak muncul
**A**: Pastikan:
- `status` = `cancelled` ✓
- `refund_amount` > 0 ✓
- `refund_method` = `points` ✓
- `refund_processed_at` = `null` ✓

### Q: Customer mau refund tapi booking belum bayar
**A**: Tidak ada yang perlu di-refund, karena belum ada pembayaran.
Jelaskan ke customer bahwa booking sudah dibatalkan tanpa penalty.

---

## 📝 **Database Schema**

```sql
-- bookings table
refund_amount INT DEFAULT 0              -- Jumlah refund (Rp)
refund_percentage INT DEFAULT 0          -- 0, 50, atau 100
refund_method ENUM('points', 'bank_transfer', 'none') DEFAULT 'none'
refund_notes TEXT NULL                   -- Catatan admin untuk tracking
refund_processed_at TIMESTAMP NULL       -- Kapan refund diproses
```

---

## 🎉 **Summary**

✅ **Otomatis**: Refund via poin (default, instant)  
✅ **Manual**: Refund via transfer bank (by request, admin process)  
✅ **Tracking**: Semua refund tercatat dengan detail lengkap  
✅ **Transparan**: Customer bisa lihat status refund di dashboard  
✅ **Admin-friendly**: 1 klik untuk proses refund transfer  

**Flow ini balance antara automation & flexibility!** 🚀
