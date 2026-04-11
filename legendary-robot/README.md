# Snowball CRM Platform

Snowball is a tenant-aware, contract-first CRM platform built as a Laravel modular monolith with a React + Vite frontend. The platform is designed so that business state remains server-owned, cross-tenant access is prevented by default, UI behavior is driven by governed contracts, and high-risk automation paths remain auditable.

## Repository structure

- `apps/api/` — Laravel API, modular domain services, migrations, seeders, contracts source, tests
- `apps/web/` — React frontend, route guards, generated API client consumption, Playwright and Vitest coverage
- `packages/contracts/` — published OpenAPI artifact and contract changelog
- `docs/` — architecture, release, operations, testing, archive, and active platform documentation
- `scripts/` — generation and anti-drift scripts shared at repo level

## Platform principles

1. **Tenant-aware by default.** Data access, workflow execution, imports, and communications must remain tenant scoped.
2. **Contract-first integration.** API changes must be reflected in the Laravel contract source, published into `packages/contracts/openapi.json`, then consumed through the generated web client.
3. **Server-owned business state.** The browser may present workflow state, but it must not become the authority for lifecycle transitions, dispositions, onboarding, or governed automation.
4. **Auditability over convenience.** High-risk domain actions must record correlation-aware audit evidence.
5. **Versioned governance.** Tenant industry configuration, rules, and workflows must resolve through explicit published versions rather than mutable drafts.
6. **Anti-drift by design.** Development, documentation, contract generation, and release verification are all part of the product surface.

## Local setup

### API

```bash
cd apps/api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

### Web

```bash
cd apps/web
npm ci
cp .env.example .env.local
npm run dev
```

The web app defaults to a Vite development proxy for `/api`, `/sanctum`, `/broadcasting`, and `/storage`, so local browser requests can stay same-origin at `http://localhost:5173` while forwarding to the Laravel API at `http://127.0.0.1:8000`.

If your API runs on a different host or port, update `VITE_API_PROXY_TARGET` in `apps/web/.env.local`.

### Contract publication

```bash
php apps/api/scripts/publish-openapi.php
node scripts/generate-web-client.mjs
node scripts/verify-no-handwritten-api.mjs
```

## Validation and release checks

### API
```bash
cd apps/api
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

### Web
```bash
cd apps/web
npm run typecheck
npm run test
npm run build
npx playwright test
```

### Release verification
The canonical release verification flow lives in `docs/release/current-platform-status.md` and `docs/testing/verification-matrix.md`.

## Canonical documentation

Read these first:

- `docs/architecture/finished-platform-thesis-and-dissertation.md`
- `docs/architecture/ongoing-development-blueprint.md`
- `docs/release/current-platform-status.md`
- `docs/testing/verification-matrix.md`
- `docs/operations/repository-hygiene-and-doc-governance.md`

## Historical sprint artifacts

Older sprint handoff, changed-file, and implementation-status documents should not remain authoritative. They should be moved under `docs/archive/` and treated as historical context only.

See `docs/archive/README.md` for the archival policy and the delete-or-archive manifest included in this closure bundle.
