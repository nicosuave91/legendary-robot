# Sprint C — Email Completion

This package implements the next communications sprint focused on SendGrid email completion.

## Scope
- deterministic inbound email routing by mailbox alias instead of raw `tenant_id` / `client_id` request fields
- thread-level reply mailbox generation for outbound email
- inbound email `EmailLog` parity
- governed outbound email retry / resend using the existing send-email API
- server-side outbound email idempotency when `idempotencyKey` is supplied
- client workspace retry action for failed outbound email items
- feature tests for mailbox routing, reply mailbox generation, and resend/idempotency

## Included files
- `apps/api/config/communications.php`
- `apps/api/app/Modules/Communications/Database/Migrations/2026_04_04_000006_create_communication_mailboxes_table.php`
- `apps/api/app/Modules/Communications/Models/CommunicationMailbox.php`
- `apps/api/app/Modules/Communications/Services/CommunicationMailboxService.php`
- `apps/api/app/Modules/Communications/Http/Requests/SendEmailRequest.php`
- `apps/api/app/Modules/Communications/Services/CommunicationCommandService.php`
- `apps/api/app/Modules/Communications/Services/Providers/SendGridEmailAdapter.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Webhooks/SendGridInboundWebhookController.php`
- `apps/web/src/features/communications/components/client-communications-panel.tsx`
- `apps/api/app/Modules/Communications/Tests/Feature/SendGridEmailCompletionFeatureTest.php`

## Apply
Copy these files into the repo root, preserving paths.

Then run from `apps/api`:

```bash
php scripts/publish-openapi.php
find app tests bootstrap config database routes -name "*.php" -print0 | xargs -0 -n1 php -l
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/phpunit
```
