#!/bin/bash

# Generate app key if not set
php artisan key:generate --force

# Cache config
php artisan config:cache
php artisan route:cache

# Run migrations and seed
php artisan migrate --seed --force

# Create storage link
php artisan storage:link

# Start Apache
apache2-foreground
