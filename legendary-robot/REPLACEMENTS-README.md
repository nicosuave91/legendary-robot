These replacement files target the merge blockers surfaced in the latest CI logs:

1. Web CI workflow setup and strict type errors in applications, disposition, client workspace, and imports.
2. API test runtime blockers: Mockery missing, Laravel writable cache directories, OpenAPI publish hardening, and SendGrid config access in pure unit tests.

Important notes:
- This bundle intentionally includes `apps/api/composer.json`, but not a regenerated `apps/api/composer.lock`. After copying the files into your real repo clone, run `cd apps/api && composer install --no-interaction --prefer-dist` to refresh the lockfile and vendor tree.
- A valid `packages/contracts/openapi.json` is included. After replacing files, also run `php apps/api/scripts/publish-openapi.php` from the repo root and verify the JSON parses cleanly.

Suggested follow-up commands from the repo root:

```bash
cd apps/api
composer install --no-interaction --prefer-dist
cd ../..
php apps/api/scripts/publish-openapi.php
php -r "json_decode(file_get_contents('packages/contracts/openapi.json'), true, 512, JSON_THROW_ON_ERROR); echo 'openapi ok\n';"
node scripts/generate-web-client.mjs
npm run --workspace apps/web typecheck
```
