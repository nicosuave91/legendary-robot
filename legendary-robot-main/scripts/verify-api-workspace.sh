#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_DIR="$ROOT_DIR/apps/api"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

cd "$API_DIR"

echo "[api] preparing Laravel writable paths"
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views

echo "[api] installing dependencies"
"$COMPOSER_BIN" install --no-interaction --prefer-dist

echo "[api] preparing sqlite database"
mkdir -p database
touch database/database.sqlite

echo "[api] publishing OpenAPI"
php scripts/publish-openapi.php

echo "[api] validating OpenAPI JSON"
php -r "json_decode(file_get_contents('$ROOT_DIR/packages/contracts/openapi.json'), true, 512, JSON_THROW_ON_ERROR); echo 'OpenAPI JSON valid\n';"

echo "[api] running migrations and seeders"
php artisan migrate:fresh --seed

echo "[api] running PHPUnit"
vendor/bin/phpunit

echo "[api] running Larastan"
vendor/bin/phpstan analyse --memory-limit=1G

echo "[api] rollback sanity"
php artisan migrate:rollback --step=1
php artisan migrate
