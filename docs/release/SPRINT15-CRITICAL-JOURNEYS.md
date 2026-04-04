# Sprint 15 Critical Journeys

This document captures the runtime journeys that must be exercised before release signoff.

## 1. Owner sign-in → dashboard
Evidence to capture:
- successful sign-in
- redirect to `/app/dashboard`
- protected shell visible
- no CSRF/session mismatch

## 2. Admin/user sign-in → onboarding → dashboard
Evidence to capture:
- onboarding route reached when expected
- profile confirmation works
- industry selection works
- completion returns the user to the protected shell

## 3. Client create → workspace edit → note → document upload
Evidence to capture:
- client created successfully
- redirected into workspace
- note persists
- document metadata persists
- audit surface updates

## 4. Calendar drilldown → event detail → task update → client drilldown
Evidence to capture:
- day view loads
- event detail drawer opens
- task transition persists
- linked client drilldown works

## 5. Client communications → evidence visibility
Evidence to capture:
- send intent is persisted
- timeline entry appears
- callback/provider evidence updates state where available
- failures and retry-safe behavior remain visible

## 6. Client applications → rule note → status transition
Evidence to capture:
- application create succeeds
- rule-note evidence appears
- status transition succeeds or blocks correctly
- audit evidence remains intact

## 7. Rules publish → execution evidence
Evidence to capture:
- rule draft can be reviewed/published
- execution logs are visible after a triggering event

## 8. Workflows publish → run monitoring
Evidence to capture:
- workflow draft can be reviewed/published
- run and run logs appear for a triggering event

## 9. Imports upload → validate → commit → notification → audit
Evidence to capture:
- staged upload succeeds
- validation completes
- commit runs through the queue-backed path
- notification appears
- audit evidence appears

## 10. Audit search for recent actions
Evidence to capture:
- recent actions are searchable
- correlation-driven investigation remains possible
