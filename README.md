# Sprint F — Communications Attachment Security Completion

This bundle implements the next communications sprint after timeline/inbox completion.

## Scope
- attachment security lifecycle metadata
- tenant-authenticated attachment scan-status update endpoint
- outbound provider policy enforcement for Twilio and SendGrid
- stricter signed public attachment serving (clean-only)
- upload policy validation for manual communication attachments
- scan-status visibility in the client communications timeline UI
- backend feature tests for scan-status updates, public delivery gating, and provider submission blocking

## Included files
- `apps/api/config/communications.php`
- `apps/api/app/Modules/Communications/Database/Migrations/2026_04_04_000008_add_scan_lifecycle_fields_to_communication_attachments_table.php`
- `apps/api/app/Modules/Communications/Models/CommunicationAttachment.php`
- `apps/api/app/Modules/Communications/Services/CommunicationAttachmentGovernanceService.php`
- `apps/api/app/Modules/Communications/Services/CommunicationAttachmentService.php`
- `apps/api/app/Modules/Communications/Http/Requests/UpdateCommunicationAttachmentScanStatusRequest.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Api/V1/CommunicationAttachmentScanStatusController.php`
- `apps/api/app/Modules/Communications/Http/Controllers/Webhooks/PublicCommunicationAttachmentController.php`
- `apps/api/app/Modules/Communications/Services/Providers/TwilioMessagingAdapter.php`
- `apps/api/app/Modules/Communications/Services/Providers/SendGridEmailAdapter.php`
- `apps/api/app/Modules/Communications/Routes/api.php`
- `apps/api/contracts/openapi.communications.php`
- `apps/web/src/features/communications/components/client-communications-panel.tsx`
- `apps/api/app/Modules/Communications/Tests/Feature/CommunicationAttachmentGovernanceFeatureTest.php`
