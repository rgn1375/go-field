# 🚨 Railway Setup - CRITICAL FIX

## Problem: Services Crash karena Database Connection Error

Error: `SQLSTATE[HY000] [2002] No such file or directory`

**Root Cause:** Railway belum punya PostgreSQL & Redis services + environment variables belum di-set.

---

## ✅ SOLUSI LENGKAP - Ikuti Step by Step

### STEP 1: Add Database Services di Railway Dashboard

#### 1a. Add PostgreSQL
1. Buka Railway project Anda
2. Click **"+ New"** (kanan atas)
3. Pilih **"Database"** → **"Add PostgreSQL"**
4. Tunggu hingga status **"Active"** (hijau)
5. ✅ Railway akan auto-generate environment variables

#### 1b. Add Redis
1. Click **"+ New"** lagi
2. Pilih **"Database"** → **"Add Redis"**
3. Tunggu hingga status **"Active"** (hijau)
4. ✅ Railway akan auto-generate environment variables

---

### STEP 2: Configure Environment Variables (CRITICAL!)

#### Go to: Your Web Service → Variables Tab

**Hapus atau set ulang variable yang salah:**
- Jika ada `DB_CONNECTION=mysql` → **DELETE** atau change ke `pgsql`
- Jika ada `DB_HOST=127.0.0.1` → **DELETE**

**Add SEMUA variables ini:**

```bash
# App Config
APP_NAME=GoField
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

# Database - CRITICAL: Gunakan Railway References
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Redis - CRITICAL: Gunakan Railway References
REDIS_HOST=${{Redis.REDIS_HOST}}
REDIS_PASSWORD=${{Redis.REDIS_PASSWORD}}
REDIS_PORT=${{Redis.REDIS_PORT}}

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Storage (TEMPORARY - bisa skip dulu untuk testing)
FILESYSTEM_DISK=public

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=9inehhhhh@gmail.com
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@gofield.com
MAIL_FROM_NAME=GoField

# WhatsApp
FONNTE_API_KEY=...
FONNTE_URL=https://api.fonnte.com/send

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

**IMPORTANT:** Pastikan format `${{Postgres.VARIABLENAME}}` sama persis!

---

### STEP 3: Redeploy

#### Option A: Via Dashboard (Recommended)
1. Go to **Deployments** tab
2. Click **"Redeploy"** pada deployment terakhir

#### Option B: Via Git Push
```bash
git add .
git commit -m "Fix Railway database configuration"
git push origin main
```

---

### STEP 4: Verify Deployment

#### Check Logs:
1. Deployments tab → Latest deployment → **View Logs**
2. Tunggu hingga muncul: `Server running on [http://0.0.0.0:XXXX]`
3. ✅ Jika tidak ada error database, SUCCESS!

#### Test Health Check:
```bash
curl https://your-app.up.railway.app/api/health
```

Expected response:
```json
{
  "status": "healthy",
  "services": {
    "database": "ok",
    "redis": "ok"
  }
}
```

---

### STEP 5: Run Migrations (Setelah Deploy Sukses)

#### Via Railway Dashboard:
1. Go to your **web service**
2. **Settings** tab → scroll ke **"Run a Command"**
3. Enter:
   ```bash
   php artisan migrate:fresh --seed --force
   ```
4. Click **"Run"**
5. Wait for success message

---

## 🔄 Setup Worker & Scheduler Services

### Worker Service (untuk Queue/Notifications):

1. Click **"+ New"** → **"Empty Service"**
2. Name: `gofield-worker`
3. **Source**: Connect same GitHub repo
4. **Settings** → **Start Command**:
   ```bash
   php artisan queue:work redis --tries=3 --timeout=90
   ```
5. **Variables** tab → **"Add All Variables"** → Copy dari web service
6. Deploy!

### Scheduler Service (untuk Booking Reminders):

1. Click **"+ New"** → **"Empty Service"**
2. Name: `gofield-scheduler`
3. **Source**: Connect same GitHub repo
4. **Settings** → **Start Command**:
   ```bash
   php artisan schedule:work
   ```
5. **Variables** tab → Copy dari web service
6. Deploy!

---

## 🚨 Common Issues & Solutions

### Issue 1: "Could not find driver"
**Solution:** Railway's nixpacks auto-installs PHP extensions. Jika tetap error, add to `nixpacks.toml`:
```toml
[phases.setup]
aptPkgs = ["postgresql-client"]
```

### Issue 2: Variables tidak ter-apply
**Solution:**
1. Go to Variables tab
2. Click **"Raw Editor"** (kanan atas)
3. Paste all variables sekaligus
4. Save → Redeploy

### Issue 3: Migration timeout
**Solution:**
```bash
# Run migrations in smaller batches
php artisan migrate --step --force
```

---

## 📊 Verification Checklist

- [ ] PostgreSQL service status = **Active** (hijau)
- [ ] Redis service status = **Active** (hijau)
- [ ] Web service logs: **no database errors**
- [ ] Health check returns `200 OK`
- [ ] Migrations completed successfully
- [ ] Worker service running (check logs for "Processing")
- [ ] Scheduler service running

---

## 🎯 Next: Commit & Push

```bash
# Commit files yang baru dibuat
git add .
git commit -m "Fix Railway database config + health check"
git push origin main

# Railway akan auto-redeploy dengan config baru
```

---

**Files yang sudah dibuat:**
1. ✅ `.env.production` - Template environment variables
2. ✅ `routes/health.php` - Health check endpoint
3. ✅ `railway.toml` - Updated dengan healthcheck
4. ✅ `RAILWAY_QUICK_FIX.md` - Dokumentasi ini

**IMPORTANT:** Jangan commit file `.env` ke Git! Sudah ada di `.gitignore`.
