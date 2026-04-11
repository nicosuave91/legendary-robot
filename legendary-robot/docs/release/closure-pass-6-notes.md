# Closure Pass 6 Notes

This pass focuses on repository closure rather than new runtime behavior.

## What this pass does

- relocates active-but-misplaced root documentation into canonical `docs/` paths
- archives the original closure implementation notes under `docs/archive/closure/`
- adds a script to perform the root-doc moves safely
- adds a CI/root-governance check that fails when prohibited root docs or sprint artifacts reappear
- updates contract CI to enforce repository root hygiene alongside contract integrity

## Why this matters

The root README already points to canonical docs under `docs/release/` and `docs/testing`, but the repository still contains older root copies of those materials. That creates documentation ambiguity and invites drift. This pass removes that ambiguity.

## After apply

1. copy the replacement docs into place
2. run `node scripts/archive-root-governance-docs.mjs` once if you want the move automated in the repo working tree
3. delete the original root files listed in `docs/archive/root-doc-relocations.md`
4. verify CI passes with the new root-governance check

## Remaining best follow-on work

- physically archive the broader set of root sprint artifacts under `docs/archive/sprints/`
- tighten live release verification for a real browser-driven workflow publish/run path against a running backend
- regenerate `packages/contracts/openapi.json` and `apps/web/src/lib/api/generated/client.ts` after the workflow closure contract changes
