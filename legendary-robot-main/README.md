# Sprint G — Communications Prerelease Stabilization

This bundle implements the communications prerelease stabilization sprint.

## Scope
- restores the communications contract as the source of truth
- adds inbox and cursor support to the communications OpenAPI fragment
- removes compile-time dependence on the stale generated communications client
- adds attachment scan-status wrapper support in the web API layer
- adds a contract test for release-critical communications paths
- adds contract-sync verification and an ordered release-check script
- adds a dedicated GitHub Actions workflow for communications prerelease validation
- adds an operator-facing prerelease checklist

## Included files
- `package.json`
- `scripts/verify-communications-contract-sync.mjs`
- `scripts/release-communications-check.mjs`
- `.github/workflows/communications-prerelease.yml`
- `apps/api/contracts/openapi.communications.php`
- `apps/api/tests/Contract/CommunicationsOpenApiContractTest.php`
- `apps/web/src/lib/api/client.ts`
- `docs/release/communications-prerelease-checklist.md`

## Apply
Copy these files into the repo root, preserving paths.

Then run:

```bash
npm ci
composer install --working-dir=apps/api
npm run release:communications:check
```
