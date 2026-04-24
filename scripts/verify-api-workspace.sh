#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_DIR="$ROOT_DIR/apps/api"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
ENV_FILE="$API_DIR/.env"
DB_PATH="$API_DIR/database/database.sqlite"

cd "$API_DIR"

echo "[api] preparing Laravel writable paths"
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p database

echo "[api] preparing deterministic local environment"
if [[ ! -f "$ENV_FILE" ]]; then
  cp .env.example .env
fi

touch "$DB_PATH"

python - <<'PY'
from pathlib import Path
import os

api_dir = Path(os.environ.get('API_DIR_OVERRIDE', '.')).resolve()
env = api_dir / '.env'
db = (api_dir / 'database' / 'database.sqlite').resolve()
text = env.read_text()
updates = {
    'DB_CONNECTION': 'sqlite',
    'DB_DATABASE': str(db),
    'CACHE_STORE': 'array',
    'SESSION_DRIVER': 'file',
    'SESSION_DOMAIN': '127.0.0.1',
    'SANCTUM_STATEFUL_DOMAINS': '127.0.0.1:5173,localhost:5173,127.0.0.1,localhost',
    'CORS_ALLOWED_ORIGINS': 'http://127.0.0.1:5173,http://localhost:5173',
}
for key, value in updates.items():
    line = f'{key}={value}'
    if any(existing.startswith(f'{key}=') for existing in text.splitlines()):
        text = '\n'.join(line if existing.startswith(f'{key}=') else existing for existing in text.splitlines())
    else:
        text = text.rstrip() + '\n' + line + '\n'
env.write_text(text)
PY

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
