#!/usr/bin/env bash
set -e

# Deployment Script for Corevisys License System (https://license.corevisys.com)
echo "=== Starting deployment for license.corevisys.com ==="

# 1. Put application into maintenance mode if already configured
if [ -f "artisan" ]; then
    php artisan down --render="errors::503" --retry=60 || true
fi

# 2. Pull the latest commits from origin/main
echo "--> Pulling latest source code..."
git fetch origin main
git reset --hard origin/main

# 3. Install/Update PHP composer dependencies (production flags)
echo "--> Installing Composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Install Node dependencies & Build production frontend assets
echo "--> Building frontend assets (Vite)..."
if command -v npm &> /dev/null; then
    npm ci --prefer-offline --no-audit || npm install
    npm run build
else
    echo "WARNING: npm is not installed on this server. Ensure public/build assets are pre-built and uploaded."
fi

# 5. Run database migrations
echo "--> Running database migrations..."
php artisan migrate --force

# 6. Ensure storage symlink exists
echo "--> Linking storage..."
php artisan storage:link || true

# 7. Cache configuration, routes, views, and events for maximum performance
echo "--> Caching Laravel config, routes, views, and events..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Restart background workers (supervisor / queue)
echo "--> Restarting queue workers..."
php artisan queue:restart || true

if command -v supervisorctl &> /dev/null; then
    supervisorctl restart corevisys-queue:* || true
fi

# 9. Bring application back up
php artisan up

echo "=== Deployment completed successfully for https://license.corevisys.com! ==="
