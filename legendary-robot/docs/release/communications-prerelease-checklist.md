# Communications prerelease checklist

Use this checklist in order before the first communications-enabled release candidate is approved.

## 1. Install dependencies
From the repository root:

```bash
npm ci
composer install --working-dir=apps/api
```

## 2. Rebuild contract artifacts
```bash
npm run contracts:sync
```

Expected outcome:
- `packages/contracts/openapi.json` is regenerated from the PHP contract source
- `apps/web/src/lib/api/generated/client.ts` is regenerated from the published contract
- no unexpected diff remains after regeneration unless new contract work is intentionally part of the release

## 3. Verify communications contract sync
```bash
npm run verify:communications:contract-sync
```

This must confirm:
- communications inbox exists in the published contract
- timeline cursor support exists in the published contract
- attachment scan-status update exists in the published contract
- the generated TypeScript client exposes those operations

## 4. Run the no-handwritten-API guard
```bash
npm run guard:no-handwritten-api
```

## 5. Validate the web app
```bash
npm --workspace apps/web run typecheck
npm --workspace apps/web run test
npm --workspace apps/web run build
```

## 6. Validate the API
```bash
php apps/api/vendor/bin/phpstan analyse --memory-limit=1G
php apps/api/vendor/bin/phpunit
```

## 7. End-to-end communications smoke review
Manually verify these release-critical flows:
1. queue outbound SMS
2. queue outbound email
3. queue outbound call
4. load client communications timeline with pagination
5. load top-level communications inbox
6. retry a failed email from the client timeline
7. confirm non-clean attachments are blocked from provider delivery
8. confirm a clean attachment is served by the signed public attachment route
9. confirm webhook callbacks still move lifecycle status correctly

## 8. One-command release gate
After the individual steps pass, run:

```bash
npm run release:communications:check
```

That command is the final prerelease gate for the communications surface.
