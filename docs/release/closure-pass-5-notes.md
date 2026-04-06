# Closure pass 5 notes

This pass closes the current workflow UI/runtime mismatch and strengthens the contract guardrails around it.

## Included in this pass

- Added `apps/api/contracts/openapi.workflow_closure.php` so workflow detail can formally describe `draftValidation`.
- Updated `apps/api/contracts/openapi.php` to merge the workflow closure fragment and refresh contract metadata.
- Added `WorkflowDetailEnvelopeWithDraftValidation` to the web API wrapper so the workflow detail page has a typed bridge while generated artifacts catch up.
- Added `scripts/verify-workflow-contract-closure.mjs`.
- Updated `contract-ci.yml` to run the workflow contract closure verification after contract publication and client generation.
- Expanded the Playwright mock layer and release-critical journeys to cover:
  - workflow publish blockers
  - workflow run evidence

## Required follow-up after applying these replacements

Run the normal contract flow from the repo root:

```bash
php apps/api/scripts/publish-openapi.php
node scripts/generate-web-client.mjs
```

Then confirm:

```bash
node scripts/verify-workflow-contract-closure.mjs
npm --workspace apps/web run test:e2e -- --grep "workflow detail surfaces publish blockers"
```

## Why this pass matters

The current repository already contains a workflow detail page that expects a draft-validation-aware contract surface. This pass makes that expectation explicit and guarded so future changes cannot silently regress the workflow publish experience.
