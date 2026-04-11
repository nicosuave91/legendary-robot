# Closure Pass 7 Notes

This pass focuses on repository hygiene and archive enforcement against the current linked GitHub repository state.

## What it adds

- canonical replacements for the root status/governance docs:
  - `docs/release/current-platform-status.md`
  - `docs/testing/verification-matrix.md`
  - `docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md`
- archive policy docs under `docs/archive/`
- a concrete relocation/delete manifest for root artifacts
- a move script for root governance and sprint artifacts
- a CI safeguard that blocks:
  - root status docs
  - root `SPRINT*` artifacts
  - root `merge-hotfix-*` snapshot directories

## Files intended to be removed after applying this pass

- `current-platform-status.md`
- `verification-matrix.md`
- `IMPLEMENTATION-NOTES.md`
- `merge-hotfix-2/` after verification
- any remaining root file matching `SPRINT*.md`, `SPRINT*.txt`, or `SPRINT*.zip`

## Why this pass matters

The current repository still contains non-source status material at the root and at least one root snapshot directory. Those artifacts increase drift risk because they create competing sources of truth and leave stale code snapshots beside the canonical source tree.

This pass makes the root cleaner and turns repo hygiene into an enforced CI rule instead of a one-time cleanup note.
