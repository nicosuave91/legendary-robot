# Repository Hygiene and Documentation Governance

This document defines what is allowed at the repository root, where active documentation belongs, and how historical sprint artifacts must be handled.

## Purpose

The platform can no longer rely on sprint-era root documents as a living planning surface. The repository root must stay readable and authoritative. Anything historical, temporary, or release-pass specific belongs in `docs/` or `docs/archive/`.

## Allowed root files

The repository root should contain only:

- source-level workspace config and package manifests
- the authoritative `README.md`
- intentionally current top-level config files
- helper scripts only when they are repo-wide and operationally useful

The following should **not** remain at root after this closure pass:

- `current-platform-status.md`
- `verification-matrix.md`
- `IMPLEMENTATION-NOTES.md`
- sprint handoff files
- changed-file manifests
- sprint release notes that duplicate current docs

## Canonical documentation locations

- `docs/architecture/` — platform thesis, ADR-linked architecture rules, development blueprint
- `docs/release/` — current release posture, readiness checklists, critical journeys, closure notes
- `docs/testing/` — verification matrix, testing policy, release-evidence expectations
- `docs/operations/` — repository governance, deployment, rollback, queues, webhook setup
- `docs/archive/` — historical sprint material and superseded closure notes

## Archive rules

A document is archive-only if any of the following is true:

- it describes a previous sprint rather than the current platform
- it is a changed-files manifest
- it is an implementation handoff from an earlier pass
- it duplicates a newer authoritative document

Archived files should be moved, not silently discarded, unless the information is fully duplicated by Git history and a current archive index.

## Anti-drift rules

1. Root documentation other than `README.md` requires explicit justification.
2. New sprint or handoff artifacts must be created under `docs/archive/` or `docs/release/`, not at root.
3. Current platform truth must live under `docs/release/` and `docs/testing/`.
4. CI should fail if prohibited root docs reappear.
5. README links must point to the current canonical docs, not historical sprint files.

## Review checklist for documentation moves

When moving a root doc into `docs/` or `docs/archive/`, verify:

- the new location is linked from the README or archive index where appropriate
- any stale root references are removed
- the file’s new purpose is clear
- a delete/remove list is included in the closure bundle

## Immediate relocation set

This closure pass relocates the following root documents:

- `current-platform-status.md` → `docs/release/current-platform-status.md`
- `verification-matrix.md` → `docs/testing/verification-matrix.md`
- `IMPLEMENTATION-NOTES.md` → `docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md`

The root originals should be deleted after the replacements are applied.
