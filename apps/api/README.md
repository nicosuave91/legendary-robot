# API Application Layer

This directory now contains the Sprint 2A / 2B continuation of the Laravel-side CRM foundation.

## Implemented now
- executable Laravel bootstrap surfaces (`artisan`, `bootstrap/app.php`, `public/index.php`)
- module provider registration for Shared, Audit, Tenant Governance, Identity & Access, and Onboarding
- versioned auth APIs with real session-oriented service flow scaffolding
- onboarding domain model, onboarding APIs, and auditable state transitions
- tenant account provisioning path that defaults admin-created users to required onboarding
- OpenAPI contract expansion and generated web client sync

## Still intentionally narrow
- deeper tenant governance UI
- homepage analytics/business widgets
- clients, calendar, communications, rules, workflows, imports, and notification-center domains

The Sprint 2 implementation preserves the original module boundaries, shared contract pipeline, and anti-drift design mandate while closing the Phase 2 identity/onboarding gap.
