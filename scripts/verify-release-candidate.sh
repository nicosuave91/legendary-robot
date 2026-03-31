#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[contracts] publish"
php apps/api/scripts/publish-openapi.php

echo "[contracts] generate web client"
node scripts/generate-web-client.mjs

echo "[contracts] verify no handwritten API drift"
node scripts/verify-no-handwritten-api.mjs

echo "[contracts] ensure generated sources are committed"
git diff --exit-code -- packages/contracts/openapi.json apps/web/src/lib/api/generated/client.ts

echo "[release] verify API workspace"
"$ROOT_DIR/scripts/verify-api-workspace.sh"

echo "[release] verify web workspace"
"$ROOT_DIR/scripts/verify-web-workspace.sh"
