# Sprint E — Communications Timeline and Inbox Completion

This bundle implements the next communications sprint after voice completion.

## Scope
- real cursor pagination for client communications timelines
- new server-filtered communications inbox endpoint
- inbox aggregation across visible clients
- top-level communications page becomes an operational inbox
- client workspace timeline supports loading older activity
- contract updates for inbox and cursor parameters
- backend feature tests for timeline paging and inbox ordering/filtering

## Included files
- `apps/api/app/Modules/Communications/Http/Requests/ListClientCommunicationsRequest.php`
- `apps/api/app/Modules/Communications/Http/Requests/ListCommunicationsInboxRequest.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Api/V1/ClientCommunicationsController.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Api/V1/CommunicationsInboxController.php`
- `apps/api/app/Modules/Communications/Routes/api.php`
- `apps/api/app/Modules/Communications/Services/CommunicationTimelineService.php`
- `apps/api/app/Modules/Communications/Services/CommunicationsInboxService.php`
- `apps/api/contracts/openapi.communications.php`
- `apps/web/src/lib/api/query-keys.ts`
- `apps/web/src/lib/api/client.ts`
- `apps/web/src/features/communications/pages/communications-page.tsx`
- `apps/web/src/features/communications/components/client-communications-panel.tsx`
- `apps/api/app/Modules/Communications/Tests/Feature/CommunicationsInboxAndTimelineFeatureTest.php`

## Apply
Copy these files into the repo root, preserving paths.

Then run from `apps/api`:
```bash
php scripts/publish-openapi.php
find app tests bootstrap config database routes -name "*.php" -print0 | xargs -0 -n1 php -l
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/phpunit
```
