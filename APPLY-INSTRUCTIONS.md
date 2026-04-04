# Apply Instructions

1. unzip this package at a temporary location
2. copy the files into the matching repo-relative paths
3. remove `apps/api/stubs/phpstan-model-properties.stub` if it still exists in your branch
4. from `apps/api`, run:
   - `vendor/bin/phpstan analyse --memory-limit=1G`
   - `vendor/bin/phpunit`

This is a targeted follow-up pass, not a claim that every remaining PHPStan warning is already eliminated.
