#!/bin/bash
# =============================================================
# EduCore ERP — GitHub Secrets Setup Helper
# Uses GitHub CLI (gh) to set all required secrets at once
# Usage: bash setup-github-secrets.sh
# Requires: gh cli installed and authenticated
# =============================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
info() { echo -e "${BLUE}[→]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  EduCore — GitHub Secrets Setup          ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# Check gh is installed
if ! command -v gh &>/dev/null; then
  echo "GitHub CLI not found. Install it first:"
  echo "  https://cli.github.com/manual/installation"
  echo "  brew install gh   OR   winget install GitHub.cli"
  exit 1
fi

# Check authenticated
if ! gh auth status &>/dev/null; then
  echo "Not logged in to GitHub CLI. Run:"
  echo "  gh auth login"
  exit 1
fi

# Get repo
REPO=$(gh repo view --json nameWithOwner -q .nameWithOwner 2>/dev/null || echo "")
if [ -z "$REPO" ]; then
  read -p "Enter your GitHub repo (user/repo): " REPO
fi

echo ""
echo "Setting secrets for: ${REPO}"
echo ""

# ── Ask which deployment target ──────────────────
echo "Choose your deployment target:"
echo "  1) Vercel   (frontend) + Railway (backend)"
echo "  2) Netlify  (frontend) + Railway (backend)"
echo "  3) VPS      (both frontend and backend)"
echo ""
read -p "Enter choice [1/2/3]: " DEPLOY_CHOICE

# ── Common secrets ────────────────────────────────
info "Enter your backend API URL (e.g. https://api.yourdomain.com/api):"
read -p "  VITE_API_URL: " VITE_API_URL

# ── Vercel secrets ────────────────────────────────
if [ "$DEPLOY_CHOICE" = "1" ]; then
  warn "You need: Vercel Token, Org ID, and Project ID"
  warn "Get token from: https://vercel.com/account/tokens"
  warn "Get org/project ID from: .vercel/project.json after first 'vercel' deploy"
  echo ""
  read -p "  VERCEL_TOKEN: " VERCEL_TOKEN
  read -p "  VERCEL_ORG_ID: " VERCEL_ORG_ID
  read -p "  VERCEL_PROJECT_ID: " VERCEL_PROJECT_ID

  gh secret set VERCEL_TOKEN      --body "$VERCEL_TOKEN"      --repo "$REPO"
  gh secret set VERCEL_ORG_ID     --body "$VERCEL_ORG_ID"     --repo "$REPO"
  gh secret set VERCEL_PROJECT_ID --body "$VERCEL_PROJECT_ID" --repo "$REPO"
  gh variable set DEPLOY_TARGET   --body "vercel"             --repo "$REPO"
  log "Vercel secrets set"
fi

# ── Netlify secrets ───────────────────────────────
if [ "$DEPLOY_CHOICE" = "2" ]; then
  warn "You need: Netlify Auth Token and Site ID"
  warn "Get token from: https://app.netlify.com/user/applications"
  warn "Get site ID from: Site settings → Site information"
  echo ""
  read -p "  NETLIFY_AUTH_TOKEN: " NETLIFY_AUTH_TOKEN
  read -p "  NETLIFY_SITE_ID: " NETLIFY_SITE_ID

  gh secret set NETLIFY_AUTH_TOKEN --body "$NETLIFY_AUTH_TOKEN" --repo "$REPO"
  gh secret set NETLIFY_SITE_ID    --body "$NETLIFY_SITE_ID"    --repo "$REPO"
  gh variable set DEPLOY_TARGET    --body "netlify"             --repo "$REPO"
  log "Netlify secrets set"
fi

# ── VPS secrets ───────────────────────────────────
if [ "$DEPLOY_CHOICE" = "3" ]; then
  warn "You need: Server IP, SSH user, and SSH private key"
  echo ""
  read -p "  SERVER_HOST (IP): " SERVER_HOST
  read -p "  SERVER_USER (e.g. root): " SERVER_USER
  read -p "  SERVER_PORT (default 22): " SERVER_PORT
  SERVER_PORT=${SERVER_PORT:-22}
  warn "Paste your SSH private key (the content of ~/.ssh/id_rsa)."
  warn "Press ENTER then CTRL+D when done:"
  SERVER_SSH_KEY=$(cat)

  gh secret set SERVER_HOST    --body "$SERVER_HOST"    --repo "$REPO"
  gh secret set SERVER_USER    --body "$SERVER_USER"    --repo "$REPO"
  gh secret set SERVER_PORT    --body "$SERVER_PORT"    --repo "$REPO"
  gh secret set SERVER_SSH_KEY --body "$SERVER_SSH_KEY" --repo "$REPO"
  gh variable set DEPLOY_TARGET --body "vps"            --repo "$REPO"
  log "VPS secrets set"
fi

# ── Database backup secrets ───────────────────────
echo ""
info "Optional: Set database backup secrets (for daily backup job)"
read -p "Set backup secrets? [y/N]: " SETUP_BACKUP
if [ "$SETUP_BACKUP" = "y" ] || [ "$SETUP_BACKUP" = "Y" ]; then
  read -p "  DB_USERNAME: " DB_USERNAME
  read -p "  DB_PASSWORD: " -s DB_PASSWORD; echo ""
  read -p "  DB_DATABASE: " DB_DATABASE

  gh secret set DB_USERNAME --body "$DB_USERNAME" --repo "$REPO"
  gh secret set DB_PASSWORD --body "$DB_PASSWORD" --repo "$REPO"
  gh secret set DB_DATABASE --body "$DB_DATABASE" --repo "$REPO"
  log "Backup secrets set"
fi

# ── VITE_API_URL ──────────────────────────────────
gh secret set VITE_API_URL --body "$VITE_API_URL" --repo "$REPO"
log "VITE_API_URL secret set"

# ── Done ─────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║   ✅  All GitHub secrets configured!         ║"
echo "╠══════════════════════════════════════════════╣"
echo "║  Now push to main to trigger auto-deploy:    ║"
echo "║    git push origin main                      ║"
echo "║                                              ║"
echo "║  Watch it deploy:                            ║"
echo "║    GitHub → Actions tab                      ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
