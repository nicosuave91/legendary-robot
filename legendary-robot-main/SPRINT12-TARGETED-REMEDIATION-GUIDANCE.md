# Sprint 12 Targeted Remediation Guidance

## Remediation boundaries
- fix only confirmed runtime defects, release blockers, or evidence gaps
- do not reopen stable Sprint 1–11 modules for stylistic refactors
- keep controllers thin and preserve service-owned business rules
- keep lifecycle authority in the Disposition engine
- keep rule and workflow publication immutable
- keep audit/history append-only

## Module-safe fixes
- Calendar & Tasks: repair runtime defects only; do not redesign authority boundaries.
- Communications: strengthen callback trust, queue execution, and evidence; do not move transport authority into the browser.
- Imports: preserve stage/validate/commit sequencing and audit lineage.
- Rules/Workflows: preserve version binding and immutable publication.
- Notifications/Audit: preserve durable evidence and permission-safe read surfaces.

## DTO and API verification
- publish OpenAPI before and after remediation
- regenerate the TypeScript client for any contract change
- fail on handwritten API usage or unreviewed generated diffs
- add or expand contract and request-level tests for any remediated endpoint

## Failure handling
- if a release blocker is found, stop broad changes and patch the blocker narrowly
- if verification is blocked by environment gaps, mark the gate `not verified` instead of pretending it passed
- if rollback safety is uncertain, prefer code rollback without schema rollback until data safety is confirmed
