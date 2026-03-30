# Snowball CRM Sprint 1 Foundation

This artifact implements **Sprint 1 — Platform Foundation and Guardrails** for the tenant-aware CRM platform. It is intentionally scoped to foundation work only:

- repository and module topology
- React + TypeScript + Vite shell bootstrap
- local design-system layer built on shadcn-style code-first primitives
- centralized design tokens via CSS variables
- Laravel modular monolith skeleton
- versioned API namespace
- OpenAPI contract publication
- generated TypeScript client
- CI/CD quality gates
- ADR and dependency governance
- baseline authentication scaffolding only

It does **not** implement Sprint 2+ business flows such as onboarding, user management UI, clients, communications, workflows, imports, or persistent business notifications.

## Repository layout

- `apps/web` — React SPA
- `apps/api` — Laravel application-layer skeleton
- `packages/contracts` — published OpenAPI artifact
- `docs` — ADRs and engineering governance
- `scripts` — contract generation and guardrail scripts

## Contract pipeline

1. Edit contract source in `apps/api/contracts/openapi.php`
2. Publish the canonical artifact:

```bash
php apps/api/scripts/publish-openapi.php
```

3. Generate the frontend client:

```bash
node scripts/generate-web-client.mjs
```

4. Verify drift guardrails:

```bash
node scripts/verify-no-handwritten-api.mjs
```

## Notes

- The frontend is implementation-ready and structured to run in a normal Node workspace once dependencies are installed.
- The backend contains Laravel application-layer code and conventions, but does not vendor the full framework runtime in this artifact.
- All async, audit, tenant, and policy conventions are scaffolded in code so later sprints do not need to restructure the foundation.
