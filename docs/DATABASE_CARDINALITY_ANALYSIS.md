# Analisis Kardinalitas Database GoField

## Executive Summary
Kardinalitas database GoField **SUDAH LOGIS** dan mengikuti best practices untuk sistem booking. Namun ada **1 masalah potensial** dan **2 rekomendasi perbaikan** untuk efisiensi jangka panjang.

---

## Struktur Kardinalitas Saat Ini

### ✅ **LOGIS - One-to-Many Relationships**

#### 1. **User → Bookings** (1:N)
```
User --< Bookings
```
- **Kardinalitas**: 1 user bisa punya banyak bookings ✅
- **Foreign Key**: `bookings.user_id → users.id`
- **Nullable**: TRUE (guest booking support) ✅
- **Logis?**: **YES** - Setiap user bisa booking berkali-kali
- **Index**: ✅ Ada `idx_bookings_user_date`

**Contoh Real**:
- User "John" → Booking #1, #2, #3 (futsal minggu depan, badminton bulan depan)

---

#### 2. **Lapangan → Bookings** (1:N)
```
Lapangan --< Bookings
```
- **Kardinalitas**: 1 lapangan bisa di-booking banyak kali ✅
- **Foreign Key**: `bookings.lapangan_id → lapangan.id`
- **Nullable**: FALSE (required) ✅
- **Logis?**: **YES** - Lapangan futsal A dibooking ratusan kali per bulan
- **Index**: ✅ Ada di cursor pagination indexes

**Contoh Real**:
- Lapangan Futsal A → Booking jam 08:00, 10:00, 14:00, 16:00 (same day, different slots)

---

#### 3. **PaymentMethod → Bookings** (1:N)
```
PaymentMethod --< Bookings
```
- **Kardinalitas**: 1 payment method bisa dipakai banyak bookings ✅
- **Foreign Key**: `bookings.payment_method_id → payment_methods.id`
- **Nullable**: TRUE ✅
- **Logis?**: **YES** - Transfer BCA dipakai ratusan kali
- **Index**: ❌ Tidak ada (optional, tapi tidak critical)

**Contoh Real**:
- BCA Transfer → Booking #1, #50, #99, #200

---

#### 4. **SportType → Lapangan** (1:N)
```
SportType --< Lapangan
```
- **Kardinalitas**: 1 jenis olahraga → banyak lapangan ✅
- **Foreign Key**: `lapangan.sport_type_id → sport_types.id`
- **Nullable**: FALSE (required) ✅
- **Logis?**: **YES** - Sport type "Futsal" punya 5 lapangan berbeda
- **Index**: ✅ Implicit (foreign key)

**Contoh Real**:
- Futsal → Lapangan A, B, C, D, E
- Badminton → Lapangan 1, 2, 3

---

#### 5. **User → UserPoints** (1:N)
```
User --< UserPoints
```
- **Kardinalitas**: 1 user → banyak transaksi poin ✅
- **Foreign Key**: `user_points.user_id → users.id`
- **Nullable**: FALSE (required) ✅
- **Logis?**: **YES** - Setiap earn/redeem/refund = 1 row baru (audit trail)
- **Index**: ✅ Ada

**Contoh Real**:
- User "John":
  - Row 1: +1000 points (earned from booking #10)
  - Row 2: -500 points (redeemed for booking #11)
  - Row 3: +100 points (refund from cancelled booking #10)

---

#### 6. **Booking → UserPoints** (1:N)
```
Booking --< UserPoints
```
- **Kardinalitas**: 1 booking → banyak point transactions ✅
- **Foreign Key**: `user_points.booking_id → bookings.id`
- **Nullable**: TRUE ✅
- **Logis?**: **YES** - Satu booking bisa punya:
  - 1 row: earned points (+1000)
  - 1 row: redeemed points (-500)
  - 1 row: refund points (+100)
- **Index**: ✅ Ada

**Contoh Real**:
- Booking #10 (Rp 100,000):
  - UserPoint #1: +1000 points (earned 1%)
  - UserPoint #2: -500 points (redeemed saat booking)
  - UserPoint #3: +100 points (refund setelah cancel)

---

### ✅ **LOGIS - One-to-One Relationships**

#### 7. **Booking → Invoice** (1:1)
```
Booking --|| Invoice
```
- **Kardinalitas**: 1 booking → 1 invoice ✅
- **Foreign Key**: `invoices.booking_id → bookings.id`
- **Nullable**: FALSE + UNIQUE ✅
- **Logis?**: **YES** - Setiap booking punya 1 invoice PDF
- **Index**: ✅ UNIQUE constraint

**Contoh Real**:
- Booking #10 → Invoice #INV-20251204-00010 (one-to-one)

---

### ⚠️ **POTENSIAL MASALAH - Transaksi Tidak Dipakai?**

#### 8. **Booking → Transactions** (1:N)
```
Booking --< Transactions
```
- **Kardinalitas**: 1 booking → banyak transaksi payment ✅
- **Foreign Key**: `transactions.booking_id → bookings.id`
- **Status**: **TABLE TIDAK DIPAKAI?** ⚠️
- **Logis?**: **YES** - Tapi model Transaction tidak dipakai di codebase

**Analisis**:
```php
// app/Models/Booking.php
public function transactions() {
    return $this->hasMany(Transaction::class); // ✅ Defined
}

// BUT: Tidak ada kode yang pakai $booking->transactions()
// Payment tracking ada di bookings.payment_status directly
```

**Rekomendasi**:
- ❌ **DROP** table `transactions` jika tidak dipakai (clean up)
- ✅ **ATAU** implement payment history tracking dengan transactions

---

## 🔍 Analisis Kardinalitas Lebih Detail

### Booking Table - Hub Utama
```
                    ┌─────────────┐
                    │    User     │
                    └──────┬──────┘
                           │ 1
                           │
                           │ N
                    ┌──────▼──────┐
     ┌──────────────┤   Booking   ├────────────┐
     │              └──────┬──────┘            │
     │ N                   │ 1                 │ N
     │                     │                   │
┌────▼─────┐         ┌────▼────┐        ┌─────▼──────┐
│UserPoint │         │ Invoice │        │Transaction │
│  (N:1)   │         │  (1:1)  │        │   (N:1)    │
└──────────┘         └─────────┘        └────────────┘

     1 │                                       │ N
       │                                       │
       │ N                                     │
    ┌──▼───────┐                        ┌─────▼─────────┐
    │ Lapangan │                        │PaymentMethod  │
    └──────────┘                        └───────────────┘
```

**Kardinalitas Benar**: ✅ Tidak ada circular dependency, structure normal

---

## ❗ Masalah & Rekomendasi

### 🔴 **MASALAH 1: Table `transactions` Tidak Dipakai**

**Bukti**:
```bash
# Cari penggunaan Transaction model di codebase
grep -r "->transactions()" app/  # ❌ Tidak ada hasil
grep -r "Transaction::" app/     # ❌ Hanya model definition
```

**Impact**:
- ❌ Dead code di database (wasted storage)
- ❌ Migration history bloated
- ❌ Confusion untuk developer baru

**Solusi**:
1. **Option A**: Drop table `transactions` (recommended jika tidak dipakai)
   ```php
   Schema::dropIfExists('transactions');
   ```

2. **Option B**: Implement payment history feature
   - Track setiap perubahan payment_status
   - Log payment confirmations
   - Audit trail untuk admin

---

### 🟡 **REKOMENDASI 1: Tambah Index pada payment_method_id**

**Saat Ini**:
```sql
-- bookings.payment_method_id: ❌ No index
```

**Dampak**:
- Query lambat saat filter by payment method
- Admin dashboard report by payment method slow

**Solusi**:
```php
Schema::table('bookings', function (Blueprint $table) {
    $table->index('payment_method_id'); // Simple index
});
```

**Use Case**:
```sql
-- Admin report: Berapa booking pakai BCA Transfer?
SELECT COUNT(*) FROM bookings WHERE payment_method_id = 2; -- ❌ Full table scan
```

---

### 🟡 **REKOMENDASI 2: Pertimbangkan Soft Delete untuk Bookings**

**Saat Ini**:
- Status: 'cancelled' = booking masih ada di database ✅
- Tidak ada `deleted_at` column

**Benefit Soft Delete**:
- ✅ Bisa restore cancelled booking
- ✅ Admin audit trail tetap utuh
- ✅ Query performance (hide deleted by default)

**Implementation**:
```php
// app/Models/Booking.php
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model {
    use SoftDeletes; // Tambah trait
}

// Migration
Schema::table('bookings', function (Blueprint $table) {
    $table->softDeletes(); // Tambah deleted_at column
});
```

**Impact**:
- Status 'cancelled' + hard delete → Data hilang permanent ❌
- Status 'cancelled' + soft delete → Data tetap ada untuk audit ✅

---

## ✅ Kesimpulan

### Kardinalitas Secara Umum: **LOGIS DAN BENAR** ✅

| Relasi | Kardinalitas | Status | Masalah |
|--------|--------------|--------|---------|
| User → Bookings | 1:N | ✅ Logis | None |
| Lapangan → Bookings | 1:N | ✅ Logis | None |
| PaymentMethod → Bookings | 1:N | ✅ Logis | Missing index |
| SportType → Lapangan | 1:N | ✅ Logis | None |
| User → UserPoints | 1:N | ✅ Logis | None |
| Booking → UserPoints | 1:N | ✅ Logis | None |
| Booking → Invoice | 1:1 | ✅ Logis | None |
| Booking → Transactions | 1:N | ⚠️ Tidak dipakai | Dead code? |

### Score: **8/8 Logis** (100%)

### Action Items:
1. ⚠️ **Investigate**: Apakah `transactions` table benar-benar tidak dipakai?
2. 🟡 **Optional**: Tambah index `payment_method_id` untuk performa
3. 🟡 **Optional**: Implement soft deletes untuk better audit trail

---

## Perbandingan dengan Best Practices

### ✅ Yang Sudah Benar:
1. **Normalized Structure** - No data redundancy
2. **Foreign Keys Defined** - Referential integrity
3. **Nullable Appropriate** - Guest booking support
4. **Composite Indexes** - Cursor pagination optimized
5. **Audit Trail** - UserPoints log semua transaksi
6. **One-to-One Invoice** - Clean separation

### 🟡 Yang Bisa Ditingkatkan:
1. **Dead Code Cleanup** - Drop unused `transactions` table
2. **Missing Indexes** - Add `payment_method_id` index
3. **Soft Deletes** - Better data retention policy

---

## Diagram Entity-Relationship (ER)

```mermaid
erDiagram
    USERS ||--o{ BOOKINGS : "has many"
    USERS ||--o{ USER_POINTS : "has many"
    
    SPORT_TYPES ||--o{ LAPANGAN : "has many"
    
    LAPANGAN ||--o{ BOOKINGS : "has many"
    
    PAYMENT_METHODS ||--o{ BOOKINGS : "used in"
    PAYMENT_METHODS ||--o{ TRANSACTIONS : "used in"
    
    BOOKINGS ||--|| INVOICES : "has one"
    BOOKINGS ||--o{ USER_POINTS : "has many"
    BOOKINGS ||--o{ TRANSACTIONS : "has many"
    
    USERS {
        int id PK
        string name
        string email UK
        int points_balance
        boolean is_admin
    }
    
    BOOKINGS {
        int id PK
        int user_id FK "nullable"
        int lapangan_id FK
        int payment_method_id FK "nullable"
        date tanggal
        time jam_mulai
        time jam_selesai
        decimal harga
        enum status
        enum payment_status
    }
    
    USER_POINTS {
        int id PK
        int user_id FK
        int booking_id FK "nullable"
        int points
        enum type
        int balance_after
    }
    
    INVOICES {
        int id PK
        int booking_id FK UK
        string invoice_number UK
        string pdf_path
    }
    
    LAPANGAN {
        int id PK
        int sport_type_id FK
        string title
        decimal price
        boolean status
    }
    
    SPORT_TYPES {
        int id PK
        string name UK
        string slug UK
    }
    
    PAYMENT_METHODS {
        int id PK
        string code UK
        string name
        boolean is_active
    }
    
    TRANSACTIONS {
        int id PK
        int booking_id FK
        int payment_method_id FK
        decimal amount
        enum status
    }
```

---

## Referensi
- **Models**: `app/Models/`
- **Migrations**: `database/migrations/`
- **Documentation**: This file
