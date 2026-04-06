# Documentation Archive Policy

This directory holds historical material that is useful for traceability but must not compete with the current platform documentation.

## What belongs here

- sprint handoff notes
- changed-file manifests
- release-prep notes tied to a specific sprint
- historical implementation-status documents
- one-off closure bundle notes
- archived root-level governance or status documents that have been replaced by canonical docs under `docs/release/`, `docs/testing/`, or `docs/operations/`

## What does not belong here

- current release guidance
- current operations runbooks
- current architecture requirements
- current module specifications

## Archive rules

- archived files keep their original names unless a rename is required to prevent collisions
- archived files should be moved, not copied, when the repo migration is performed
- no archived file should be linked from the root README as an authoritative source
- historical sprint docs should be grouped under `docs/archive/sprints/`
