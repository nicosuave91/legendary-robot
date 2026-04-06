# ADR-001: Use a Laravel Modular Monolith

- Status: Accepted
- Date: 2026-03-29
- Decision owners: Engineering

## Context

The CRM requires tenant-safe transactional consistency across identity, onboarding, rules, workflows, audit trails, and future communications.

## Decision

Use Laravel as a modular monolith with bounded modules under `app/Modules`, explicit public interfaces, and no direct cross-module table writes.

## Consequences

- Strong transactional consistency.
- Lower operational complexity than microservices for the current phase.
- Requires discipline around module boundaries.

## Guardrails

- Controllers stay thin.
- Cross-module collaboration happens through services, DTOs, and events.
- Tenant context must be available in request and job execution paths.
