# ADR-002: Use a Local Design-System Layer on Top of shadcn-style Primitives

- Status: Accepted
- Date: 2026-03-29
- Decision owners: Engineering

## Context

The product must prevent design drift while retaining code ownership over primitives.

## Decision

Adopt local wrapper components under `src/components/ui` and prohibit feature modules from importing raw primitive implementations directly.

## Consequences

- Product team owns UI contracts.
- Easier token enforcement and future theming.
- Slightly more wrapper code in the foundation sprint.

## Guardrails

- All shared UI imports go through `src/components/ui`.
- Tokens are the only approved source for spacing, radius, semantic colors, and typography roles.
