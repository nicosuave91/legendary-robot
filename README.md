# Snowball CRM Sprint 8 Package

This artifact continues from the verified Sprint 7 handoff and implements **Sprint 8 — Rules Library and Workflow Builder** for the tenant-aware CRM platform.

## Delivered in this package

- tenant-scoped **Rules Library** module with:
  - stable `rules` catalog entries
  - immutable `rule_versions`
  - append-only `rule_execution_logs`
  - list/detail/draft update/publish API routes
  - publish-time immutability enforcement
- tenant-scoped **Workflow Builder** module with:
  - stable `workflows` catalog entries
  - immutable `workflow_versions`
  - queued `workflow_runs`
  - append-only `workflow_run_logs`
  - list/detail/draft update/publish/run-monitor API routes
- Sprint 7 **Applications** rule bridge upgraded from a provisional evaluator to governed published rules
- Sprint 7 **Disposition** events exposed as workflow trigger sources without moving lifecycle authority back into Clients
- OpenAPI contract republished for Sprint 8 and the generated TypeScript client regenerated
- frontend routes, navigation, permission map, query keys, and draft/detail monitoring pages for Rules Library and Workflow Builder

## Preserved architectural posture

- lifecycle state still moves through the **Disposition** engine
- `clients.status` remains a **projection cache**, not the authority
- published rules and workflows are immutable
- execution binds to specific published version IDs
- durable rule/workflow execution evidence is append-only and auditable
- Communications was not reopened beyond clean event-boundary integration posture

## Known carry-forward caveats still visible

- the historical **Phase 5 Calendar/Tasks gap** remains unresolved in this artifact unless the real runtime proves closure later
- full Laravel/PHPUnit and full frontend dependency-aware build/typecheck still require installing project dependencies in a normal workspace
- Communications provider signature verification remains a hardening follow-up, not a Sprint 8 redesign trigger

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

4. Verify no handwritten API drift:

```bash
node scripts/verify-no-handwritten-api.mjs
```

## Package notes

- The package preserves earlier sprint notes for historical traceability, but this README reflects the current Sprint 8 handoff state.
- The frontend and backend remain structured for a normal dependency-installed workspace; framework/vendor files are not bundled here.
