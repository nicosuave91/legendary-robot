# API Application Layer

This directory now reflects the Sprint 12 release-readiness posture for the Snowball CRM modular Laravel API.

## Sprint 12 hardening delivered here

- communications webhook verification posture upgraded so callback trust is no longer implied without evidence
- release-readiness CI strengthened with dependency install, PHPUnit, contract publication, and Larastan hooks
- release-governance documentation added for migration order, rollback, runtime operations, environment setup, and incident response baseline
- contract and test assets expanded to support final verification of Phase 8 and Phase 9 surfaces

## Architectural guardrails preserved

- controllers remain thin and delegate business behavior to services
- tenant scope remains server-enforced
- Disposition remains lifecycle authority
- `clients.status` remains a projection cache only
- published rule/workflow versions remain immutable
- imports stay staging/validation/commit governed
- notifications preserve lineage rather than deleting source events
- OpenAPI remains the contract source for generated frontend clients

## Runtime expectations before release

- install composer dependencies in a normal workspace
- configure webhook trust secrets and/or public keys
- run migrations and seeders
- publish contracts and regenerate the frontend client
- execute PHPUnit, frontend typecheck/build/tests, queue smoke, and browser verification

## Key closeout docs

- `../../docs/release-readiness-checklist.md`
- `../../docs/deployment-runbook.md`
- `../../docs/rollback-plan.md`
- `../../docs/environment-checklist.md`
- `../../docs/incident-monitoring-baseline.md`
