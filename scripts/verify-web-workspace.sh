#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[web] forcing public npm registry"
npm config set registry https://registry.npmjs.org/

echo "[web] verifying npm cache"
npm cache verify

echo "[web] installing workspace dependencies from lockfile"
npm ci

echo "[web] typechecking"
npm run --workspace apps/web typecheck

echo "[web] running unit tests"
npm run --workspace apps/web test

echo "[web] building production bundle"
npm run --workspace apps/web build

echo "[web] installing Playwright browser"
npx playwright install --with-deps chromium

echo "[web] running mocked browser shell smoke suite"
echo "[web] note: live API/runtime proof is owned by seeded PHPUnit feature coverage"
npm run --workspace apps/web test:e2e:mocked-shell
