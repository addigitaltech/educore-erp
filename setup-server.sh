#!/bin/bash
# =============================================================
# EduCore ERP — Automated VPS Server Setup Script
# Run as root on Ubuntu 22.04 / 24.04
# Usage: bash setup-server.sh
# =============================================================

set -e  # Exit on any error

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
info() { echo -e "${BLUE}[→]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║   EduCore ERP — Server Setup Script      ║"
echo "║   Ubuntu 22.04 / 24.04                   ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# ── Collect config ──────────────────────────────
read -p "Enter your domain (e.g. yourdomain.com): " DOMAIN
read -p "Enter your API subdomain (e.g. api.yourdomain.com): " API_DOMAIN
read -p "Enter GitHub repo URL (e.g. https://github.com/user/educore-erp.git): " REPO_URL
read -p "Enter DB password for 'educore' user: " -s DB_PASSWORD
echo ""
read -p "Enter your email (for SSL certificate): " SSL_EMAIL
echo ""

# ── Update system ───────────────────────────────
info "Updating system packages..."
apt-get update -qq && apt-get upgrade -y -qq
log "System updated"

# ── Install core packages ────────────────────────
info "Installing core dependencies..."
apt-get install -y -qq \
  curl git unzip zip wget gnupg2 ca-certificates \
  software-properties-common apt-transport-https lsb-release \
  ufw fail2ban
log "Core packages installed"

# ── Install PHP 8.2 ──────────────────────────────
info "Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y -q
apt-get update -qq
apt-get install -y -qq \
  php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-gd php8.2-zip php8.2-curl \
  php8.2-intl php8.2-tokenizer php8.2-pdo
log "PHP 8.2 installed: $(php -v | head -1)"

# ── Install Composer ─────────────────────────────
info "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --quiet
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
log "Composer installed: $(composer --version)"

# ── Install Node.js 20 ───────────────────────────
info "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - > /dev/null
apt-get install -y -qq nodejs
log "Node.js installed: $(node -v)"

# ── Install Nginx ────────────────────────────────
info "Installing Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx
systemctl start nginx
log "Nginx installed"

# ── Install MySQL ────────────────────────────────
info "Installing MySQL 8.0..."
apt-get install -y -qq mysql-server
systemctl enable mysql
systemctl start mysql
log "MySQL installed"

# ── Configure MySQL ──────────────────────────────
info "Setting up MySQL database..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS educore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'educore'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON educore_db.* TO 'educore'@'localhost';
FLUSH PRIVILEGES;
EOF
log "MySQL database 'educore_db' and user 'educore' created"

# ── Clone repository ─────────────────────────────
info "Cloning repository..."
mkdir -p /var/www/educore
cd /var/www/educore
git clone "$REPO_URL" . 2>/dev/null || git pull origin main
log "Repository cloned"

# ── Setup Backend ────────────────────────────────
info "Setting up Laravel backend..."
cd /var/www/educore/backend

composer install --no-interaction --no-dev --optimize-autoloader -q

cp .env.example .env

APP_KEY=$(php artisan key:generate --show 2>/dev/null || echo "base64:$(openssl rand -base64 32)")

cat > .env << ENVEOF
APP_NAME="EduCore ERP"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${API_DOMAIN}
APP_TIMEZONE=Africa/Lagos

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=educore_db
DB_USERNAME=educore
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
FRONTEND_URL=https://${DOMAIN}

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="EduCore ERP"
ENVEOF

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

chown -R www-data:www-data /var/www/educore/backend
chmod -R 755 /var/www/educore/backend
chmod -R 775 /var/www/educore/backend/storage
chmod -R 775 /var/www/educore/backend/bootstrap/cache

log "Laravel backend configured"

# ── Build Frontend ───────────────────────────────
info "Building Vue frontend..."
cd /var/www/educore/frontend

echo "VITE_API_URL=https://${API_DOMAIN}/api" > .env

npm install --silent
npm run build

chown -R www-data:www-data /var/www/educore/frontend/dist

log "Frontend built"

# ── Configure Nginx ──────────────────────────────
info "Configuring Nginx..."

cat > /etc/nginx/sites-available/educore-api << NGINXEOF
server {
    listen 80;
    server_name ${API_DOMAIN};
    root /var/www/educore/backend/public;
    index index.php;

    charset utf-8;
    client_max_body_size 50M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }

    access_log /var/log/nginx/educore-api.log;
    error_log  /var/log/nginx/educore-api-error.log;
}
NGINXEOF

cat > /etc/nginx/sites-available/educore-frontend << NGINXEOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root /var/www/educore/frontend/dist;
    index index.html;

    location / {
        try_files \$uri \$uri/ /index.html;
    }

    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    access_log /var/log/nginx/educore-frontend.log;
    error_log  /var/log/nginx/educore-frontend-error.log;
}
NGINXEOF

ln -sf /etc/nginx/sites-available/educore-api      /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/educore-frontend /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl reload nginx
log "Nginx configured"

# ── Firewall ─────────────────────────────────────
info "Configuring firewall..."
ufw --force enable
ufw allow OpenSSH
ufw allow 'Nginx Full'
log "Firewall enabled"

# ── SSL Certificates ─────────────────────────────
info "Installing SSL certificates..."
apt-get install -y -qq certbot python3-certbot-nginx
certbot --nginx \
  -d "${DOMAIN}" -d "www.${DOMAIN}" -d "${API_DOMAIN}" \
  --email "${SSL_EMAIL}" \
  --agree-tos --non-interactive --redirect
log "SSL certificates installed"

# ── Deploy Script ────────────────────────────────
cat > /usr/local/bin/educore-deploy << 'DEPLOYEOF'
#!/bin/bash
echo "Deploying EduCore ERP..."
cd /var/www/educore

git pull origin main

# Backend
cd backend
composer install --no-interaction --no-dev --optimize-autoloader -q
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
chown -R www-data:www-data storage bootstrap/cache

# Frontend
cd ../frontend
npm install --silent
npm run build
chown -R www-data:www-data dist

systemctl reload nginx

echo "✅ Deployment complete!"
DEPLOYEOF

chmod +x /usr/local/bin/educore-deploy
log "Deploy script created at /usr/local/bin/educore-deploy"

# ── Done ─────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║         ✅  SETUP COMPLETE!                      ║"
echo "╠══════════════════════════════════════════════════╣"
echo "║  Frontend:  https://${DOMAIN}"
echo "║  API:       https://${API_DOMAIN}/api"
echo "║  Database:  educore_db (user: educore)"
echo "╠══════════════════════════════════════════════════╣"
echo "║  To redeploy:  educore-deploy"
echo "╚══════════════════════════════════════════════════╝"
echo ""
