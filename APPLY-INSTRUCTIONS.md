# Apply Instructions

1. unzip this package at a temporary location
2. copy the files into the matching repo-relative paths
3. from `apps/api`, run:
   - `composer update twilio/sdk`
   - `vendor/bin/phpstan analyse --memory-limit=1G`
   - `vendor/bin/phpunit`
4. commit the applied changes into your current branch

This patch is intended as a high-impact PHPStan reduction pass plus fixes for the concrete defects visible in the CI output.
