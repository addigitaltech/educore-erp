#!/bin/bash

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

printenv | grep -E "^(APP_|DB_|CACHE_|SESSION_|QUEUE_|MAIL_|FRONTEND_|FILESYSTEM_)" | while IFS='=' read -r key value; do
    sed -i "/^${key}=/d" /var/www/html/.env
    echo "${key}=${value}" >> /var/www/html/.env
done

grep -q "^APP_KEY=base64:" /var/www/html/.env || php artisan key:generate --force

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan migrate --force
php artisan db:seed --force 2>&1 | grep -v "already exists" || true
php artisan storage:link 2>/dev/null || true

exec apache2-foreground
# Start Apache
exec apache2-foreground
