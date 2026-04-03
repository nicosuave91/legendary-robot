# Notifications

Sprint 9 implements a persistent notification center with per-user read and dismissal lineage.

Guardrails:
- notification source rows remain durable
- read and dismissal state is stored separately per user
- shell toasts do not erase notification truth
