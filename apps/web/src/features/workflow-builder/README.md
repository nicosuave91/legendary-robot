# workflow-builder

This feature renders tenant-scoped workflow definitions, immutable version history, and append-only runtime evidence.

## Frontend responsibilities

- present workflow list/detail surfaces from the generated API client
- allow draft editing of trigger and step JSON
- keep published versions read-only
- expose workflow run logs as operator-visible execution evidence

## Drift controls

Workflow UI changes must stay aligned with:
- backend workflow runtime behavior
- published contract shapes
- release documentation and operator guidance

The frontend must not imply that a workflow action executed unless the runtime evidence returned by the API confirms it.
