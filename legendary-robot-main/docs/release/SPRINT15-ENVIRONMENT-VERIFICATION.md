# Sprint 15 Environment Verification Notes

## Communications providers
Verify and document the current state of the following:
- Twilio account SID/auth token presence
- Twilio messaging/call sender configuration
- Twilio webhook base URL and signature enforcement setting
- SendGrid API key and sender configuration
- SendGrid webhook secret/public key/OAuth bearer configuration
- SendGrid signature enforcement setting

## Local/staging runtime assumptions
Confirm:
- API base URL used by the web app
- stateful Sanctum domains
- session domain
- CORS allowed origins
- queue worker command and transport
- seeded/demo credentials in the target environment

## Honest release note
If a provider callback or queue-backed behavior cannot be fully proven in the current environment, record:
- what was verified
- what was not verified
- what exact environment dependency prevented full verification
- whether the gap is release-blocking
