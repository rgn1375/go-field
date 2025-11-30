# 🚀 Deploy GoField ke Laravel Cloud

Panduan lengkap untuk mendeploy aplikasi GoField Multi-Sport Booking System ke Laravel Cloud.

## 📋 Prasyarat

1. **Akun Laravel Cloud** - Daftar di [cloud.laravel.com](https://cloud.laravel.com)
2. **Repository GitHub** - Project sudah terhubung ke GitHub (✅ sudah ada: `rgn1375/go-field`)
3. **Akun Email SMTP** - Untuk notifikasi email
4. **API Key Fonnte** - Untuk notifikasi WhatsApp

## 🎯 Langkah 1: Persiapan Repository

### 1.1 Commit & Push Kode Terbaru

```bash
# Pastikan semua perubahan sudah di-commit
git add .
git commit -m "Prepare for Laravel Cloud deployment"
git push origin main
```

### 1.2 Verifikasi File Konfigurasi

File `.laravel-cloud.yml` sudah dibuat otomatis. Pastikan file ini ada di root project.

## 🎯 Langkah 2: Setup Laravel Cloud Dashboard

### 2.1 Buat Project Baru

1. Login ke [cloud.laravel.com](https://cloud.laravel.com)
2. Klik **"New Project"**
3. Pilih repository: `rgn1375/go-field`
4. Branch: `main`
5. Project Name: `gofield` atau nama yang Anda inginkan
6. Region: Pilih yang terdekat (Singapore/Asia Pacific recommended)

### 2.2 Konfigurasi Database

Laravel Cloud akan otomatis provision MySQL database. Catat credentials yang diberikan.

## 🎯 Langkah 3: Environment Variables

Set environment variables di Laravel Cloud dashboard (**Settings** → **Environment**):

### Required Variables

```env
# Application
APP_NAME="GoField"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-project.laravel.cloud

# Generate dengan: php artisan key:generate --show (lokal)
APP_KEY=base64:YOUR_32_CHARACTER_KEY_HERE

# Database (Laravel Cloud provides automatically)
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password

# Queue
QUEUE_CONNECTION=database

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=database

# Mail (SMTP Configuration)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@gofield.com"
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp (Fonnte API)
FONNTE_API_KEY=your_fonnte_api_key_here
FONNTE_URL=https://api.fonnte.com/send

# Filesystem
FILESYSTEM_DISK=public

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null
```

### Generate APP_KEY

Di lokal, jalankan:
```bash
php artisan key:generate --show
```
Copy output dan paste ke environment variable `APP_KEY` di Laravel Cloud.

## 🎯 Langkah 4: Deploy

### 4.1 Trigger Deployment

Di Laravel Cloud dashboard:
1. Go to **Deployments** tab
2. Klik **"Deploy Now"**
3. Monitor logs untuk memastikan deployment berhasil

### 4.2 Build Process

Laravel Cloud akan otomatis menjalankan:
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### 4.3 Deployment Process

Setelah build, akan menjalankan:
```bash
php artisan migrate --force
php artisan queue:restart
php artisan cache:clear
```

## 🎯 Langkah 5: Database Seeding (Production)

### Seed Admin & Essential Data

Untuk pertama kali, Anda perlu seed data admin dan lapangan:

1. Di Laravel Cloud dashboard, buka **Terminal** atau **Tinker**
2. Jalankan salah satu:

**Option A: Production Seeder (Recommended - Only Admin + Facilities)**
```bash
php artisan db:seed --class=ProductionSeeder --force
```

**Option B: Full Seeder (Include Test Users + Sample Bookings)**
```bash
php artisan db:seed --force
```

**Note**: Flag `--force` diperlukan untuk bypass production protection.

### Admin Credentials

Setelah seeding, akses admin:
```
URL: https://your-project.laravel.cloud/admin
Email: admin@admin.com
Password: admin123
```

**PENTING**: Segera ubah password admin setelah login!

## 🎯 Langkah 6: Storage Setup

### Public Storage Link

Laravel Cloud otomatis menjalankan `php artisan storage:link` saat deployment.

Pastikan folder `storage/app/public` ada dan writable.

### Upload Test

Test upload gambar lapangan di admin panel untuk memastikan storage berfungsi.

## 🎯 Langkah 7: Queue Workers

Laravel Cloud otomatis menjalankan queue workers sesuai konfigurasi di `.laravel-cloud.yml`:

```yaml
queues:
  - connection: database
    queue: default
    processes: 2
    tries: 3
    timeout: 90
```

### Verify Queue

Test dengan membuat booking dan pastikan notifikasi terkirim (email + WhatsApp).

## 🎯 Langkah 8: Cron Jobs (Scheduler)

Laravel Cloud otomatis setup cron untuk Laravel Scheduler:

```yaml
cron:
  - schedule: "* * * * *"
    command: "php artisan schedule:run"
```

Schedule yang akan berjalan:
- **09:00 daily**: Send booking reminders (H-24)
- **Hourly**: Update booking status to completed

## 🎯 Langkah 9: Custom Domain (Optional)

### Setup Custom Domain

1. Di Laravel Cloud dashboard, go to **Domains**
2. Klik **"Add Domain"**
3. Masukkan domain Anda (e.g., `gofield.com`)
4. Update DNS records di domain provider:
   ```
   CNAME www.gofield.com -> your-project.laravel.cloud
   A     gofield.com      -> [IP dari Laravel Cloud]
   ```
5. Tunggu propagasi DNS (5-30 menit)
6. Laravel Cloud otomatis provision SSL certificate

### Update Environment

Jangan lupa update `APP_URL`:
```env
APP_URL=https://gofield.com
```

## 🎯 Langkah 10: Monitoring & Maintenance

### Health Check

Laravel Cloud otomatis monitor aplikasi:
```yaml
health:
  path: /
  timeout: 10
```

### View Logs

Di dashboard:
1. Go to **Logs** tab
2. Filter by: Application / Queue / Scheduler
3. Monitor errors dan performance

### Performance Monitoring

Laravel Cloud menyediakan:
- CPU & Memory usage
- Request rate
- Response time
- Error rate
- Queue throughput

## 📊 Post-Deployment Checklist

- [ ] ✅ Aplikasi accessible via URL
- [ ] ✅ Admin login berfungsi (`/admin`)
- [ ] ✅ Database migrations berhasil
- [ ] ✅ Storage upload berfungsi
- [ ] ✅ Queue workers running
- [ ] ✅ Cron jobs aktif
- [ ] ✅ Email notifications terkirim
- [ ] ✅ WhatsApp notifications terkirim
- [ ] ✅ Booking flow end-to-end works
- [ ] ✅ Point system berfungsi
- [ ] ✅ Payment upload works
- [ ] ✅ Cancellation & refund works
- [ ] ✅ SSL certificate aktif (HTTPS)
- [ ] ✅ Custom domain (jika ada)
- [ ] ✅ Performance acceptable

## 🧪 Testing Production

### Test Scenarios

1. **User Registration**
   ```
   https://your-project.laravel.cloud/register
   ```

2. **Browse Lapangan**
   ```
   https://your-project.laravel.cloud/
   ```

3. **Make Booking**
   - Pilih lapangan
   - Pilih tanggal & waktu
   - Isi form booking
   - Submit

4. **Check Notifications**
   - Email confirmation diterima
   - WhatsApp message diterima

5. **Admin Panel**
   ```
   https://your-project.laravel.cloud/admin
   ```
   - Login
   - Approve payment
   - Manage bookings

6. **API Test**
   ```bash
   curl https://your-project.laravel.cloud/api/v1/lapangan
   ```

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error

**Solution**:
1. Check logs di Laravel Cloud dashboard
2. Pastikan `APP_KEY` sudah di-set
3. Verify database credentials
4. Check `.env` variables

### Issue: Queue Jobs Not Processing

**Solution**:
1. Verify `QUEUE_CONNECTION=database`
2. Check queue workers di dashboard
3. Restart queue: Deploy ulang atau via terminal:
   ```bash
   php artisan queue:restart
   ```

### Issue: Notifications Not Sending

**Solution**:
1. Check SMTP credentials
2. Verify Fonnte API key
3. Check queue logs
4. Test email manually:
   ```bash
   php artisan tinker
   Mail::raw('Test', function($m) { 
       $m->to('test@example.com')->subject('Test'); 
   });
   ```

### Issue: Storage/Images Not Working

**Solution**:
1. Check if `storage:link` ran successfully
2. Verify disk permissions
3. Check `FILESYSTEM_DISK=public`
4. Re-run: `php artisan storage:link`

### Issue: Migrations Failed

**Solution**:
1. Check database credentials
2. Verify database exists
3. Manual migrate via terminal:
   ```bash
   php artisan migrate --force
   ```

### Issue: Filament Admin Not Accessible

**Solution**:
1. Clear cache: `php artisan cache:clear`
2. Re-optimize: `php artisan filament:optimize`
3. Check user seeding: `php artisan db:seed --class=DatabaseSeeder`

## 📈 Performance Optimization

### Laravel Cloud Auto-Optimizations

Laravel Cloud otomatis mengaktifkan:
- OpCache untuk PHP
- JIT compilation (PHP 8.3+)
- Redis cache (jika enabled)
- CDN untuk static assets
- HTTP/2 & Brotli compression

### Manual Optimizations

Sudah dijalankan saat deployment:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### Database Optimization

Indexes sudah ada (lihat migration: `2025_11_06_155736_add_performance_indexes.php`):
- `bookings(lapangan_id, tanggal, status)`
- `bookings(jam_mulai, jam_selesai)`

## 💰 Pricing & Scaling

### Laravel Cloud Pricing

Check pricing di [cloud.laravel.com/pricing](https://cloud.laravel.com/pricing)

Typical configuration:
- **Database**: Managed MySQL (auto-scaling)
- **Compute**: Auto-scaling based on traffic
- **Storage**: SSD storage (grows as needed)
- **Queue Workers**: 2 workers (configurable)

### Scaling

Laravel Cloud auto-scales berdasarkan:
- Traffic volume
- CPU usage
- Memory usage
- Queue throughput

## 🔒 Security

### Production Security Checklist

- [x] `APP_DEBUG=false`
- [x] `APP_ENV=production`
- [x] SSL/TLS enabled (auto by Laravel Cloud)
- [x] Database credentials secured
- [x] API keys in environment (not in code)
- [x] CSRF protection enabled
- [x] XSS protection enabled
- [x] SQL injection prevention (Eloquent ORM)
- [ ] Change default admin password
- [ ] Setup 2FA for admin (recommended)
- [ ] Regular backups enabled

### Backup Strategy

Laravel Cloud menyediakan automatic backups:
1. Go to **Backups** tab
2. Enable automatic daily backups
3. Set retention period

Manual backup via terminal:
```bash
php artisan backup:run
```

## 📞 Support

### Laravel Cloud Support

- Documentation: [cloud.laravel.com/docs](https://cloud.laravel.com/docs)
- Support: Via dashboard ticket system
- Community: Laravel Discord

### Application Support

- GitHub Issues: [github.com/rgn1375/go-field/issues](https://github.com/rgn1375/go-field/issues)
- Documentation: See `docs/` folder

## 🎉 Success!

Aplikasi GoField Anda sekarang live di Laravel Cloud! 🚀

**Next Steps**:
1. Share URL dengan team
2. Test semua fitur di production
3. Monitor performance & logs
4. Setup custom domain (optional)
5. Enable backups
6. Update admin password

---

**Deployment Date**: [Your Date]  
**Laravel Cloud Project**: [Your Project Name]  
**Production URL**: https://your-project.laravel.cloud

**Happy Booking! 🏟️⚽🏀🏐🎾**
