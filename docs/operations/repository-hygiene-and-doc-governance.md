# Repository Hygiene and Documentation Governance

This document defines how Snowball keeps source, release evidence, and documentation from drifting apart.

## Authoritative documentation locations

- `README.md` — entrypoint and setup
- `docs/release/current-platform-status.md` — current thesis, closure standard, and release status
- `docs/testing/verification-matrix.md` — ongoing delivery blueprint and verification policy
- `docs/release-readiness-checklist.md` — release signoff checklist
- `docs/known-issues-and-accepted-risks.md` — sanctioned residual-risk register
- `docs/archive/` — historical material only

## Root-level repository policy

The repository root should contain:

- source and package directories
- configuration files
- scripts
- the current root README
- other clearly current, durable project-level files

The repository root should not contain:

- sprint handoff notes
- changed-file manifests
- temporary implementation notes
- status snapshots replaced by canonical docs
- code snapshot directories like `merge-hotfix-*`

## Required review checks

Any PR that introduces or changes product behavior should confirm:

- docs were updated or no doc change was necessary
- contract changes were published and generated artifacts reconciled
- root-level file additions do not introduce status or sprint drift
- archived content was not reintroduced as active documentation

## Migration targets

These historical root docs are now expected to live elsewhere:

- `current-platform-status.md` → `docs/release/current-platform-status.md`
- `verification-matrix.md` → `docs/testing/verification-matrix.md`
- `IMPLEMENTATION-NOTES.md` → `docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md`

## Code snapshot directories

Root directories like `merge-hotfix-2/` are treated as repository contamination, not active source. They should be deleted after confirmation that their content is already represented by Git history or by the canonical source tree.
