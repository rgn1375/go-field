#!/bin/bash

# Laravel Cloud Pre-Deployment Script
# Run this locally before pushing to trigger deployment

echo "🚀 Preparing GoField for Laravel Cloud Deployment..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel project root directory"
    exit 1
fi

# Check if .env.example exists
if [ ! -f ".env.example" ]; then
    echo "❌ Error: .env.example not found"
    exit 1
fi

echo "✅ Laravel project detected"

# Run tests
echo ""
echo "🧪 Running tests..."
php artisan config:clear
php artisan test

if [ $? -ne 0 ]; then
    echo "❌ Tests failed! Fix errors before deploying."
    exit 1
fi

echo "✅ Tests passed"

# Check for uncommitted changes
echo ""
echo "📝 Checking for uncommitted changes..."
if [[ -n $(git status -s) ]]; then
    echo "⚠️  You have uncommitted changes:"
    git status -s
    read -p "Do you want to commit them now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git add .
        read -p "Enter commit message: " commit_msg
        git commit -m "$commit_msg"
    else
        echo "⚠️  Continuing with uncommitted changes..."
    fi
fi

# Push to GitHub
echo ""
echo "📤 Pushing to GitHub..."
git push origin main

if [ $? -ne 0 ]; then
    echo "❌ Git push failed!"
    exit 1
fi

echo "✅ Code pushed to GitHub"

# Deployment checklist
echo ""
echo "📋 Pre-Deployment Checklist:"
echo "   1. ✅ Tests passed"
echo "   2. ✅ Code pushed to GitHub"
echo ""
echo "   Laravel Cloud will now:"
echo "   - Install dependencies (composer & npm)"
echo "   - Build assets (Vite)"
echo "   - Run migrations"
echo "   - Cache config/routes/views"
echo "   - Start queue workers"
echo "   - Setup cron jobs"
echo ""
echo "🎯 Next Steps:"
echo "   1. Go to cloud.laravel.com"
echo "   2. Your deployment should start automatically"
echo "   3. Monitor deployment logs"
echo "   4. Test the application after deployment"
echo ""
echo "📚 Full Guide: See LARAVEL_CLOUD_DEPLOYMENT.md"
echo ""
echo "✨ Deployment initiated! Good luck! 🚀"
