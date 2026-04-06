# PHPStan Follow-up Patch

This follow-up patch is based on the current PHPStan run and fixes the two biggest issues in the prior pass:

1. it stops relying on the repo-level stub file that was generating its own analysis failures
2. it moves the highest-value property metadata into the real Eloquent model classes and adds explicit typed collections in the top failing service/query files

## Targeted files in this pass
- core client/application/calendar/notification/rule/workflow models
- application, client workspace, calendar event, notification, rule catalog, workflow catalog, and workflow run query/serializer services
- phpstan.neon cleanup

## Apply next
From `apps/api`:

```bash
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/phpunit
```

If the old stub file still exists in your branch, it is safe to remove it after this patch because `phpstan.neon` no longer references it.
