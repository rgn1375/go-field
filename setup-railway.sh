#!/bin/bash

echo "================================================"
echo "🚀 GoField - Railway Deployment Setup"
echo "================================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if git is initialized
if [ ! -d .git ]; then
    echo -e "${RED}❌ Git repository not initialized${NC}"
    echo "Run: git init && git add . && git commit -m 'Initial commit'"
    exit 1
fi

echo -e "${GREEN}✅ Git repository detected${NC}"

# Check if all required files exist
FILES=("Procfile" "nixpacks.toml" "railway.json" ".env.production")
MISSING=0

echo ""
echo "Checking deployment files..."
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ $file${NC}"
    else
        echo -e "${RED}❌ $file missing${NC}"
        MISSING=1
    fi
done

if [ $MISSING -eq 1 ]; then
    echo -e "${RED}Some required files are missing!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ All deployment files present${NC}"

# Check if Railway CLI is installed
echo ""
echo "Checking Railway CLI..."
if ! command -v railway &> /dev/null; then
    echo -e "${YELLOW}⚠️  Railway CLI not installed${NC}"
    echo "Installing Railway CLI..."
    npm install -g @railway/cli
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Railway CLI installed${NC}"
    else
        echo -e "${RED}❌ Failed to install Railway CLI${NC}"
        echo "Install manually: npm install -g @railway/cli"
        exit 1
    fi
else
    echo -e "${GREEN}✅ Railway CLI installed${NC}"
fi

# Generate APP_KEY
echo ""
echo "Generating APP_KEY..."
APP_KEY=$(php artisan key:generate --show)
echo -e "${GREEN}✅ APP_KEY generated:${NC}"
echo "$APP_KEY"
echo ""
echo -e "${YELLOW}⚠️  Copy this key to Railway environment variables!${NC}"

echo ""
echo "================================================"
echo "📋 Next Steps:"
echo "================================================"
echo ""
echo "1. Push code to GitHub:"
echo "   git add ."
echo "   git commit -m 'Prepare for Railway deployment'"
echo "   git push origin main"
echo ""
echo "2. Create Railway project:"
echo "   Visit: https://railway.app/new"
echo "   Select: Deploy from GitHub → BookingLapang"
echo ""
echo "3. Add PostgreSQL database:"
echo "   Click: + New → Database → PostgreSQL"
echo ""
echo "4. Set environment variables:"
echo "   Copy from .env.production"
echo "   Set APP_KEY to: $APP_KEY"
echo ""
echo "5. Login to Railway CLI:"
echo "   railway login"
echo "   railway link"
echo ""
echo "6. Run migrations:"
echo "   railway run php artisan migrate --force"
echo "   railway run php artisan db:seed --force"
echo ""
echo "================================================"
echo -e "${GREEN}✅ Setup complete! Follow the steps above.${NC}"
echo "================================================"
echo ""
echo "Full guide: RAILWAY_QUICKSTART.md"
echo ""
