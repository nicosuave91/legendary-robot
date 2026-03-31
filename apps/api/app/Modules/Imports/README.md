# Imports

Sprint 9 implements the imports ledger and governed staging pipeline.

Guardrails:
- uploaded files stay in governed storage
- parsed rows remain in staging until validation succeeds
- commit is explicit, policy-protected, and auditable
- imports do not bypass the Clients domain service path
