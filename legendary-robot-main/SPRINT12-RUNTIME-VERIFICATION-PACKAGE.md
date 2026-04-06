# Sprint 12 Runtime Verification Package

## Backend verification strategy
- Run `scripts/verify-api-workspace.sh`.
- Require dependency install, OpenAPI publish, fresh migration + seed, PHPUnit, Larastan, and rollback sanity.
- Preserve migration output, test summaries, and static-analysis output as release evidence.

## Frontend verification strategy
- Run `scripts/verify-web-workspace.sh`.
- Require workspace dependency install, typecheck, unit tests, build, Playwright browser install, and browser smoke tests.
- Preserve test output and browser traces/screenshots for major journeys.

## Contract and generated-client sync
- Publish `apps/api/contracts/openapi.php`.
- Regenerate `packages/contracts/openapi.json`.
- Regenerate `apps/web/src/lib/api/generated/client.ts`.
- Run handwritten API drift guard and fail on unreviewed generated diffs.

## Queue and runtime proof
- Validate and commit a staged import with a live queue worker.
- Start a workflow run and confirm execution remains bound to the published workflow version.
- Queue communications sends and confirm current visible state is callback-driven.
- Confirm queue jobs carry tenant and correlation identifiers for traceability.

## Configured-environment integrations
- Twilio messaging/voice callbacks must be exercised with signature enforcement configured correctly for the target environment.
- SendGrid event/inbound verification must be exercised with the configured public key or OAuth bearer token path.
- Any non-production unverified callback posture must remain explicit in runtime evidence and docs.
