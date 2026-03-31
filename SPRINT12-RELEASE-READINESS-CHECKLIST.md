# Sprint 12 Release-Readiness Checklist

## Contract and drift control
- [ ] OpenAPI publishes cleanly.
- [ ] Generated TypeScript client matches the published contract.
- [ ] Handwritten API drift guard passes.
- [ ] No unreviewed generated diffs remain.

## Backend runtime
- [ ] Composer dependencies install cleanly.
- [ ] Migrations run cleanly on a fresh database.
- [ ] Seeders run cleanly.
- [ ] PHPUnit passes.
- [ ] Larastan passes.
- [ ] Rollback sanity has been exercised and documented.

## Frontend runtime
- [ ] Workspace dependencies install cleanly.
- [ ] Typecheck passes.
- [ ] Unit/component tests pass.
- [ ] Production build passes.
- [ ] Browser smoke suite passes.

## Critical architecture gates
- [ ] Calendar -> event -> task flow is proven with history tracking.
- [ ] Communications status is callback-driven and visible in the UI.
- [ ] Disposition remains lifecycle authority.
- [ ] `clients.status` remains projection-only.
- [ ] Published rules are immutable.
- [ ] Published workflows are immutable and execution binds to version IDs.
- [ ] Imports only commit after validation.
- [ ] Audit remains append-only and reviewable.

## Tenant isolation and policy
- [ ] Critical cross-tenant deny paths have passing tests.
- [ ] Queue jobs preserve tenant context.
- [ ] Queue jobs preserve correlation identifiers.
- [ ] Callback reconciliation is tenant-safe.

## Operational readiness
- [ ] Environment checklist is complete.
- [ ] Deployment runbook is reconciled.
- [ ] Rollback plan is reconciled.
- [ ] Monitoring baseline is assigned.
- [ ] Known issues and accepted risks are current.
- [ ] Final RC evidence package is assembled.
