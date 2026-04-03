# Environment Checklist

## Core application
- `APP_ENV`
- `APP_URL`
- `APP_KEY`
- `APP_DEBUG`

## Database/session/cache
- `DB_CONNECTION`
- `DB_DATABASE` or `DB_HOST` / `DB_PORT` / `DB_USERNAME` / `DB_PASSWORD`
- `DB_FOREIGN_KEYS`
- `CACHE_STORE`
- `SESSION_DRIVER`
- `SESSION_DOMAIN`
- `SANCTUM_STATEFUL_DOMAINS`

## Twilio
- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_MESSAGING_SERVICE_SID` or `TWILIO_FROM_NUMBER`
- `TWILIO_VOICE_FROM_NUMBER`
- `TWILIO_WEBHOOK_ENFORCE_SIGNATURE`
- `TWILIO_WEBHOOK_BASE_URL` when SSL termination or proxy rewriting would otherwise break callback URL validation

## SendGrid
- `SENDGRID_API_KEY`
- `SENDGRID_FROM_EMAIL`
- `SENDGRID_FROM_NAME`
- `SENDGRID_WEBHOOK_PUBLIC_KEY` when signed event / parse webhooks are enabled
- `SENDGRID_WEBHOOK_OAUTH_BEARER_TOKEN` when OAuth validation is used
- `SENDGRID_WEBHOOK_ENFORCE_SIGNATURE`

## Operational notes
- production should not enable webhook signature enforcement until the correct secrets/public keys are present
- if SendGrid signed webhooks are enabled, keep the public key in the exact PEM form supplied by SendGrid or provide it as a base64 body that can be normalized into PEM
- if reverse proxies terminate SSL, set `TWILIO_WEBHOOK_BASE_URL` to the externally visible webhook origin so signature validation uses the public callback URL
