# Snowball CRM Sprint 12 Package

This artifact continues from the completed Sprint 11 handoff and implements **Sprint 12 — Full Runtime Verification, Integration Hardening, and Release-Readiness Reassessment** for the tenant-aware CRM platform.

## Sprint 12 closeout outcomes delivered

- communications webhook trust hardening for **Twilio** and **Twilio SendGrid** callbacks
  - Twilio callback verification now routes through signature validation posture instead of blindly treating callbacks as verified
  - SendGrid Event Webhook and Inbound Parse callback verification now support signed webhook validation using raw request bytes and optional OAuth bearer-token validation
  - callback audit/event lineage now records whether the callback was truly verified or only accepted in a non-enforced environment
- release-governance artifacts for final closeout
  - release readiness checklist
  - deployment runbook
  - rollback plan
  - environment checklist
  - monitoring and incident baseline
  - Sprint 12 execution, verification, backlog, checklist, status, and changed-files artifacts
- CI/CD hardening for release-candidate posture
  - API workflow now installs composer dependencies, runs contract publish, PHPUnit, and Larastan
  - contract workflow verifies publish/generate/guard discipline
  - release-candidate workflow runs cross-stack checks and uploads the core artifacts
- testing expansion for Sprint 12 release-readiness closeout
  - communications webhook verification unit coverage
  - release-focused contract coverage for Phase 8/9 surfaces
  - frontend component coverage for notification, import commit, and audit filter behaviors

## Preserved architectural posture

- lifecycle state still moves through the **Disposition** engine
- `clients.status` remains a **projection cache**, not the authority
- published rules and workflows remain immutable and execution still binds to specific published version IDs
- imports remain staged/validated/explicitly committed
- notifications retain read/dismiss lineage without deleting source rows
- OpenAPI plus generated frontend client remain the contract source of truth

## Sprint 12 posture

Sprint 12 is treated as a **proof and closeout sprint**, not a new feature phase. Stable Sprint 1–11 modules were not reopened unless they had a confirmed release-readiness or hardening gap.

## Known carry-forward caveats still requiring real workspace proof

- the historical **Phase 5 Calendar/Tasks caveat** still requires normal-workspace verification unless runtime proof closes it
- full dependency-installed Laravel and frontend execution still must be completed in a normal workspace
- provider secrets, signed webhook public keys, and OAuth tokens must be configured in the target environment before production release

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

## Sprint 12 handoff set

- `SPRINT10-IMPLEMENTATION-STATUS.md`
- `SPRINT10-HANDOFF.md`
- `SPRINT10-CHANGED-FILES.txt`
- `docs/release-readiness-checklist.md`
- `docs/deployment-runbook.md`
- `docs/rollback-plan.md`
- `docs/environment-checklist.md`
- `docs/incident-monitoring-baseline.md`
- `docs/known-issues-and-accepted-risks.md`
