#!/bin/bash

# Exit on fail
set -e

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear caches
echo "Clearing caches..."
php artisan optimize:clear

# Create storage link if not exists
if [ ! -L public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
