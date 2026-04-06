# Sprint 12 Regression and Quality Package

## Regression suite focus
- owner login and homepage
- admin-created user onboarding
- homepage operational surfaces
- calendar selected-day drilldown
- client workspace navigation
- communications send plus callback projection
- disposition transition
- applications create plus rule-note evidence
- immutable rule publication
- immutable workflow publication and run behavior
- import upload/validate/commit
- notification read/dismiss
- audit search/read

## Tenant-isolation verification
- deny cross-tenant reads and writes on clients, events, tasks, imports, and audit.
- ensure provider callbacks cannot reconcile by phone/email alone without tenant-safe correlation.
- ensure queued jobs retain tenant context.

## Accessibility posture
- keyboard reachability for shell navigation, notifications, calendar, dialogs, and client workspace tabs.
- visible focus states and icon-button labels.
- dialog focus trap and return-focus checks.
- color-contrast review for status badges and theme tokens.

## Visual regression posture
- capture stable screenshots for sign-in, homepage, calendar, event drawer, client workspace, imports, notifications, and audit.
- compare against an approved baseline or preserve structured screenshot evidence for release review.

## Route and permission verification
- add route-guard tests for unauthenticated, onboarding-incomplete, and permission-denied states.
- verify sidebar visibility reflects the permission map instead of page-local assumptions.
