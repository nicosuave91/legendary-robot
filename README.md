# Sprint D — Voice Completion

This package implements the next communications sprint focused on Twilio voice completion.

## Scope
- replace the placeholder TwiML URL with a real outbound call-control flow
- add agent whisper TwiML so internal purpose notes are spoken only to the agent leg
- add governed call retry using the existing start-call API
- add server-side call idempotency when `idempotencyKey` is supplied
- persist purpose note, bridged agent number, retry lineage, and answer time on `call_logs`
- add failed-call retry action in the client workspace
- update voice callback handling so answer/start timestamps are captured
- feature tests for TwiML routes, call idempotency/retry, and callback lifecycle updates

## Included files
- `apps/api/config/communications.php`
- `apps/api/app/Modules/Communications/Database/Migrations/2026_04_04_000007_add_voice_flow_fields_to_call_logs_table.php`
- `apps/api/app/Modules/Communications/Models/CallLog.php`
- `apps/api/app/Modules/Communications/Services/CommunicationCommandService.php`
- `apps/api/app/Modules/Communications/Services/Providers/TwilioVoiceAdapter.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Webhooks/TwilioVoiceWebhookController.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Webhooks/TwilioVoiceTwiMLController.php`
- `apps/api/app/Modules/Communications/Routes/webhooks.php`
- `apps/web/src/features/communications/components/client-communications-panel.tsx`
- `apps/api/app/Modules/Communications/Tests/Feature/TwilioVoiceCompletionFeatureTest.php`

## Apply
Copy these files into the repo root, preserving paths.

Then run from `apps/api`:

```bash
php scripts/publish-openapi.php
find app tests bootstrap config database routes -name "*.php" -print0 | xargs -0 -n1 php -l
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/phpunit
```
