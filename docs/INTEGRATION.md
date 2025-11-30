# 🔄 Integrasi Filament Admin dengan Frontend

## Gambaran Umum
Aplikasi ini menggunakan **Filament 4** sebagai admin panel dan **Livewire 2** untuk frontend interaktif. Data dikelola melalui admin panel dan secara otomatis ditampilkan di frontend.

---

## 🏗️ Arsitektur Integrasi

### 1. **Lapangan Management (Filament → Frontend)**

#### Admin Panel (Filament)
- **Resource**: `app/Filament/Resources/Lapangans/LapanganResource.php`
- **Fitur**:
  - ✅ CRUD Lapangan (Create, Read, Update, Delete)
  - ✅ Upload multiple images (max 3)
  - ✅ Set kategori: Futsal, Basket, Volly, Badminton, Tennis
  - ✅ Set harga per sesi
  - ✅ Status: Active (1), Inactive (0), Under Maintenance (2)
  - ✅ Rich text editor untuk deskripsi
  - ✅ Filter by category dan status
  - ✅ Search by title

#### Frontend Display
- **Controller**: `app/Http/Controllers/HomeController.php`
  ```php
  // Hanya menampilkan lapangan aktif dengan pagination
  $lapangan = Lapangan::where('status', 1)->paginate(6);
  ```

- **View**: `resources/views/home.blade.php`
  - Menampilkan grid lapangan dengan card design
  - Category icons dinamis berdasarkan kategori
  - Pagination dengan design custom
  - Link ke halaman detail

- **Detail Page**: `resources/views/detail.blade.php`
  - Gallery images (dari JSON array)
  - Informasi lengkap lapangan
  - Integrated booking form

---

### 2. **Booking Management (Frontend → Filament)**

#### Frontend (Livewire Component)
- **Component**: `app/Livewire/BookingForm.php`
- **View**: `resources/views/livewire/booking-form.blade.php`
- **Flow**:
  1. User memilih tanggal (7 hari ke depan)
  2. System load booked slots dari database
  3. Generate available time slots (1 jam interval)
  4. User pilih slot tersedia
  5. Fill form: Nama & No. Telepon
  6. Submit → Create booking dengan status `confirmed`

#### Admin Panel (Filament)
- **Resource**: `app/Filament/Resources/Bookings/BookingResource.php`
- **Fitur**:
  - ✅ View all bookings
  - ✅ Filter by status: pending, confirmed, cancelled, completed
  - ✅ Filter by lapangan
  - ✅ Search by nama pemesan atau nomor telepon
  - ✅ Copy nomor telepon dengan 1 click
  - ✅ Sortable columns
  - ✅ Badge status dengan warna:
    - 🟡 Pending (warning)
    - 🟢 Confirmed (success)
    - 🔴 Cancelled (danger)
    - 🔵 Completed (info)
  - ✅ View lapangan category
  - ✅ Icon indicators

---

### 3. **Settings Management**

#### Admin Panel
- **Resource**: `app/Filament/Resources/Settings/SettingResource.php`
- **Key Settings**:
  - `jam_buka`: Jam buka operasional (default: 06:00)
  - `jam_tutup`: Jam tutup operasional (default: 21:00)

#### Frontend Integration
- **BookingForm.php** membaca settings untuk generate time slots:
  ```php
  $jamBuka = Setting::where('key', 'jam_buka')->first();
  $jamTutup = Setting::where('key', 'jam_tutup')->first();
  ```

---

## 🔄 Data Flow

### Lapangan: Admin → Frontend
```
Admin Creates Lapangan
    ↓
Saved to Database (lapangan table)
    ↓
Frontend Controller fetches active lapangans
    ↓
Displayed in home page with pagination
    ↓
User clicks "Booking Sekarang"
    ↓
Detail page with BookingForm component
```

### Booking: Frontend → Admin
```
User fills BookingForm
    ↓
Livewire validates availability
    ↓
Creates Booking record (status: confirmed)
    ↓
Admin sees new booking in Filament panel
    ↓
Admin can filter, search, manage status
```

---

## 🎯 Key Integration Points

### 1. **Model Relationships**
```php
// Booking.php
public function lapangan()
{
    return $this->belongsTo(Lapangan::class);
}
```

### 2. **Status Synchronization**
- **Lapangan Status**:
  - `1` = Active → Tampil di frontend
  - `0` = Inactive → Hidden
  - `2` = Under Maintenance → Hidden

- **Booking Status**:
  - `pending` → Menunggu konfirmasi
  - `confirmed` → Dikonfirmasi (default dari frontend)
  - `cancelled` → Dibatalkan
  - `completed` → Selesai

### 3. **Image Handling**
- Images stored as JSON array in `image` column
- Uploaded via Filament FileUpload to `storage/app/public/lapangan-images/`
- Accessed via `asset('storage/lapangan-images/{filename}')`
- Multiple images support (max 3)

### 4. **Real-time Availability**
- BookingForm loads booked slots on date selection
- Prevents double booking with overlap detection
- Time slots marked as booked automatically

---

## 🔧 Configuration

### Storage Link
Pastikan storage link sudah dibuat:
```bash
php artisan storage:link
```

### Database Seeder
Populate initial data:
```bash
php artisan db:seed
```
Seeds:
- Admin user (admin@admin.com / admin123)
- Settings (jam_buka, jam_tutup)
- Sample lapangans (6 different sports)

---

## 📱 Admin Access

**URL**: http://localhost:8000/admin

**Default Credentials**:
- Email: `admin@admin.com`
- Password: `admin123`

**Navigation**:
- 📋 Pemesanan (Bookings)
- 🏢 Lapangan (Courts/Fields)
- ⚙️ Settings

---

## 🎨 Frontend Routes

| Route | Controller | Description |
|-------|-----------|-------------|
| `/` | `HomeController@index` | Home page dengan list lapangan (paginated) |
| `/detail/{id}` | `HomeController@detail` | Detail lapangan dengan booking form |

---

## ✅ Checklist Integration

### Admin Panel (Filament)
- ✅ Lapangan CRUD with image upload
- ✅ Category selection (Futsal, Basket, Volly, Badminton, Tennis)
- ✅ Price input with currency mask (IDR)
- ✅ Rich text description editor
- ✅ Status management
- ✅ Booking list with filters
- ✅ Status badges and icons
- ✅ Searchable columns
- ✅ Settings management

### Frontend
- ✅ Display active lapangans only
- ✅ Pagination (6 per page)
- ✅ Category icons mapping
- ✅ Interactive booking form
- ✅ Real-time slot availability
- ✅ Date picker (7 days ahead)
- ✅ Time slot selection
- ✅ Form validation
- ✅ Success/error messages
- ✅ Responsive design
- ✅ Modern UI/UX with animations

### Data Integrity
- ✅ Status filtering (only active shown)
- ✅ Overlap detection (prevent double booking)
- ✅ Relationship integrity (Booking → Lapangan)
- ✅ Settings integration (operational hours)
- ✅ Image storage via public disk
- ✅ Consistent category naming

---

## 🚀 Development Workflow

### Adding New Lapangan
1. Login ke admin panel
2. Navigate to **Lapangan** menu
3. Click **Create**
4. Fill form:
   - Nama Lapangan
   - Kategori (dropdown)
   - Deskripsi (rich text)
   - Harga per Sesi
   - Upload Images (max 3)
   - Set Status (Active)
5. Save → Langsung muncul di frontend!

### Managing Bookings
1. Frontend user creates booking via BookingForm
2. Booking saved with status `confirmed`
3. Admin receives notification (in Filament)
4. Admin can:
   - View details
   - Filter by status/lapangan
   - Search by nama/telepon
   - Copy phone number
   - Update status if needed

### Updating Settings
1. Navigate to **Settings** menu
2. Edit `jam_buka` atau `jam_tutup`
3. Save → Time slots di BookingForm updated automatically

---

## 🐛 Troubleshooting

### Images not showing
```bash
php artisan storage:link
```

### Old categories not matching
Update seeder atau manual edit via admin panel:
- Use: `Futsal`, `Basket`, `Volly`, `Badminton`, `Tennis`
- NOT: `futsal`, `basketball`, `volleyball`

### Pagination not working
Check controller:
```php
// ✅ Correct
$lapangan = Lapangan::where('status', 1)->paginate(6);

// ❌ Wrong
$lapangan = Lapangan::all();
```

### Booking overlap issues
BookingForm has built-in overlap detection. If issues persist:
1. Check database for cancelled bookings affecting slots
2. Verify `status != 'cancelled'` in query
3. Check time format (H:i) consistency

---

## 📊 Database Tables

### `lapangan`
- id, title, category, description, price, image (JSON), status, timestamps

### `bookings`
- id, lapangan_id, tanggal, jam_mulai, jam_selesai, nama_pemesan, nomor_telepon, status, timestamps

### `settings`
- id, key, value, description, timestamps

---

## 🔐 Security Notes

- ✅ Admin panel protected by authentication
- ✅ Frontend booking validated server-side
- ✅ Overlap detection prevents double booking
- ✅ Status filtering prevents showing inactive courts
- ✅ Input validation on both Filament and Livewire
- ✅ CSRF protection enabled

---

**Created**: November 4, 2025  
**Version**: 1.0  
**Framework**: Laravel 12 + Filament 4 + Livewire 2
