# Sprint 8 Handoff

## Starting posture used

- continued from the patched Sprint 7 package
- preserved Disposition as the lifecycle authority
- preserved Applications immutable rule-note evidence
- did not reopen Communications except through clean workflow trigger boundaries
- kept the historical Phase 5 Calendar/Tasks caveat visible

## Core Sprint 8 outcomes delivered

### Rules Library
- stable `rules` catalog with tenant scope
- editable `rule_versions` drafts and immutable published versions
- `POST /api/v1/rules/{ruleId}/publish`
- append-only `rule_execution_logs`
- frontend Rules Library list/detail/edit/publish/execution-log surfaces

### Workflow Builder
- stable `workflows` catalog with tenant scope
- editable `workflow_versions` drafts and immutable published versions
- `POST /api/v1/workflows/{workflowId}/publish`
- queued `workflow_runs` and append-only `workflow_run_logs`
- workflow trigger matcher listening to Sprint 7 Application and Disposition domain events
- frontend Workflow Builder list/detail/edit/publish/run-monitor surfaces

### Sprint 7 bridge closure
- Applications now resolves governed published rule versions instead of the prior hard-coded evaluator
- application rule evidence now persists `rule_id` and `rule_version_id` while preserving immutable snapshots for operator review

## Validation completed in artifact

- PHP syntax lint across new/modified Sprint 8 backend files
- OpenAPI republished to `packages/contracts/openapi.json`
- generated frontend client regenerated from Sprint 8 contract
- isolated TypeScript transpilation syntax check passed for Sprint 8 frontend files

## Validation still requiring a normal workspace

- full Laravel/PHPUnit execution
- frontend dependency-aware typecheck and build

## Suggested next step for Sprint 9 handoff

Use this package as the new source of truth and preserve:
- governed Rules Library versioning
- immutable Workflow Builder publication lifecycle
- Applications consuming published rules
- Disposition remaining the only lifecycle authority
