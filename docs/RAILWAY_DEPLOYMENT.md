# 🚀 Railway Deployment Guide - GoField

## Prerequisites
- GitHub account
- Railway account (https://railway.app)
- Fonnte API key (for WhatsApp notifications)
- SMTP credentials (Mailtrap, SendGrid, or Gmail)

---

## 📋 Deployment Steps

### 1. Prepare Repository

Pastikan semua file konfigurasi sudah di-commit:
```bash
git add Procfile nixpacks.toml railway.json .env.production
git commit -m "Add Railway deployment configuration"
git push origin main
```

### 2. Create Railway Project

1. Login ke https://railway.app
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Authorize Railway & pilih repository `BookingLapang`
5. Railway akan otomatis detect Laravel dan mulai build

### 3. Add PostgreSQL Database

1. Di Railway dashboard, click **"New"** → **"Database"** → **"PostgreSQL"**
2. Railway akan auto-generate credentials
3. Database akan otomatis ter-link ke aplikasi (via `DATABASE_URL`)

### 4. Configure Environment Variables

Di Railway dashboard, buka **Variables** tab dan tambahkan:

#### **Essential Variables:**
```bash
APP_NAME=GoField
APP_ENV=production
APP_DEBUG=false
APP_KEY=                    # Generate dengan: php artisan key:generate --show
APP_URL=https://your-app.up.railway.app

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_DRIVER=database
SESSION_DRIVER=database

# Mail (Contoh: Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@gofield.com
MAIL_FROM_NAME=GoField

# WhatsApp (Fonnte)
FONNTE_API_KEY=your_fonnte_api_key
FONNTE_URL=https://api.fonnte.com/send

# File Storage (Cloudinary - recommended)
FILESYSTEM_DISK=cloudinary
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

**Note:** Railway auto-inject `DATABASE_URL` jadi tidak perlu set `DB_*` variables manual.

### 5. Generate APP_KEY

Di terminal local:
```bash
php artisan key:generate --show
```
Copy outputnya (contoh: `base64:abc123...`) ke Railway variable `APP_KEY`

### 6. Run Database Migration

Di Railway dashboard:
1. Buka tab **"Deployments"**
2. Setelah deployment success, buka **"View Logs"**
3. Atau deploy manual via terminal:

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link project
railway link

# Run migration
railway run php artisan migrate --force

# Seed data (optional - untuk test accounts)
railway run php artisan db:seed --force
```

### 7. Setup Worker Service (Queue)

Railway bisa run multiple services dalam 1 project:

1. Di Railway dashboard, click **"New Service"**
2. Pilih **"Empty Service"**
3. Link ke repository yang sama
4. Di **Settings** → **Start Command**, set:
   ```bash
   php artisan queue:listen database --tries=3 --timeout=90
   ```
5. Di **Variables**, copy semua variables dari web service
6. Deploy service

### 8. Setup Scheduler Service (Optional)

Untuk booking reminders & status updates:

1. Create another service seperti step 7
2. Set **Start Command**:
   ```bash
   while true; do php artisan schedule:run; sleep 60; done
   ```
3. Copy environment variables
4. Deploy

---

## 🔧 Post-Deployment Tasks

### 1. Create Admin Account

```bash
railway run php artisan tinker
```

Lalu jalankan:
```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@gofield.com',
    'password' => bcrypt('your-secure-password'),
    'is_admin' => true,
    'email_verified_at' => now(),
]);
exit
```

### 2. Test Notifications

```bash
# Test email
railway run php artisan tinker
>>> \App\Models\User::first()->notify(new \App\Notifications\BookingConfirmed(\App\Models\Booking::first()));

# Check queue jobs
railway run php artisan queue:work --once
```

### 3. Setup Custom Domain (Optional)

1. Di Railway dashboard → **Settings** → **Domains**
2. Click **"Add Domain"**
3. Ikuti instruksi untuk setup DNS

---

## 📊 Monitoring & Logs

### View Real-time Logs
```bash
railway logs --follow
```

### Check Queue Status
```bash
railway run php artisan queue:monitor database
```

### Database Console
```bash
railway run php artisan tinker
```

---

## 🔐 Security Checklist

- [x] `APP_DEBUG=false` di production
- [x] `APP_ENV=production`
- [x] APP_KEY generated dan unique
- [x] Database credentials secure (auto-handled Railway)
- [x] SMTP credentials di environment variables
- [x] Fonnte API key di environment variables
- [ ] Enable Railway's **"Sleep on Idle"** OFF (agar worker tetap jalan)
- [ ] Setup **Sentry** atau logging service untuk error tracking

---

## 💰 Cost Estimation

### Free Tier (Railway)
- **$5 credit/month** gratis
- Cukup untuk testing/development
- Auto-sleep setelah 1 jam idle

### Paid (Production-Ready)
- **Web Service**: ~$5-10/month
- **Worker Service**: ~$3-5/month
- **PostgreSQL**: ~$5/month
- **Total**: ~$13-20/month

**Tips**: Disable "Sleep on Idle" untuk worker service agar notifikasi tetap jalan.

---

## 🔄 Continuous Deployment

Railway otomatis deploy setiap kali push ke GitHub:

```bash
git add .
git commit -m "Update booking logic"
git push origin main
# Railway auto-deploy dalam 2-3 menit
```

### Rollback
Di Railway dashboard → **Deployments** → Click deployment lama → **"Rollback"**

---

## 🐛 Troubleshooting

### Issue: Migration Failed
```bash
# Force run migration
railway run php artisan migrate:fresh --force --seed
```

### Issue: Queue Not Processing
Check worker service logs:
```bash
railway logs --service worker
```

Pastikan `QUEUE_CONNECTION=database` di environment variables.

### Issue: Images Not Uploading
Setup Cloudinary:
1. Daftar di https://cloudinary.com (Free 25GB)
2. Copy credentials dari dashboard
3. Add ke Railway variables:
   ```
   CLOUDINARY_CLOUD_NAME=xxx
   CLOUDINARY_API_KEY=xxx
   CLOUDINARY_API_SECRET=xxx
   ```
4. Install package:
   ```bash
   composer require cloudinary-labs/cloudinary-laravel
   ```

### Issue: WhatsApp Not Sending
1. Check Fonnte balance: https://fonnte.com
2. Verify `FONNTE_API_KEY` di Railway
3. Check logs:
   ```bash
   railway logs | grep WhatsApp
   ```

---

## 📚 Additional Resources

- Railway Docs: https://docs.railway.app
- Laravel Deployment: https://laravel.com/docs/deployment
- Nixpacks: https://nixpacks.com/docs

---

## 🎉 Success Checklist

Setelah deployment, test:
- [ ] Homepage load tanpa error
- [ ] Admin login di `/admin`
- [ ] Create booking
- [ ] Email notification terkirim
- [ ] WhatsApp notification terkirim (jika ada credit Fonnte)
- [ ] Queue worker processing jobs
- [ ] Scheduled tasks running (check logs pukul 9 pagi untuk reminders)

---

**Deployment Status**: ✅ Production-Ready

**Last Updated**: November 30, 2025

**Railway Project Link**: `railway link` akan generate URL unik Anda
