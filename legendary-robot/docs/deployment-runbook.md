# Deployment Runbook

## Pre-deploy
1. Confirm release checklist signoff.
2. Snapshot the database or validate backup freshness.
3. Confirm webhook secrets/public keys are present in the target environment.
4. Confirm the published OpenAPI artifact and generated client are committed.

## Deploy order
1. Put the application into the agreed maintenance posture if required.
2. Deploy application code.
3. Run database migrations.
4. Warm configuration/cache as appropriate for the environment.
5. Restart queue workers.
6. Verify scheduler is active.
7. Confirm webhook routes are reachable.
8. Run post-deploy smoke tests.

## Post-deploy smoke
- sign in as owner/admin/user
- open homepage shell
- open a client workspace
- verify imports list loads
- verify notifications load
- verify audit search loads
- verify a queued import validate/commit cycle processes correctly
- verify one Twilio/SendGrid callback reaches the application, is recorded with the expected verification posture, and remains tenant-safe

## Release evidence to preserve
- migration output
- contract publish output
- test summaries
- queue smoke output
- any callback verification logs
