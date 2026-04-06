# Rollback Plan

## Trigger conditions
Rollback is required when a release introduces:
- tenant-isolation failure
- broken boot/migration path
- callback trust failure on critical communications flows
- lifecycle authority regression
- immutable publication regression
- import commit corruption risk

## Rollback sequence
1. Halt traffic if necessary.
2. Stop queue workers to prevent continued mutation.
3. Restore the previous application version.
4. Roll back the latest migration batch only if the migration set is confirmed reversible and data-safe.
5. If migrations are not safely reversible, restore from backup and redeploy the last known-good release.
6. Restart workers and validate critical smoke paths.
7. Capture incident notes and affected correlation IDs.

## Decision rule
Prefer a code rollback without schema rollback when data safety would be jeopardized by reversing migrations blindly.
