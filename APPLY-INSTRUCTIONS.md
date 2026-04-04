# Apply Instructions

This package contains the Sprint 15 patch set as repo-relative files.

## How to apply
1. unzip this package at a temporary location
2. copy each file into the matching path in the repo
3. run the backend and frontend validation commands
4. execute the Playwright smoke suite in a real workspace
5. review and complete the release checklist
6. commit the applied changes into your Sprint 15 branch

## Suggested validation commands

### Backend
- composer install
- php artisan key:generate
- php artisan migrate:fresh --seed
- composer run contracts:publish
- composer test
- ./vendor/bin/phpstan analyse

### Frontend
- npm ci
- npm run typecheck
- npm run test
- npm run build

### Playwright
- npx playwright install --with-deps chromium
- VITE_API_BASE_URL=http://127.0.0.1:8000 npx playwright test e2e/release-critical-journeys.spec.ts
