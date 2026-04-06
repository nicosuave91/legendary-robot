# PHPStan Fix Patch

This package focuses on the highest-value static-analysis cleanup visible in the current repo and CI log.

## Included fixes
- adds Twilio SDK requirement to match the webhook verifier runtime code
- registers a repo-level PHPStan stub file for Eloquent model properties and relation-backed dynamic attributes
- fixes the `ClientService` closure so `$importSourceId` is available inside the transaction
- makes `ExecuteWorkflowRunStepJob` backward-compatible with both 2-arg and 3-arg dispatch signatures
- adds a safe `class_exists` guard around Twilio request validation
- corrects the `list<string>` PHPDoc type for the fillable/hidden properties PHPStan flagged directly

## Recommended next commands
From `apps/api`:

```bash
composer update twilio/sdk
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/phpunit
```

If PHPStan still reports remaining relation/generic callback issues after these changes, the next pass should target:
- `ApplicationQueryService`
- `EventQueryService`
- `ClientWorkspaceService`
- `CommunicationTimelineService`
- workflow/rules query serializers
