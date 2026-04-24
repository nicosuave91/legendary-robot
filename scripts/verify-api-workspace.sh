#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_DIR="$ROOT_DIR/apps/api"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
DB_PATH="$API_DIR/database/database.sqlite"

cd "$API_DIR"

echo "[api] preparing Laravel writable paths"
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p database

echo "[api] preparing env and sqlite database"
if [[ ! -f .env ]]; then
  cp .env.example .env
fi
touch "$DB_PATH"
php -r '$env=file_get_contents(".env"); $updates=["DB_CONNECTION"=>"sqlite","DB_DATABASE"=>realpath("database/database.sqlite"),"CACHE_STORE"=>"array","SESSION_DRIVER"=>"file","SESSION_DOMAIN"=>"127.0.0.1","SANCTUM_STATEFUL_DOMAINS"=>"127.0.0.1:5173,localhost:5173,127.0.0.1,localhost"]; foreach($updates as $k=>$v){$line=$k."=".$v; if(preg_match("/^".preg_quote($k,"/")."=.*/m",$env)){$env=preg_replace("/^".preg_quote($k,"/")."=.*/m",$line,$env);}else{$env=rtrim($env).PHP_EOL.$line.PHP_EOL;}} file_put_contents(".env",$env);'

echo "[api] installing dependencies"
"$COMPOSER_BIN" install --no-interaction --prefer-dist

echo "[api] ensuring Laravel app key"
php artisan key:generate --force

echo "[api] publishing OpenAPI"
php scripts/publish-openapi.php

echo "[api] validating OpenAPI JSON"
php -r "json_decode(file_get_contents('$ROOT_DIR/packages/contracts/openapi.json'), true, 512, JSON_THROW_ON_ERROR); echo 'OpenAPI JSON valid\n';"

echo "[api] running migrations and seeders"
php artisan migrate:fresh --seed --force

echo "[api] running PHPUnit"
vendor/bin/phpunit

echo "[api] running Larastan"
vendor/bin/phpstan analyse --memory-limit=1G

echo "[api] rollback sanity"
php artisan migrate:rollback --step=1 --force
php artisan migrate --force
