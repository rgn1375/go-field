# Laravel Cloud Pre-Deployment Script (PowerShell)
# Run this locally before pushing to trigger deployment

Write-Host "🚀 Preparing GoField for Laravel Cloud Deployment..." -ForegroundColor Green

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: Not in Laravel project root directory" -ForegroundColor Red
    exit 1
}

# Check if .env.example exists
if (-not (Test-Path ".env.example")) {
    Write-Host "❌ Error: .env.example not found" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Laravel project detected" -ForegroundColor Green

# Run tests
Write-Host ""
Write-Host "🧪 Running tests..." -ForegroundColor Yellow
php artisan config:clear
php artisan test

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Tests failed! Fix errors before deploying." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Tests passed" -ForegroundColor Green

# Check for uncommitted changes
Write-Host ""
Write-Host "📝 Checking for uncommitted changes..." -ForegroundColor Yellow
$status = git status -s
if ($status) {
    Write-Host "⚠️  You have uncommitted changes:" -ForegroundColor Yellow
    git status -s
    $response = Read-Host "Do you want to commit them now? (y/n)"
    if ($response -eq 'y' -or $response -eq 'Y') {
        git add .
        $commitMsg = Read-Host "Enter commit message"
        git commit -m $commitMsg
    } else {
        Write-Host "⚠️  Continuing with uncommitted changes..." -ForegroundColor Yellow
    }
}

# Push to GitHub
Write-Host ""
Write-Host "📤 Pushing to GitHub..." -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Git push failed!" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Code pushed to GitHub" -ForegroundColor Green

# Deployment checklist
Write-Host ""
Write-Host "📋 Pre-Deployment Checklist:" -ForegroundColor Cyan
Write-Host "   1. ✅ Tests passed" -ForegroundColor Green
Write-Host "   2. ✅ Code pushed to GitHub" -ForegroundColor Green
Write-Host ""
Write-Host "   Laravel Cloud will now:" -ForegroundColor White
Write-Host "   - Install dependencies (composer & npm)"
Write-Host "   - Build assets (Vite)"
Write-Host "   - Run migrations"
Write-Host "   - Cache config/routes/views"
Write-Host "   - Start queue workers"
Write-Host "   - Setup cron jobs"
Write-Host ""
Write-Host "🎯 Next Steps:" -ForegroundColor Cyan
Write-Host "   1. Go to cloud.laravel.com"
Write-Host "   2. Your deployment should start automatically"
Write-Host "   3. Monitor deployment logs"
Write-Host "   4. Test the application after deployment"
Write-Host ""
Write-Host "📚 Full Guide: See LARAVEL_CLOUD_DEPLOYMENT.md" -ForegroundColor Yellow
Write-Host ""
Write-Host "✨ Deployment initiated! Good luck! 🚀" -ForegroundColor Green
