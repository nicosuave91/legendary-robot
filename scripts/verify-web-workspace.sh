#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[web] installing workspace dependencies"
npm install

echo "[web] typechecking"
npm run --workspace apps/web typecheck

echo "[web] running unit tests"
npm run --workspace apps/web test

echo "[web] building production bundle"
npm run --workspace apps/web build

echo "[web] installing Playwright browser"
npx playwright install --with-deps chromium

echo "[web] running browser smoke suite"
npm run --workspace apps/web test:e2e
