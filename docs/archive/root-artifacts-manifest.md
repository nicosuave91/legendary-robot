# Root Artifact Relocation and Deletion Manifest

This manifest describes the next repository-hygiene moves that should be applied to the connected GitHub repository.

## Move into canonical documentation paths

- `current-platform-status.md` → `docs/release/current-platform-status.md`
- `verification-matrix.md` → `docs/testing/verification-matrix.md`
- `IMPLEMENTATION-NOTES.md` → `docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md`

## Move into sprint archive

Move any root-level file matching these patterns into `docs/archive/sprints/`:

- `SPRINT*.md`
- `SPRINT*.txt`
- `SPRINT*.zip`

These are historical artifacts and should not remain at the repository root.

## Delete from repository after verification

- `merge-hotfix-2/`

This directory is a code snapshot artifact and should not remain in the canonical source tree once its contents have been verified as redundant.

## Post-migration state

After the migration:
- the root README remains the only root-level documentation entrypoint
- active release and testing docs live under `docs/`
- historical sprint material lives under `docs/archive/sprints/`
- root snapshot directories are removed
