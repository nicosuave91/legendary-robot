# Sprint 9 Handoff

## Source-of-truth posture

This package now treats Sprint 8 as the preserved baseline and layers Sprint 9 imports, notifications, audit-search, and hardening work on top of it.

Preserved guardrails:
- Disposition remains lifecycle authority
- `clients.status` remains a projection cache, not lifecycle authority
- Applications still bind to governed published rule versions
- Rules Library and Workflow Builder published versions remain immutable
- OpenAPI plus generated frontend client remain the contract source of truth

## Sprint 9 module additions

### Imports
- `GET /api/v1/imports`
- `POST /api/v1/imports`
- `GET /api/v1/imports/{importId}`
- `GET /api/v1/imports/{importId}/errors`
- `POST /api/v1/imports/{importId}/validate`
- `POST /api/v1/imports/{importId}/commit`
- governed staging tables and CSV validation pipeline
- commit orchestration through `ClientService::createFromImport()`

### Notifications
- `GET /api/v1/notifications`
- `POST /api/v1/notifications/{notificationId}/dismiss`
- `POST /api/v1/notifications/{notificationId}/read`
- persistent center surfaces in header bell and tray
- per-user dismissal/read lineage preserved without deleting source rows

### Audit
- `GET /api/v1/audit`
- append-only audit search baseline for Sprint 9 operational review

## Recommended next validation step

Use this package as the new source of truth and validate it in a normal workspace with:
- migrations
- Laravel boot + feature execution
- queue worker smoke for import validation/commit
- frontend install + typecheck + build
- E2E coverage for imports and notifications

## Suggested Sprint 10 posture

Treat Sprint 9 as a real feature-complete foundation for final release readiness, not as a reason to reopen Sprint 6–8 architecture unless the normal workspace proves a narrow blocking defect.
