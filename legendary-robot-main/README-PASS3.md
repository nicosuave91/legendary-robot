\
# PHPStan Pass 3

This package targets the next dense cluster from the latest PHPStan run:
- application/task history model fields
- audit/disposition/import model fields
- communications model fields
- onboarding/tenant-governance relation fields
- controller `ApiResponse::success()` payload shape mismatches
- draft-service `first()` / `fresh()` return typing
- notification dismissal sorting typing

## Apply
1. copy these files into the repo
2. from `apps/api`, run:
   - `vendor/bin/phpstan analyse --memory-limit=1G`
   - `vendor/bin/phpunit`

This is another reduction pass, not a claim that PHPStan will be fully clean after this package.
