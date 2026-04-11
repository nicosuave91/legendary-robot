# ADR-003: Enforce Generated TypeScript API Clients

- Status: Accepted
- Date: 2026-03-29
- Decision owners: Engineering

## Context

API contract drift is a core architectural risk.

## Decision

Publish an OpenAPI artifact from the backend and generate a TypeScript client into the frontend. Forbid handwritten fetch logic outside the approved HTTP adapter and generated client boundary.

## Consequences

- Frontend contracts remain typed and versioned.
- API changes require regeneration and review.
- Contract publication becomes a merge-blocking workflow.

## Guardrails

- `packages/contracts/openapi.json` is canonical.
- `apps/web/src/lib/api/generated/client.ts` is generated.
- CI reruns publication and generation, then fails on diff.
