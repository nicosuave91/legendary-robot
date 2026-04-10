# Localhost startup in Git Bash

These steps run the Laravel API on `http://127.0.0.1:8000` and the React frontend on `http://127.0.0.1:5173` from Git Bash.

## Prerequisites

Install and confirm these are available in Git Bash:

```bash
php -v
composer -V
node -v
npm -v
```

This repo also uses SQLite for the default local path in the instructions below.

## 1. Clone and install dependencies

```bash
git clone https://github.com/nicosuave91/legendary-robot.git
cd legendary-robot
npm ci
cd apps/api
composer install
cd ../..
```

## 2. Configure the Laravel API

```bash
cd apps/api
cp .env.example .env
php artisan key:generate
mkdir -p database
touch database/database.sqlite
```

Update `.env` so the local database path points at the SQLite file. In Git Bash, this works well:

```bash
sed -i 's#^DB_CONNECTION=.*#DB_CONNECTION=sqlite#' .env
sed -i 's#^DB_DATABASE=.*#DB_DATABASE='"$(pwd)"'/database/database.sqlite#' .env
sed -i 's#^SESSION_DRIVER=.*#SESSION_DRIVER=file#' .env
sed -i 's#^CACHE_STORE=.*#CACHE_STORE=file#' .env
```

Then migrate and seed:

```bash
php artisan migrate:fresh --seed
```

## 3. Point the frontend at the local API

Create `apps/web/.env.local`:

```bash
cd ../web
cat > .env.local <<'ENV'
VITE_API_BASE_URL=http://127.0.0.1:8000
ENV
cd ../..
```

## 4. Start the backend

Use one Git Bash window for the API:

```bash
cd /c/path/to/legendary-robot/apps/api
php artisan serve --host=127.0.0.1 --port=8000
```

## 5. Start the frontend

Use a second Git Bash window for the web app:

```bash
cd /c/path/to/legendary-robot/apps/web
npm run dev -- --host 127.0.0.1 --port 5173
```

Open:

- Frontend: `http://127.0.0.1:5173`
- API: `http://127.0.0.1:8000`

## 6. If the sign-in page shows a CSRF mismatch

Make sure:

- `VITE_API_BASE_URL` is set to `http://127.0.0.1:8000`
- the API is running before you submit the sign-in form
- you are using `127.0.0.1` consistently for both frontend and backend instead of mixing `localhost` and `127.0.0.1`

If needed, clear cookies for both origins and reload.

## 7. Optional contract sync after backend changes

From the repo root:

```bash
php apps/api/scripts/publish-openapi.php
node scripts/generate-web-client.mjs
```
