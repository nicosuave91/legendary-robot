# Sprint 15 Handoff

## Scope
Sprint 15 adds release-verification infrastructure and signoff artifacts for the current CRM build.

## Included patch areas
1. `apps/web/e2e/release-critical-journeys.spec.ts`
   - Playwright smoke journeys for sign-in, protected shell, client workspace, and operational surfaces

2. `apps/api/tests/Feature/Release/ReleaseReadinessSmokeTest.php`
   - backend smoke coverage for authenticated release-critical API surfaces

3. `.github/workflows/release-verification.yml`
   - backend validation, frontend validation, and Playwright smoke workflow

4. `docs/release/*`
   - release-readiness checklist
   - critical journey matrix
   - environment verification notes

## Honest caveat
This package is implementation-ready, but I could not directly write the changes into the connected GitHub repository from this environment. The changed files are included here for direct application and execution in the repo workspace.
