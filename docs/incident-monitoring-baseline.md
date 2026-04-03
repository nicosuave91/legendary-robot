# Incident and Monitoring Baseline

## Monitor at minimum
- auth/sign-in failures
- migration failures
- queue backlog / failed jobs
- import validation and commit failures
- Twilio callback verification failures
- SendGrid callback verification failures
- audit query errors
- unexpected 5xx spikes on critical API routes

## Required operational context in logs
- correlation ID
- tenant ID
- actor ID when available
- provider reference for external callbacks
- import ID / workflow run ID / application ID when relevant

## Initial incident response posture
1. identify tenant and correlation IDs
2. classify whether the issue is data integrity, tenant isolation, provider callback trust, or operational degradation
3. stop unsafe writes if business authority is at risk
4. preserve append-only evidence and raw callback payload references
5. apply rollback plan when the issue crosses release-blocker thresholds
