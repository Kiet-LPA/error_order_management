#!/usr/bin/env bash
# Deploy script chạy TRÊN server production (được gọi từ GitHub Actions SSH).
# Usage: bash scripts/deploy.sh

set -euo pipefail

# Về root project (thư mục chứa artisan)
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

echo "==> Deploy path: $ROOT"
echo "==> User: $(whoami)"
echo "==> Branch target: main"

if [ ! -f artisan ]; then
  echo "ERROR: artisan not found"
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: php not found in PATH"
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "ERROR: composer not found in PATH"
  exit 1
fi

if ! command -v git >/dev/null 2>&1; then
  echo "ERROR: git not found in PATH"
  exit 1
fi

echo "==> Maintenance mode ON"
php artisan down --retry=60 || true

echo "==> Fetch & reset to origin/main"
git fetch origin main
git checkout main
git reset --hard origin/main

echo "==> Composer install (production)"
composer install \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader \
  --no-dev

if command -v npm >/dev/null 2>&1; then
  echo "==> npm build (Vite)"
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
  npm run build
else
  echo "WARN: npm not found — bỏ qua build frontend"
fi

echo "==> Run migrations"
php artisan migrate --force

echo "==> Optimize caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Không fail nếu link đã tồn tại
php artisan storage:link 2>/dev/null || true

echo "==> Maintenance mode OFF"
php artisan up

echo "==> Deploy OK — $(date '+%Y-%m-%d %H:%M:%S')"
