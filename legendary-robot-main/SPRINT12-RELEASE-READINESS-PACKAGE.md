# Sprint 12 Release-Readiness Package

## Blocker triage
Classify each issue as one of:
- release blocker
- validation blocker
- accepted risk with signoff
- post-release follow-up

Release blockers include tenant-isolation failures, contract drift, migration failures, callback-trust failures, broken calendar/task history, import integrity failures, or immutable versioning regressions.

## Accepted-risk posture
Accepted risk is allowed only when:
- it does not weaken business authority, tenant isolation, callback trust in production, or contract discipline
- it is documented in `docs/known-issues-and-accepted-risks.md`
- it has an owner and a follow-up plan

## Governance artifacts to reconcile
- `docs/release-readiness-checklist.md`
- `docs/deployment-runbook.md`
- `docs/environment-checklist.md`
- `docs/rollback-plan.md`
- `docs/incident-monitoring-baseline.md`
- `docs/known-issues-and-accepted-risks.md`

## Final RC evidence package
Preserve:
- migration output
- contract publication output
- generated-client output
- PHPUnit and Larastan summaries
- frontend typecheck/test/build summaries
- Playwright smoke output
- queue/runtime smoke output
- configured-environment callback verification evidence
- blocker register and accepted-risk register
