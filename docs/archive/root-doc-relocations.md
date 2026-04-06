# Root Documentation Relocations

This manifest records the first concrete repository cleanup move set for non-source root documents.

## Relocated in this pass

- `current-platform-status.md` → `docs/release/current-platform-status.md`
- `verification-matrix.md` → `docs/testing/verification-matrix.md`
- `IMPLEMENTATION-NOTES.md` → `docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md`

## Delete after apply

Remove these root files once the replacement docs are copied into place:

- `current-platform-status.md`
- `verification-matrix.md`
- `IMPLEMENTATION-NOTES.md`

## Still pending archive cleanup

Historical sprint files already identified in earlier closure manifests should be moved under `docs/archive/sprints/` and removed from the repository root once archived.
