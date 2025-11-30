#!/bin/bash
# Railway Post-Deploy Hook
# This runs after successful deployment

echo "🚀 Running post-deployment tasks..."

# Run database migrations
php artisan migrate --force --no-interaction
echo "✅ Database migrations completed"

# Clear and cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Configs cached"

# Link storage (if not already linked)
php artisan storage:link --quiet || true
echo "✅ Storage linked"

# Optimize application
php artisan optimize
echo "✅ Application optimized"

# Clear expired sessions and cache
php artisan cache:prune-stale-tags
echo "✅ Cache pruned"

echo "🎉 Deployment completed successfully!"
