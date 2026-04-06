# Release Readiness Checklist

## Backend verification
- [ ] `composer install` completed successfully in `apps/api`
- [ ] migrations and seed path executed successfully
- [ ] OpenAPI artifact published successfully
- [ ] PHPUnit passed
- [ ] Larastan passed

## Frontend verification
- [ ] `npm ci` completed successfully in `apps/web`
- [ ] `npm run typecheck` passed
- [ ] `npm run test` passed
- [ ] `npm run build` passed
- [ ] Playwright smoke suite passed

## Runtime critical journeys
- [ ] owner sign-in → dashboard
- [ ] admin/user sign-in → onboarding → dashboard
- [ ] client create → workspace edit → note → document upload
- [ ] calendar drilldown → event detail → task update → client drilldown
- [ ] workspace communications send → evidence visible
- [ ] workspace application create → rule note → status transition
- [ ] rule publish → execution evidence visible
- [ ] workflow publish → run monitoring visible
- [ ] import upload → validate → commit → notification → audit
- [ ] audit search returns recent actions

## Environment verification
- [ ] Twilio configuration reviewed
- [ ] SendGrid configuration reviewed
- [ ] webhook trust/signature posture reviewed
- [ ] queue worker runtime reviewed
- [ ] known issues and residual risks documented

## Release decision
- [ ] go / no-go decision recorded
- [ ] final handoff package prepared
