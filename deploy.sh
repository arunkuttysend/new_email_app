#!/bin/bash

# Production Deployment Script
# Usage: ./deploy.sh

echo "🚀 Starting Production Deployment..."

# 1. Stop existing containers
echo "🛑 Stopping containers..."
docker compose -f docker-compose.prod.yml down

# 2. Build images (no cache to ensure fresh code)
echo "🏗️  Building images..."
docker compose -f docker-compose.prod.yml build --no-cache

# 3. Start services
echo "✅ Starting services..."
docker compose -f docker-compose.prod.yml up -d

# 4. Wait for database
echo "⏳ Waiting for database to be ready..."
sleep 10

# 5. Run migrations & optimizations
echo "🔄 Running migrations and optimizations..."
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link

# 6. Set permissions
echo "🔒 Setting permissions..."
docker compose -f docker-compose.prod.yml exec -T app chown -R appuser:appuser storage bootstrap/cache

echo "🎉 Deployment Complete!"
echo "🌍 App is running on port 8000"
