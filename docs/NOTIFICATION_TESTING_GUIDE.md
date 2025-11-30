# 📨 Panduan Testing Sistem Notifikasi SportBooking

## ✅ Status Implementasi

### Fitur yang Telah Diimplementasikan
- ✅ Konfigurasi environment (SMTP + Fonnte WhatsApp)
- ✅ Custom WhatsApp notification channel dengan formatting nomor Indonesia
- ✅ 3 Jenis notifikasi dengan dual-channel (Email + WhatsApp):
  - `BookingConfirmed` - Notifikasi setelah booking berhasil
  - `BookingCancelled` - Notifikasi pembatalan booking (dengan alasan)
  - `BookingReminder` - Reminder H-24 sebelum jadwal booking
- ✅ Migrasi database: tambah kolom `email` di tabel bookings
- ✅ Form booking frontend: input email customer
- ✅ Filament admin actions: tombol Cancel dengan form alasan pembatalan
- ✅ Scheduled command: kirim reminder otomatis setiap hari jam 09:00
- ✅ Queue integration: semua notifikasi diproses async
- ✅ Error handling: notifikasi gagal tidak memblokir booking

---

## 🔧 Langkah 1: Konfigurasi Environment

### A. Setup Email (Gmail SMTP)

1. **Dapatkan App Password Gmail:**
   - Buka https://myaccount.google.com/security
   - Aktifkan 2-Step Verification
   - Buka https://myaccount.google.com/apppasswords
   - Generate App Password untuk "Mail"
   - Salin 16-digit password yang dihasilkan

2. **Update `.env`:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=emailanda@gmail.com
   MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # App Password dari step 1
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=emailanda@gmail.com
   MAIL_FROM_NAME="SportBooking"
   ```

3. **Testing Konfigurasi:**
   ```powershell
   php artisan tinker
   
   # Kirim test email
   Mail::raw('Test email dari SportBooking', function($message) {
       $message->to('emailtujuan@example.com')
               ->subject('Test Email');
   });
   ```

### B. Setup WhatsApp (Fonnte API)

1. **Daftar Akun Fonnte:**
   - Buka https://fonnte.com/
   - Klik "Daftar" atau "Register"
   - Verifikasi email
   - Login ke dashboard

2. **Dapatkan API Token:**
   - Masuk ke dashboard Fonnte
   - Klik menu "Account" → "Settings"
   - Salin "Token" yang ditampilkan

3. **Update `.env`:**
   ```env
   FONNTE_API_KEY=your_fonnte_token_here
   FONNTE_URL=https://api.fonnte.com/send
   ```

4. **Testing Konfigurasi:**
   ```powershell
   php artisan tinker
   
   # Test WhatsApp channel
   use App\Channels\WhatsAppChannel;
   use Illuminate\Notifications\Notification;
   
   $channel = new WhatsAppChannel();
   # Verifikasi token tersimpan:
   config('services.fonnte.api_key');
   ```

---

## 🚀 Langkah 2: Persiapan Testing

### A. Jalankan Services yang Diperlukan

```powershell
# Dari root directory: s:\xampp\htdocs\BookingLapang

# Opsi 1: Jalankan semua services (recommended)
composer dev

# Opsi 2: Manual (buka 4 terminal terpisah)
# Terminal 1: PHP Development Server
php artisan serve

# Terminal 2: Queue Worker (PENTING untuk notifikasi async)
php artisan queue:work --tries=3

# Terminal 3: Log Monitor
php artisan pail

# Terminal 4: Vite Dev Server
npm run dev
```

**CATATAN PENTING:** Queue worker HARUS berjalan untuk mengirim notifikasi!

### B. Verifikasi Database

```powershell
# Pastikan migrasi terbaru sudah dijalankan
php artisan migrate

# Check kolom email di tabel bookings
php artisan tinker
Schema::hasColumn('bookings', 'email');  # Harus return true
```

### C. Verifikasi Storage Link

```powershell
# Pastikan storage link exists untuk upload gambar
php artisan storage:link
```

---

## 🧪 Skenario Testing

### 1️⃣ Test Booking Confirmation (Frontend → Email + WhatsApp)

**Langkah:**
1. Buka browser: http://127.0.0.1:8000
2. Klik salah satu lapangan
3. Isi form booking:
   - **Tanggal:** Pilih tanggal besok
   - **Waktu:** Pilih slot yang available
   - **Nama:** John Doe
   - **Nomor Telepon:** 081234567890
   - **Email:** emailanda@gmail.com (gunakan email yang bisa diakses)
4. Klik "Pesan Sekarang"
5. Lihat success message: "Booking berhasil! Silakan cek email dan WhatsApp..."

**Expected Results:**
- ✅ Booking tersimpan dengan status `confirmed`
- ✅ Email diterima di inbox dengan subject "Pemesanan Dikonfirmasi"
- ✅ WhatsApp diterima dengan format:
  ```
  ✅ PEMESANAN DIKONFIRMASI

  Halo John Doe!
  Pemesanan Anda telah dikonfirmasi.

  📋 Detail Pemesanan:
  - No. Booking: #00001
  - Lapangan: [Nama Lapangan]
  ...
  ```

**Troubleshooting:**
- Jika notifikasi tidak terkirim:
  - Check terminal queue:work, lihat error message
  - Check `storage/logs/laravel.log`
  - Jalankan: `php artisan queue:failed` untuk lihat failed jobs
  - Retry failed jobs: `php artisan queue:retry all`

### 2️⃣ Test Booking Cancellation (Admin → Email + WhatsApp)

**Langkah:**
1. Login admin: http://127.0.0.1:8000/admin
   - Email: admin@admin.com
   - Password: admin123
2. Klik menu "Pemesanan"
3. Pilih booking dengan status "Confirmed"
4. Klik tombol "Batalkan" (ikon X merah)
5. Isi form alasan pembatalan:
   ```
   Lapangan sedang maintenance darurat. Mohon maaf atas ketidaknyamanan ini.
   ```
6. Klik "Confirm"

**Expected Results:**
- ✅ Status booking berubah jadi `cancelled`
- ✅ Email pembatalan terkirim dengan alasan
- ✅ WhatsApp pembatalan terkirim dengan format:
  ```
  ❌ PEMESANAN DIBATALKAN

  Halo John Doe,
  Maaf, pemesanan Anda telah dibatalkan.

  📋 Detail Pemesanan:
  - No. Booking: #00001
  ...

  📝 Alasan Pembatalan:
  Lapangan sedang maintenance darurat. Mohon maaf...
  ```

**Verifikasi di Admin Panel:**
- Status badge berubah jadi merah "Dibatalkan"
- Tombol "Batalkan" hilang (tidak bisa cancel lagi)
- Success notification muncul

### 3️⃣ Test Booking Reminder (Scheduled Command)

**Cara 1: Manual Testing (Recommended untuk testing awal)**

```powershell
# Jalankan command secara manual
php artisan bookings:send-reminders
```

**Expected Output:**
```
📧 Mengirim reminder untuk 3 pemesanan...

✅ Reminder terkirim ke John Doe untuk pemesanan #00001
✅ Reminder terkirim ke Jane Smith untuk pemesanan #00002
✅ Reminder terkirim ke Bob Wilson untuk pemesanan #00003

📊 Summary:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total Bookings: 3
✅ Berhasil: 3
❌ Gagal: 0
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Expected Email/WhatsApp:**
```
⏰ PENGINGAT PEMESANAN

Halo John Doe!
Pemesanan Anda akan dimulai dalam 24 jam.

📋 Detail Pemesanan:
- No. Booking: #00001
- Lapangan: Lapangan Futsal A
- Tanggal: 31 Desember 2025
- Waktu: 10:00 - 11:00

📝 Checklist Persiapan:
✓ KTP/identitas
✓ Pakaian olahraga
✓ Sepatu olahraga
✓ Handuk
✓ Botol minum

Sampai jumpa besok! 🙌
```

**Cara 2: Testing Scheduler (Full Automation)**

1. **Setup Cron (Production):**
   ```bash
   # Tambahkan ke crontab
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Testing di Development:**
   ```powershell
   # Terminal terpisah, jalankan scheduler secara continuous
   php artisan schedule:work
   ```

**Verifikasi:**
- Setiap hari jam 09:00, command akan auto-run
- Check logs: `storage/logs/laravel.log`
- Search log entry: "Mengirim reminder untuk X pemesanan"

---

## 🐛 Troubleshooting Common Issues

### Issue 1: Email Tidak Terkirim

**Diagnosis:**
```powershell
# Check mail config
php artisan config:cache
php artisan tinker
config('mail.mailer');  # Harus return 'smtp', bukan 'log'
```

**Solusi:**
- Pastikan App Password Gmail benar (16 digit tanpa spasi)
- Check Gmail "Less secure app access" disabled (harus gunakan App Password)
- Coba port 465 + MAIL_ENCRYPTION=ssl jika port 587 bermasalah
- Check firewall tidak block port 587/465

### Issue 2: WhatsApp Tidak Terkirim

**Diagnosis:**
```powershell
# Check Fonnte config
php artisan tinker
config('services.fonnte.api_key');  # Harus ada value
config('services.fonnte.url');  # Harus ada URL
```

**Solusi:**
- Pastikan token Fonnte valid (login ke dashboard, check status)
- Check saldo Fonnte cukup
- Test manual via Postman:
  ```
  POST https://api.fonnte.com/send
  Headers:
    Authorization: YOUR_TOKEN_HERE
  Body (form-data):
    target: 6281234567890
    message: Test from Postman
  ```
- Check nomor HP format (62xxx, bukan 0xxx)

### Issue 3: Queue Job Stuck/Failed

**Diagnosis:**
```powershell
# Check failed jobs
php artisan queue:failed

# Check jobs table
php artisan tinker
DB::table('jobs')->count();  # Lihat jumlah pending jobs
```

**Solusi:**
```powershell
# Retry failed jobs
php artisan queue:retry all

# Clear semua jobs (HATI-HATI!)
php artisan queue:flush

# Restart queue worker
# Ctrl+C di terminal queue:work, lalu jalankan lagi
php artisan queue:work --tries=3 --timeout=90
```

### Issue 4: Notification Tidak Dispatch

**Check Code:**
```powershell
# Pastikan Notifiable trait ada di model
php artisan tinker
$booking = App\Models\Booking::first();
method_exists($booking, 'notify');  # Harus return true
```

**Check Email Column:**
```powershell
php artisan tinker
$booking = App\Models\Booking::first();
$booking->email;  # Harus ada value
```

### Issue 5: Scheduler Tidak Jalan

**Testing:**
```powershell
# List scheduled tasks
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Check apakah command terdaftar
php artisan schedule:list | Select-String "bookings:send-reminders"
```

---

## 📊 Monitoring & Logs

### 1. Laravel Logs

**Location:** `storage/logs/laravel.log`

**Key Search Terms:**
- `"BookingConfirmed"` - Konfirmasi booking
- `"BookingCancelled"` - Pembatalan booking
- `"BookingReminder"` - Reminder H-24
- `"Failed to send"` - Error notifikasi
- `"WhatsApp sent successfully"` - WhatsApp terkirim
- `"WhatsApp notification failed"` - WhatsApp gagal

**Live Monitoring:**
```powershell
# Watch logs real-time
php artisan pail

# Atau gunakan tail
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

### 2. Queue Monitoring

```powershell
# Check pending jobs
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Detailed job info
php artisan tinker
DB::table('jobs')->select('*')->get();
DB::table('failed_jobs')->select('*')->get();
```

### 3. Database Verification

```powershell
php artisan tinker

# Check latest bookings dengan email
App\Models\Booking::whereNotNull('email')->latest()->take(5)->get();

# Check bookings yang akan reminder besok
App\Models\Booking::whereDate('tanggal', now()->addDay()->toDateString())
    ->where('status', 'confirmed')
    ->get();
```

---

## ✅ Checklist Testing Final

### Pre-Testing
- [ ] `.env` sudah diupdate dengan SMTP credentials
- [ ] `.env` sudah diupdate dengan Fonnte API key
- [ ] `composer dev` sudah running (atau queue:work manual)
- [ ] Database migration terbaru sudah dijalankan
- [ ] Storage link sudah dibuat

### Testing Scenarios
- [ ] ✅ Booking baru via frontend → Email + WhatsApp terkirim
- [ ] ✅ Cancel booking via admin → Email + WhatsApp pembatalan terkirim
- [ ] ✅ Manual run `bookings:send-reminders` → Reminder terkirim
- [ ] ✅ Check logs tidak ada error
- [ ] ✅ Check queue:work terminal tidak ada failed jobs

### Edge Cases
- [ ] Booking tanpa email (opsional field) → Tetap tersimpan, notifikasi ke WhatsApp saja
- [ ] Nomor HP format 08xxx → Auto convert ke 62xxx
- [ ] Booking untuk hari ini (bukan besok) → Tidak dapat reminder
- [ ] Cancel booking yang sudah cancelled → Tombol Batalkan hidden
- [ ] Queue worker mati saat booking → Job tertahan, retry setelah worker up

---

## 🚀 Production Deployment Notes

### 1. Environment Setup
```env
# Production .env
QUEUE_CONNECTION=database  # Atau redis untuk performa lebih baik
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
# ... (sama seperti development)
```

### 2. Cron Job Setup (Linux/Unix)
```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /var/www/sportbooking && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Queue Worker Setup (Supervisor)
```ini
; /etc/supervisor/conf.d/sportbooking-worker.conf
[program:sportbooking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sportbooking/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sportbooking/storage/logs/worker.log
stopwaitsecs=3600
```

### 4. Cache Configuration
```powershell
# Setelah update .env di production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 Support

Jika menemui masalah:
1. Check error di `storage/logs/laravel.log`
2. Check queue worker terminal output
3. Verify environment config: `php artisan config:clear && php artisan config:cache`
4. Test ulang SMTP via tinker (lihat section Testing Konfigurasi)

**Good luck testing! 🎉**
