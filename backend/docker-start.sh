#!/bin/bash
set -e

# Copy .env.example to .env if .env doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Inject environment variables into .env
printenv | grep -E "^(APP_|DB_|CACHE_|SESSION_|QUEUE_|MAIL_|FRONTEND_|FILESYSTEM_)" | while IFS='=' read -r key value; do
    sed -i "/^${key}=/d" /var/www/html/.env
    echo "${key}=${value}" >> /var/www/html/.env
done

# Generate app key if not set
grep -q "^APP_KEY=base64:" /var/www/html/.env || php artisan key:generate --force

# Clear cache
php artisan config:clear
php artisan config:cache
php artisan route:cache

# Run migrations
php artisan migrate --force

# Seed only if users table is empty
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 | tr -d '[:space:]')
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded ($USER_COUNT users found), skipping."
fi

# Storage link
php artisan storage:link || true

# Start Apache
exec apache2-foreground
