# 🚀 Laravel Cloud - Quick Reference

Deploy GoField ke Laravel Cloud dalam 5 menit!

## ⚡ Quick Deploy (Fastest)

```bash
# Windows
.\deploy-laravel-cloud.ps1

# Linux/Mac
./deploy-laravel-cloud.sh
```

## 📝 Setup Checklist

### 1️⃣ GitHub Repository
- [x] Repository: `rgn1375/go-field`
- [x] Branch: `main`
- [x] Code pushed

### 2️⃣ Laravel Cloud Account
1. Sign up: https://cloud.laravel.com
2. Connect GitHub account
3. Select repository: `rgn1375/go-field`

### 3️⃣ Environment Variables

**Copy these to Laravel Cloud Dashboard** → Settings → Environment:

```env
APP_NAME=GoField
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_THIS_LOCALLY
APP_URL=https://your-project.laravel.cloud

# Database (provided by Laravel Cloud)
DB_CONNECTION=mysql
DB_HOST=<provided>
DB_PORT=3306
DB_DATABASE=<provided>
DB_USERNAME=<provided>
DB_PASSWORD=<provided>

# Queue
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_DRIVER=database

# Mail (configure your SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@gofield.com

# WhatsApp
FONNTE_API_KEY=your_api_key
FONNTE_URL=https://api.fonnte.com/send

# Storage
FILESYSTEM_DISK=public
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 4️⃣ Generate APP_KEY

**Run locally:**
```bash
php artisan key:generate --show
```
Copy output ke environment variable `APP_KEY`

### 5️⃣ Deploy!

1. Click **"Deploy Now"** di Laravel Cloud
2. Wait ~3-5 minutes
3. Application ready! 🎉

## 🎯 Post-Deployment

### Seed Database
```bash
# Via Laravel Cloud Terminal/Tinker

# Option A: Production only (Admin + Facilities)
php artisan db:seed --class=ProductionSeeder --force

# Option B: Full seed (Include test users + bookings)
php artisan db:seed --force
```

**Note**: Flag `--force` required to bypass production protection.

### Test Application
- Public site: `https://your-project.laravel.cloud`
- Admin panel: `https://your-project.laravel.cloud/admin`
  - Email: `admin@admin.com`
  - Password: `admin123` (change immediately!)

### Verify Services
- ✅ Queue workers running
- ✅ Cron jobs active (every minute)
- ✅ Database migrations applied
- ✅ Storage linked

## 📊 Configuration Files

### `.laravel-cloud.yml`
Auto-configures:
- PHP 8.3
- Composer install (production)
- NPM build
- Queue workers (2 processes)
- Cron scheduler
- Health checks

### Build Commands (Automatic)
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### Deploy Commands (Automatic)
```bash
php artisan migrate --force
php artisan queue:restart
php artisan cache:clear
```

## 🔧 Common Issues

### Build Failed
**Check**: PHP/Composer errors in logs
**Fix**: Ensure `composer.json` is valid

### Database Connection Error
**Check**: DB credentials in environment
**Fix**: Copy credentials from Laravel Cloud dashboard

### Queue Not Working
**Check**: Queue logs
**Fix**: Verify `QUEUE_CONNECTION=database`

### Notifications Not Sending
**Check**: SMTP/Fonnte credentials
**Fix**: Test with `php artisan tinker`

## 📚 Full Documentation

- **Complete Guide**: [LARAVEL_CLOUD_DEPLOYMENT.md](LARAVEL_CLOUD_DEPLOYMENT.md)
- **Testing**: [docs/TESTING_GUIDE.md](docs/TESTING_GUIDE.md)
- **API**: [docs/API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)
- **Laravel Cloud Docs**: https://cloud.laravel.com/docs

## 🎉 Success Indicators

- [ ] ✅ Site accessible via URL
- [ ] ✅ Admin login works
- [ ] ✅ Can create booking
- [ ] ✅ Email notification sent
- [ ] ✅ WhatsApp notification sent
- [ ] ✅ Queue processing
- [ ] ✅ HTTPS enabled
- [ ] ✅ No errors in logs

## 💡 Pro Tips

1. **Custom Domain**: Add in Laravel Cloud → Domains
2. **Backups**: Enable automatic daily backups
3. **Monitoring**: Check Laravel Cloud dashboard regularly
4. **Scaling**: Auto-scales based on traffic
5. **Security**: Change admin password after first login!

---

**Need Help?** 
- Laravel Cloud Support: dashboard → Support
- GitHub Issues: https://github.com/rgn1375/go-field/issues
- Full Guide: [LARAVEL_CLOUD_DEPLOYMENT.md](LARAVEL_CLOUD_DEPLOYMENT.md)

**Deployment Time**: ~5 minutes ⚡
**Zero-Config**: Laravel Cloud handles everything! 🚀
