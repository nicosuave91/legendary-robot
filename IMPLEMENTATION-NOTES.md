# Closure Bundle Implementation Notes

This bundle begins the closure phase by addressing high-value runtime and governance issues while also replacing the repository’s authoritative documentation surface.

## Included runtime/code replacements

- `apps/api/app/Modules/TenantGovernance/Services/IndustryConfigurationService.php`
  - adds active published version lookup
  - adds explicit published version lookup
  - preserves existing version-creation behavior

- `apps/api/app/Modules/Audit/Services/AuditSearchService.php`
  - resolves actor display names where possible
  - labels null-actor entries as `System`

- `apps/web/src/components/shell/app-sidebar.tsx`
  - aligns Calendar and Communications navigation with route permission requirements
  - updates the badge label from release-candidate wording to closure-phase wording

- `apps/api/tests/Feature/TenantGovernance/IndustryConfigurationRuntimeTest.php`
- `apps/api/tests/Feature/Audit/AuditSearchPresentationTest.php`

## Included documentation replacements

- `README.md`
- architecture thesis/dissertation
- ongoing development blueprint
- release status document
- verification matrix
- repository hygiene and archive policy
- archive/delete manifest
- tenant governance module README refresh

## Not yet completed in this bundle

The following major closure tracks still require follow-on implementation work:

- real workflow action-step execution (`send_sms`, `send_email`, `create_client_note`)
- real wait scheduling semantics in Workflow Builder
- live end-to-end browser coverage against a running backend for all critical journeys
- broad repository archival moves for historical sprint documents
- deeper design-system conformity safeguards

Those remain active closure requirements and should be delivered next.
