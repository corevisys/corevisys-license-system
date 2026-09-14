# CoreVisys

CoreVisys is a Laravel-based licensing, billing, and subscription platform for software vendors. It manages license issuance, offline verification, payment gateways, order processing, notifications, and admin operations for a multi-product SaaS stack.

## Project structure

- `app/Http/Controllers/Api/V1` – API endpoints for licenses, orders, webhooks, and admin analytics
- `app/Services` – orchestration for licenses, billing, fulfillment, and offline signing
- `app/Models` – business data models including License, Order, Payment, User, and SystemSetting
- `config/` – environment config for app, mail, logging, queue, and service providers
- `database/migrations` – schema evolution for licensing, payments, and operational defaults
- `resources/js` – admin dashboard and Inertia pages
- `routes/` – web and API route registration
- `tests/` – Pest feature and unit coverage for regressions

## Local development setup

1. Install PHP dependencies:
   - `composer install`
2. Install frontend dependencies:
   - `npm install`
3. Copy environment settings:
   - `cp .env.example .env` (or create a local `.env` from your environment template)
4. Generate the app key:
   - `php artisan key:generate`
5. Run migrations and seed default settings:
   - `php artisan migrate --seed`
6. Start the app processes:
   - `php artisan serve`
   - `npm run dev`
7. For queue workers and scheduled jobs in a local or staging environment:
   - `php artisan queue:work database --tries=3 --backoff=60`
   - `php artisan schedule:work`

## Current verification baseline

As of 2026-09-14, the project is verified with the full Laravel suite:

- `php artisan test`
- Result: 90 passed, 281 assertions, 0 failed

## Production-safe defaults

The project intentionally defaults to local-friendly and optional-provider-safe settings:

- `QUEUE_CONNECTION` defaults to a database queue and should be explicitly configured for production workers.
- Gateway toggles default to off until actual credentials are configured.
- Mail defaults to local-safe behavior unless a real external provider is configured.
- Receipt storage defaults to local disk if no external object storage is configured.
- Optional external alerting is opt-in via the `alert` log channel and webhook/Slack integrations.

Do not commit real secrets. Keep `.env` outside version control and place keys in your deployment secret manager.

## Core runtime configuration

### License signing and offline verification

Required keys in `.env`:

- `LICENSE_SIGNING_PRIVATE_KEY`
- `LICENSE_SIGNING_PUBLIC_KEY`
- `LICENSE_SIGNING_PUBLIC_KEYS`
- `LICENSE_SIGNING_KEY_ID`
- `LICENSE_SIGNING_ALGORITHM`

Behavioral settings:

- `FINGERPRINT_GRACE_MODE=true` enables the temporary grace window for missing fingerprints.
- `FINGERPRINT_ENFORCEMENT_DEADLINE` sets the deadline after which missing fingerprints are treated as non-compliant when a bound fingerprint exists.

### Payment gateways

The system supports:

- Stripe
- bKash tokenized checkout
- additional optional providers when enabled in configuration

Keep each gateway disabled by default until corresponding credentials are added. This prevents a production outage caused by hard dependencies on paid providers.

### Mail and receipt storage

Recommended defaults:

- `MAIL_MAILER=log` or another local-safe driver while validating the environment
- `FILESYSTEM_DISK=local` unless object storage is explicitly configured

The app exposes receipt handling through a dedicated storage service with validation, retention pruning, and scanning hooks. It is safe to start with local storage and scale to a managed object store later.

## Deployment checklist

Before production go-live:

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a valid `APP_URL` with HTTPS.
2. Configure real SMTP or transactional mail credentials.
3. Run a queue worker in the background for database-backed jobs.
4. Configure scheduler cron entries for renewal, expiry alerts, and cleanup jobs.
5. Ensure `failed_jobs` is monitored and that critical alerts are routed to the alert log or a monitoring channel.
6. Rotate license-signing keys with a proper overlap window before key retirement.
7. Validate webhook secrets for Stripe and any payment provider you enable.

## API overview

Primary endpoints:

- `POST /api/v1/license/activate`
- `POST /api/v1/license/check`
- `POST /api/v1/license/pulse`
- `GET /api/v1/license/public-key`
- `POST /api/v1/license/history`
- `POST /api/v1/orders/create`
- `POST /api/v1/orders/bkash/execute`
- `POST /api/v1/webhooks/stripe`

Responses use a signed payload wrapper for license validation endpoints when signing is configured.

## Operational jobs

The system includes scheduled and queued tasks for:

- renewals
- expiry notifications
- cleanup of expired licenses after the grace period
- webhook processing
- failed-queue alerting

Recommended cron items:

- `php artisan schedule:run` via a system cron entry
- queue workers running continuously in the background

## Monitoring and alerts

Critical failures should be logged through Laravel's `alert` channel. This channel is deliberately opt-in and local-friendly by default. In production, forward the channel to a centralized monitoring provider or a webhook-based alert platform.

The app already logs queue failures and emits critical alerts for job failures, making it easier to detect outages without hard-wiring a third-party provider into local development defaults.

## Security notes

- License keys are hashed with a per-record salt before storage.
- Legacy plaintext keys are intentionally rejected unless explicitly migrated.
- Response payloads avoid exposing raw license keys after initial issuance.
- Fingerprint enforcement is mode-aware and uses a temporary grace period before final enforcement becomes strict.
- Key rotation metadata and revoked keys are tracked for offline signature verification.

## License

CoreVisys is distributed under the MIT License.
