# Sprint 8 Implementation Status

## Delivered in this package

- Rules Library backend module with rule catalog, immutable versions, publish lifecycle, execution logs, routes, requests, services, and provider registration
- Workflow Builder backend module with workflow catalog, immutable versions, publish lifecycle, queued run engine, run logs, routes, requests, services, jobs, listeners, and provider registration
- Applications rule evaluation upgraded to a governed published-rule path with `rule_id` and `rule_version_id` evidence persisted alongside immutable rule-note snapshots
- Disposition and Applications domain events exposed as workflow trigger sources without weakening existing module boundaries
- OpenAPI contract updated to Sprint 8 and generated web client regenerated
- Frontend routes, sidebar entries, permissions, query keys, API wrappers, and list/detail pages for Rules Library and Workflow Builder
- Permission seeding updated for rule/workflow read, create, publish, update-draft, execution-log, and run-monitor capabilities

## Carry-forward caveats preserved

- Historical Phase 5 Calendar/Tasks gap remains documented and was not reopened in Sprint 8
- Communications provider-signature verification remains a hardening follow-up and was not destabilized here

## Validation completed

- PHP syntax lint passed for the new and modified backend Sprint 8 files
- OpenAPI artifact republished to `packages/contracts/openapi.json`
- Generated TypeScript client refreshed to include Sprint 8 rule/workflow operations
- Frontend routes, wrapper layer, and pages were added against the regenerated contract

## Validation not completed in this package

- Full Laravel/PHPUnit execution
- Full frontend dependency-aware typecheck or Vite build

Those runtime steps still require the project dependencies to be installed in the execution environment.
