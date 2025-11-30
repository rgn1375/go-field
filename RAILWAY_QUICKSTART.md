# ============================================
# Railway Production Deployment - Quick Start
# ============================================

## ✅ Pre-Deployment Checklist

Sebelum deploy, pastikan:
- [x] All code di-commit ke GitHub
- [x] `.env.production` sudah dibuat
- [x] Procfile sudah ada
- [x] nixpacks.toml configured
- [x] SMTP credentials ready
- [x] Fonnte API key ready

## 🚀 Deploy dalam 5 Langkah

### 1️⃣ Push ke GitHub
```bash
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

### 2️⃣ Create Railway Project
1. Go to: https://railway.app/new
2. Click: **"Deploy from GitHub"**
3. Select: `BookingLapang` repository
4. Wait ~3-5 minutes for initial build

### 3️⃣ Add PostgreSQL Database
1. Click: **"+ New"** → **"Database"** → **"PostgreSQL"**
2. Database auto-connects via `DATABASE_URL`
3. No manual configuration needed!

### 4️⃣ Set Environment Variables

Click **"Variables"** tab, paste ini:

```env
APP_NAME=GoField
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

QUEUE_CONNECTION=database
CACHE_DRIVER=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@gofield.com

FONNTE_API_KEY=your_fonnte_key
FONNTE_URL=https://api.fonnte.com/send

LOG_LEVEL=error
```

**Generate APP_KEY:**
```bash
php artisan key:generate --show
```

### 5️⃣ Run Migration

Install Railway CLI:
```bash
npm i -g @railway/cli
railway login
railway link
```

Run migration:
```bash
railway run php artisan migrate --force
railway run php artisan db:seed --force
```

## ✅ Verify Deployment

1. **Test URL**: Buka `https://your-app.up.railway.app`
2. **Admin Login**: `https://your-app.up.railway.app/admin`
   - Email: `admin@admin.com`
   - Password: `admin123`
3. **Create Booking**: Test booking flow
4. **Check Logs**: `railway logs --follow`

## 🔧 Setup Worker (Queue) - CRITICAL!

Notifications butuh queue worker:

1. Click **"+ New"** → **"Empty Service"**
2. Link ke repository yang sama
3. Di **Settings** → **Start Command**:
   ```bash
   php artisan queue:listen database --tries=3 --timeout=90
   ```
4. Copy **ALL** environment variables dari web service
5. Click **"Deploy"**

## 📊 Monitor

```bash
# Real-time logs
railway logs --follow

# Specific service
railway logs --service worker

# Database console
railway run php artisan tinker
```

## 🐛 Common Issues

**Migration Error?**
```bash
railway run php artisan migrate:fresh --force --seed
```

**Queue Not Working?**
- Check worker service running
- Verify `QUEUE_CONNECTION=database`
- Check logs: `railway logs --service worker`

**Images Not Uploading?**
Setup Cloudinary (opsional, untuk production):
```bash
composer require cloudinary-labs/cloudinary-laravel
```

Add to Railway variables:
```env
FILESYSTEM_DISK=cloudinary
CLOUDINARY_CLOUD_NAME=xxx
CLOUDINARY_API_KEY=xxx
CLOUDINARY_API_SECRET=xxx
```

## 💰 Cost Estimate

- **Free tier**: $5 credit (cukup untuk testing 2-3 hari)
- **Production**: ~$13-20/month (web + worker + database)

## 📚 Full Documentation

See: `docs/RAILWAY_DEPLOYMENT.md` untuk guide lengkap.

---

**Status**: ✅ Ready to Deploy

**Estimated Time**: 10-15 minutes

**Support**: Railway Discord atau docs.railway.app
