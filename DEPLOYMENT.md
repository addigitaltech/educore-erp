# EduCore ERP — Complete Deployment Guide

This guide covers every deployment option: **GitHub + Vercel/Netlify** (recommended for beginners),
**VPS with Nginx**, and **Docker**. Choose whichever fits your setup.

---

## Table of Contents

1. [Local Development Setup](#1-local-development-setup)
2. [Push to GitHub](#2-push-to-github)
3. [Deploy Frontend to Vercel (Free)](#3-deploy-frontend-to-vercel-free)
4. [Deploy Frontend to Netlify (Free)](#4-deploy-frontend-to-netlify-free)
5. [Deploy Backend to Railway (Free)](#5-deploy-backend-to-railway-free)
6. [Deploy to VPS / DigitalOcean / Hetzner](#6-deploy-to-vps--digitalocean--hetzner)
7. [Docker Deployment](#7-docker-deployment)
8. [GitHub Actions CI/CD Secrets](#8-github-actions-cicd-secrets)
9. [Domain & SSL Setup](#9-domain--ssl-setup)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Local Development Setup

### Requirements
| Tool       | Version  | Download                        |
|------------|----------|---------------------------------|
| PHP        | 8.2+     | https://php.net                 |
| Composer   | Latest   | https://getcomposer.org         |
| Node.js    | 20+      | https://nodejs.org              |
| MySQL      | 8.0+     | https://mysql.com               |
| Git        | Latest   | https://git-scm.com             |

### Backend (Laravel API)
```bash
cd backend

# Install PHP dependencies
composer install

# Create environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Edit .env and set your database credentials:
# DB_DATABASE=educore_db
# DB_USERNAME=root
# DB_PASSWORD=yourpassword

# Create MySQL database
mysql -u root -p -e "CREATE DATABASE educore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed

# Start API server (runs on http://localhost:8000)
php artisan serve
```

### Frontend (Vue 3)
```bash
cd frontend

# Install Node dependencies
npm install

# Create environment file
cp .env.example .env
# Edit: VITE_API_URL=http://localhost:8000/api

# Start dev server (runs on http://localhost:5173)
npm run dev
```

### Demo Login Accounts
| Role         | Email                    | Password |
|--------------|--------------------------|----------|
| Super Admin  | alex@educore.io          | password |
| Admin        | john@greenfield.edu      | password |
| Teacher      | sarah@greenfield.edu     | password |
| Student      | michael@student.edu      | password |
| Parent       | linda@email.com          | password |
| Accountant   | james@greenfield.edu     | password |
| Librarian    | grace@greenfield.edu     | password |

---

## 2. Push to GitHub

### First-time setup
```bash
# Go to the root of the project
cd educore

# Initialize git repository
git init

# Add all files
git add .

# Make first commit
git commit -m "feat: initial EduCore ERP full-stack project"

# Create a new repo on GitHub.com, then connect it:
git remote add origin https://github.com/YOUR_USERNAME/educore-erp.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### Ongoing development workflow
```bash
# Create a feature branch
git checkout -b feature/my-new-feature

# Make changes, then commit
git add .
git commit -m "feat: add new feature"

# Push branch and open pull request
git push origin feature/my-new-feature
```

---

## 3. Deploy Frontend to Vercel (Free) ⭐ Recommended

Vercel is the easiest way to deploy the Vue frontend — zero config needed.

### Option A: Vercel Dashboard (no CLI needed)
1. Go to **https://vercel.com** → Sign in with GitHub
2. Click **"Add New Project"**
3. Import your `educore-erp` repository
4. Set **Framework Preset** to `Vite`
5. Set **Root Directory** to `frontend`
6. Add **Environment Variable**:
   - Name: `VITE_API_URL`
   - Value: `https://your-backend-url.com/api`
7. Click **Deploy** ✅

### Option B: Vercel CLI
```bash
# Install Vercel CLI
npm install -g vercel

# Go to frontend folder
cd frontend

# Deploy (follow prompts)
vercel

# Deploy to production
vercel --prod
```

### Option C: Automatic via GitHub Actions
The workflow in `.github/workflows/frontend.yml` auto-deploys on every push to `main`.

Set these in **GitHub → Settings → Secrets and Variables → Actions**:
```
VERCEL_TOKEN       → From vercel.com/account/tokens
VERCEL_ORG_ID      → From .vercel/project.json after first deploy
VERCEL_PROJECT_ID  → From .vercel/project.json after first deploy
VITE_API_URL       → https://your-backend-url.com/api
```

Also set this **variable** (not secret):
```
DEPLOY_TARGET = vercel
```

---

## 4. Deploy Frontend to Netlify (Free) ⭐ Also Great

### Option A: Netlify Dashboard
1. Go to **https://netlify.com** → Sign in with GitHub
2. Click **"Add new site"** → **"Import an existing project"**
3. Connect your GitHub repo
4. Set build settings:
   - **Base directory**: `frontend`
   - **Build command**: `npm run build`
   - **Publish directory**: `frontend/dist`
5. Add **Environment Variable**:
   - `VITE_API_URL` = `https://your-backend-url.com/api`
6. Click **Deploy** ✅

The `netlify.toml` file in the root already has all settings configured.

### Option B: Netlify CLI
```bash
npm install -g netlify-cli
cd frontend
npm run build
netlify deploy --prod --dir=dist
```

### Option C: GitHub Actions
Set in GitHub Secrets:
```
NETLIFY_AUTH_TOKEN  → From netlify.com/user/applications
NETLIFY_SITE_ID     → From site settings → Site information
VITE_API_URL        → https://your-backend-url.com/api
DEPLOY_TARGET       → netlify   (set as Variable not Secret)
```

---

## 5. Deploy Backend to Railway (Free) ⭐ Easiest Backend

Railway gives you free PHP + MySQL hosting.

### Steps
1. Go to **https://railway.app** → Sign in with GitHub
2. Click **"New Project"** → **"Deploy from GitHub repo"**
3. Select your `educore-erp` repo
4. Railway auto-detects the `backend/` folder
5. Add a **MySQL** service from Railway dashboard
6. Set these **Environment Variables** in Railway:
```
APP_ENV=production
APP_KEY=          ← Run: php artisan key:generate --show
APP_DEBUG=false
APP_URL=          ← Your Railway app URL

DB_CONNECTION=mysql
DB_HOST=          ← From Railway MySQL connection info
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=      ← From Railway MySQL connection info
DB_PASSWORD=      ← From Railway MySQL connection info

FRONTEND_URL=     ← Your Vercel/Netlify URL
```
7. In Railway **Settings → Deploy** set:
   - **Root Directory**: `backend`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
8. After deploy, run migrations via Railway shell:
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 6. Deploy to VPS / DigitalOcean / Hetzner

Best for full control. Recommended: **DigitalOcean Droplet** ($6/mo) or **Hetzner CX11** (€3.29/mo).

### 6.1 Server Initial Setup
```bash
# SSH into your server
ssh root@YOUR_SERVER_IP

# Update system
apt update && apt upgrade -y

# Install dependencies
apt install -y nginx mysql-server php8.2 php8.2-fpm php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-gd php8.2-zip \
  php8.2-curl php8.2-intl git unzip curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Secure MySQL
mysql_secure_installation
```

### 6.2 Create MySQL Database
```bash
mysql -u root -p

CREATE DATABASE educore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'educore'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON educore_db.* TO 'educore'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6.3 Clone and Configure Backend
```bash
# Create web directory
mkdir -p /var/www/educore
cd /var/www/educore

# Clone your repo
git clone https://github.com/YOUR_USERNAME/educore-erp.git .

# Setup backend
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# Edit .env
nano .env
```

Set these values in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=educore_db
DB_USERNAME=educore
DB_PASSWORD=StrongPassword123!

FRONTEND_URL=https://yourdomain.com
```

```bash
# Run migrations and seed
php artisan migrate --force
php artisan db:seed --force

# Cache config for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/educore/backend
chmod -R 775 /var/www/educore/backend/storage
chmod -R 775 /var/www/educore/backend/bootstrap/cache
```

### 6.4 Build and Deploy Frontend
```bash
cd /var/www/educore/frontend

npm install
echo "VITE_API_URL=https://api.yourdomain.com/api" > .env
npm run build

# Files are now in /var/www/educore/frontend/dist
```

### 6.5 Configure Nginx

```bash
# Backend API config
nano /etc/nginx/sites-available/educore-api
```

Paste this:
```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/educore/backend/public;
    index index.php;

    charset utf-8;
    client_max_body_size 50M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/educore-api-access.log;
    error_log  /var/log/nginx/educore-api-error.log;
}
```

```bash
# Frontend config
nano /etc/nginx/sites-available/educore-frontend
```

Paste this:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/educore/frontend/dist;
    index index.html;

    # SPA routing — all routes go to index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    access_log /var/log/nginx/educore-frontend-access.log;
    error_log  /var/log/nginx/educore-frontend-error.log;
}
```

```bash
# Enable sites
ln -s /etc/nginx/sites-available/educore-api      /etc/nginx/sites-enabled/
ln -s /etc/nginx/sites-available/educore-frontend /etc/nginx/sites-enabled/

# Test config
nginx -t

# Reload Nginx
systemctl reload nginx
```

### 6.6 Set Up SSL with Let's Encrypt (Free HTTPS)
```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Get certificates for both domains
certbot --nginx -d yourdomain.com -d www.yourdomain.com -d api.yourdomain.com

# Auto-renewal is set up automatically
certbot renew --dry-run
```

### 6.7 GitHub Actions Auto-Deploy to VPS

Set these in **GitHub → Settings → Secrets → Actions**:
```
SERVER_HOST     → Your server IP (e.g. 104.248.1.2)
SERVER_USER     → root (or your deploy user)
SERVER_SSH_KEY  → Your private SSH key (contents of ~/.ssh/id_rsa)
SERVER_PORT     → 22
VITE_API_URL    → https://api.yourdomain.com/api
DEPLOY_TARGET   → vps   (set as Variable not Secret)
```

Generate an SSH key for GitHub Actions:
```bash
# On your LOCAL machine
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/educore_deploy

# Copy public key to server
ssh-copy-id -i ~/.ssh/educore_deploy.pub root@YOUR_SERVER_IP

# Add PRIVATE key to GitHub secrets
cat ~/.ssh/educore_deploy
# Copy this entire output → GitHub Secret: SERVER_SSH_KEY
```

---

## 7. Docker Deployment

Run the entire stack (backend + frontend + MySQL) with one command.

### 7.1 Setup
```bash
# Copy root env file
cp .env.example .env

# Edit .env and set:
# APP_KEY=base64:... (generate with: php artisan key:generate --show)
# DB_PASSWORD=StrongPassword123!
# VITE_API_URL=http://localhost:8000/api

# Build and start all containers
docker-compose up -d --build

# Run migrations inside container
docker exec educore_backend php artisan migrate --force
docker exec educore_backend php artisan db:seed --force

# View logs
docker-compose logs -f
```

### 7.2 Services
| Service          | URL                        |
|------------------|----------------------------|
| Frontend (Vue)   | http://localhost:5173       |
| Backend (API)    | http://localhost:8000       |
| MySQL            | localhost:3306              |

### 7.3 Useful Docker Commands
```bash
# Stop all containers
docker-compose down

# Restart a specific service
docker-compose restart backend

# Run artisan commands
docker exec educore_backend php artisan migrate
docker exec educore_backend php artisan db:seed
docker exec educore_backend php artisan cache:clear

# Access MySQL
docker exec -it educore_db mysql -u educore -p

# View backend logs
docker logs educore_backend -f
```

---

## 8. GitHub Actions CI/CD Secrets

Go to: **GitHub repo → Settings → Secrets and variables → Actions**

### Secrets (sensitive values)
| Secret Name        | Description                              | Where to get it                     |
|--------------------|------------------------------------------|-------------------------------------|
| `SERVER_HOST`      | Your VPS IP address                      | DigitalOcean / Hetzner dashboard    |
| `SERVER_USER`      | SSH username (usually `root`)            | Your VPS setup                      |
| `SERVER_SSH_KEY`   | Private SSH key for deploy               | `cat ~/.ssh/id_rsa`                 |
| `SERVER_PORT`      | SSH port (default 22)                    | Your VPS setup                      |
| `VERCEL_TOKEN`     | Vercel API token                         | vercel.com/account/tokens           |
| `VERCEL_ORG_ID`    | Vercel organization ID                   | `.vercel/project.json`              |
| `VERCEL_PROJECT_ID`| Vercel project ID                        | `.vercel/project.json`              |
| `NETLIFY_AUTH_TOKEN`| Netlify personal access token           | netlify.com/user/applications       |
| `NETLIFY_SITE_ID`  | Netlify site ID                          | Site settings → Site information    |
| `VITE_API_URL`     | Your backend API URL                     | https://api.yourdomain.com/api      |
| `DB_USERNAME`      | Database username (for backup job)       | Your database credentials           |
| `DB_PASSWORD`      | Database password (for backup job)       | Your database credentials           |
| `DB_DATABASE`      | Database name (for backup job)           | Usually `educore_db`                |

### Variables (non-sensitive)
| Variable Name   | Value options              | Purpose                        |
|-----------------|----------------------------|--------------------------------|
| `DEPLOY_TARGET` | `vercel`, `netlify`, `vps` | Tells CI which deploy job runs |

---

## 9. Domain & SSL Setup

### Recommended DNS Setup
Point your domain to your server/service:

| Record | Name            | Value                          |
|--------|-----------------|--------------------------------|
| A      | `@`             | Your VPS IP                    |
| A      | `www`           | Your VPS IP                    |
| A      | `api`           | Your VPS IP                    |
| CNAME  | `@`             | your-site.vercel.app (Vercel)  |
| CNAME  | `api`           | your-api.railway.app (Railway) |

### SSL (Let's Encrypt — Free)
```bash
# Install on Ubuntu
apt install certbot python3-certbot-nginx -y

# Issue certificate
certbot --nginx -d yourdomain.com -d www.yourdomain.com -d api.yourdomain.com

# Auto-renew test
certbot renew --dry-run
```

---

## 10. Troubleshooting

### Backend: 500 Internal Server Error
```bash
# Check Laravel logs
tail -f /var/www/educore/backend/storage/logs/laravel.log

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Frontend: Blank page after deploy
```bash
# Make sure the SPA redirect is configured
# Vercel: vercel.json rewrites ✅ already included
# Netlify: netlify.toml redirect ✅ already included
# Nginx: try_files $uri /index.html ✅ in config above

# Check browser console for errors
# Usually VITE_API_URL is wrong — check environment variable
```

### CORS Error (API blocked)
```bash
# Edit backend/.env
FRONTEND_URL=https://yourdomain.com   # NO trailing slash

# Rebuild config cache
php artisan config:cache
```

### Database connection refused
```bash
# Check MySQL is running
systemctl status mysql

# Test connection
mysql -u educore -p educore_db

# Check .env credentials match database
grep DB_ /var/www/educore/backend/.env
```

### GitHub Actions deployment failing
```bash
# Check secrets are set correctly
# GitHub → Settings → Secrets → verify each value

# Test SSH connection manually
ssh -i ~/.ssh/educore_deploy root@YOUR_SERVER_IP

# Check workflow logs in GitHub Actions tab
```

### Migrations fail
```bash
# Drop all tables and re-migrate (⚠️ erases data)
php artisan migrate:fresh --seed

# Or just re-run with force
php artisan migrate --force
php artisan db:seed --force
```

---

## Quick Reference

```bash
# ── LOCAL ──────────────────────────────────────────
cd backend && php artisan serve          # Start API: localhost:8000
cd frontend && npm run dev               # Start UI:  localhost:5173

# ── GIT ────────────────────────────────────────────
git add . && git commit -m "msg"
git push origin main                     # Triggers CI/CD auto-deploy

# ── SERVER ─────────────────────────────────────────
cd /var/www/educore && git pull
cd backend && php artisan migrate --force
php artisan config:cache && php artisan route:cache
systemctl reload nginx

# ── DOCKER ─────────────────────────────────────────
docker-compose up -d --build
docker exec educore_backend php artisan migrate --seed
docker-compose logs -f
docker-compose down
```
