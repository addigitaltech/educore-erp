#!/bin/bash

# Create .env from environment variables if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key
php artisan key:generate --force

# Cache config and routes
php artisan config:cache
php artisan route:cache

# Run migrations and seed
php artisan migrate --seed --force

# Create storage link
php artisan storage:link

# Start Apache
apache2-foreground
