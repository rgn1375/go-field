# 🎯 Railway Deployment Checklist

## Pre-Deployment (Local)

### Code Preparation
- [ ] All code committed to GitHub
- [ ] No `.env` file in repository (use `.env.production` as template)
- [ ] `composer.json` has deploy scripts
- [ ] `Procfile` exists for web/worker/scheduler
- [ ] `nixpacks.toml` configured for PHP 8.2
- [ ] `.railwayignore` created

### Configuration Files
- [ ] `Procfile` - Multi-service setup
- [ ] `nixpacks.toml` - Build configuration
- [ ] `railway.json` - Railway settings
- [ ] `.env.production` - Environment template
- [ ] `railway-deploy.sh` - Post-deploy script

### Dependencies
- [ ] All Composer packages up-to-date
- [ ] All NPM packages up-to-date
- [ ] No dev dependencies in production

## Railway Setup

### Project Creation
- [ ] Railway account created
- [ ] GitHub repository connected
- [ ] Auto-deploy on push enabled

### Database
- [ ] PostgreSQL database added
- [ ] `DATABASE_URL` auto-injected
- [ ] Database persistent storage enabled

### Environment Variables
- [ ] `APP_KEY` generated and set
- [ ] `APP_URL` pointing to Railway domain
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `QUEUE_CONNECTION=database`
- [ ] `MAIL_*` variables configured
- [ ] `FONNTE_API_KEY` set
- [ ] All service configs validated

### Services Setup
- [ ] **Web Service**: Main application running
- [ ] **Worker Service**: Queue processing enabled
- [ ] **Scheduler Service**: Cron jobs running (optional but recommended)

## Post-Deployment

### Database
- [ ] Migrations run successfully
- [ ] Seeders executed (test data)
- [ ] Admin user created

### Testing
- [ ] Homepage loads without errors
- [ ] `/admin` accessible
- [ ] Login works (test user)
- [ ] Create booking works
- [ ] Email notification sent
- [ ] WhatsApp notification sent (if Fonnte has credit)
- [ ] Queue processing works
- [ ] Scheduled tasks running

### Monitoring
- [ ] Logs accessible via Railway dashboard
- [ ] Error tracking setup (Sentry/Bugsnag optional)
- [ ] Database backup configured
- [ ] Uptime monitoring enabled

### Performance
- [ ] Config cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Opcache enabled in production

### Security
- [ ] SSL certificate auto-issued by Railway
- [ ] Environment variables secured
- [ ] Database credentials not hardcoded
- [ ] Admin password changed from default
- [ ] Rate limiting configured

## Maintenance

### Regular Tasks
- [ ] Check logs weekly
- [ ] Monitor disk usage
- [ ] Review queue job failures
- [ ] Database backup verification
- [ ] Update dependencies monthly

### Emergency
- [ ] Rollback procedure documented
- [ ] Database restore tested
- [ ] Support contact info ready
- [ ] Incident response plan

## Cost Optimization

- [ ] Free tier limitations understood
- [ ] Sleep on idle disabled for worker
- [ ] Resource usage monitored
- [ ] Scaling strategy planned

---

## Quick Commands Reference

```bash
# Deploy
git push origin main

# View logs
railway logs --follow

# Run migration
railway run php artisan migrate --force

# Access database
railway run php artisan tinker

# Check queue
railway run php artisan queue:monitor database

# Clear cache
railway run php artisan cache:clear

# Restart service
railway restart --service web
```

---

**Last Updated**: November 30, 2025
**Status**: ✅ Ready for Production
