# Known Issues and Accepted Risks

## Release blockers unless closed in the target workspace
- historical Phase 5 Calendar/Tasks caveat is still open until runtime validation proves closure in the Sprint 12 workspace
- production release must not proceed with webhook signature enforcement enabled unless the correct Twilio auth token path and SendGrid public key/OAuth token are configured and tested

## Accepted only with explicit signoff
- local or CI environments may allow unverified callbacks when enforcement flags are disabled; this is acceptable only for non-production verification environments and must be visible in audit/event metadata
- Twilio callback validation can require `TWILIO_WEBHOOK_BASE_URL` when SSL termination or reverse proxies rewrite the externally visible callback origin

## Deferred follow-up candidates after release
- deeper browser E2E coverage for non-critical module permutations
- richer operational dashboards beyond the baseline logging/correlation posture
