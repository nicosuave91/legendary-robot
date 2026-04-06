# Release Readiness Checklist

## Architecture and governance
- [ ] Disposition remains lifecycle authority.
- [ ] `clients.status` remains projection-only.
- [ ] Published rules are immutable.
- [ ] Published workflows are immutable and execution binds to version IDs.
- [ ] Workflow publish is blocked when `draftValidation.isValid === false`.
- [ ] Controllers remain thin and service-oriented.
- [ ] Audit records remain append-only and reviewable.

## Contract and client integrity
- [ ] `apps/api/contracts/openapi.php` publishes cleanly.
- [ ] workflow closure schemas are merged from `apps/api/contracts/openapi.workflow_closure.php`.
- [ ] `packages/contracts/openapi.json` is current.
- [ ] `apps/web/src/lib/api/generated/client.ts` matches the published contract.
- [ ] `scripts/verify-workflow-contract-closure.mjs` passes.
- [ ] handwritten API guard passes.

## Runtime verification
- [ ] migrations run cleanly in a normal workspace.
- [ ] seeders run cleanly.
- [ ] queue worker smoke passes for imports and workflow jobs.
- [ ] webhook entry points are configured and trusted.
- [ ] frontend typecheck/build passes.
- [ ] backend test suite passes.
- [ ] browser smoke/E2E passes for critical journeys.

## Critical journeys
- [ ] sign-in and onboarding
- [ ] homepage and shell
- [ ] client workspace
- [ ] communications timeline and callback projection
- [ ] disposition transition
- [ ] application status and rule evidence
- [ ] workflow detail surfaces publish blockers before publish
- [ ] workflow publish → trigger → run → side-effect evidence
- [ ] import upload/validate/commit
- [ ] notification read/dismiss
- [ ] audit search and review

## Operational readiness
- [ ] environment variables are complete.
- [ ] migration order is documented.
- [ ] rollback plan is approved.
- [ ] deployment runbook is reviewed.
- [ ] monitoring and incident baseline is assigned.
- [ ] known issues and accepted risks are documented.
