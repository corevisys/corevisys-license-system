# CoreVisys — License Management System

> **Version:** v1.0.1

CoreVisys is a Laravel + Vue (Inertia.js) licensing, billing, and subscription platform for software vendors. It manages license issuance, offline verification, payment gateway integration, order processing, notifications, audit logging, and admin operations for a multi-product SaaS stack.

---

## Project Structure

```
app/
├── Console/Commands/        – Artisan CLI commands for license lifecycle & maintenance
├── Http/Controllers/Api/V1/ – REST API controllers (license, order, webhook, product, notification)
│   └── Admin/               – Admin-only endpoints (analytics, payment verify, license reset)
├── Jobs/                    – Queued jobs (renewal, expiry notification)
├── Mail/                    – Mailable classes for transactional emails
├── Models/                  – Eloquent models (see full list below)
├── Services/                – Business logic orchestration layer
└── Support/                 – Helpers (DomainNormalizer, OfflineLicenseVerification, OrderStatus)

config/                      – App, mail, logging, queue, and service provider config
database/migrations/         – 40+ schema migrations for licensing, payments, and operational defaults
resources/js/
├── Pages/                   – User-facing Inertia/Vue pages (Dashboard, Licenses, Orders, Store…)
│   ├── Admin/               – Admin panel pages (Dashboard, Licenses, Products, Orders, Settings…)
│   └── Public/              – Public marketing pages (Home, Pricing, Developers, Contact…)
routes/
├── api.php                  – API route definitions
├── auth.php                 – Authentication routes (Breeze)
├── console.php              – Scheduled command definitions
└── web.php                  – Web/Inertia page routes
tests/                       – Pest feature & unit test suite
```

---

## Models

| Model | Purpose |
|---|---|
| `License` | Core license record, state, fingerprint, features, billing |
| `LicenseActivation` | Per-activation records tied to a license |
| `LicenseReset` | Tracks fingerprint reset requests |
| `Order` | Purchase orders with billing-cycle tracking |
| `OrderItem` | Line items linked to orders and product prices |
| `Payment` | Payment records (Stripe, bKash, offline) |
| `Product` | Licensable software products |
| `ProductPrice` | Pricing tiers per product |
| `User` | Authenticated customer/admin account |
| `Team` | License team grouping |
| `AuditLog` | Immutable audit trail for sensitive operations |
| `ExchangeRate` | Currency exchange rate snapshots |
| `NotificationPreference` | Per-user notification opt-in settings |
| `ProcessedWebhook` | Idempotency records for webhook deduplication |
| `SystemSetting` | Runtime key-value configuration store |
| `TrialHistory` | Trial abuse prevention records |

---

## Services

| Service | Purpose |
|---|---|
| `LicenseService` | Core license activation, check, pulse, deactivate, revoke, feature entitlement |
| `LicenseStateMachine` | State transitions (active → expired → revoked → cancelled) |
| `BKashPaymentService` | bKash tokenized checkout and payment execution |
| `BkashRenewalCheckoutService` | bKash renewal link generation and customer billing flow |
| `StripePaymentService` | Stripe charge creation and webhook verification |
| `OrderFulfillmentService` | Post-payment order fulfilment and license provisioning |
| `CurrencyService` | Multi-currency conversion using stored exchange rates |
| `ReceiptStorageService` | Offline receipt upload, validation, retention pruning, and scanning |
| `AuditService` | Structured audit log writes for sensitive events |

---

## Artisan Commands

| Command | Description |
|---|---|
| `license:renew-subscriptions` | Process due subscription renewals (daily 00:00) |
| `license:notify-expiring` | Send expiry warning emails (daily 01:00) |
| `license:flag-stale` | Flag stale/missing-fingerprint licenses (daily 02:00) |
| `license:cleanup-expired` | Hard-delete expired licenses after grace period (monthly) |
| `license:generate-keys` | Generate RSA license signing key pairs |
| `license:migrate-legacy-keys` | Migrate plaintext legacy keys to hashed format |
| `license:repair-missing` | Repair licenses with missing or broken key records |
| `license:reset-lookup-hashes` | Rebuild lookup hashes after a salt rotation |
| `license:check-prerequisites` | Validate environment prerequisites before go-live |
| `receipts:prune` | Delete old receipt files past retention policy (daily) |
| `corevisys:install` | Interactive guided setup wizard for fresh installations |

---

## Scheduled Jobs (Cron)

The scheduler is driven by Laravel's built-in scheduler. A single cron entry is sufficient:

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

| Schedule | Command |
|---|---|
| Daily at 00:00 | `license:renew-subscriptions` |
| Daily at 01:00 | `license:notify-expiring` |
| Daily at 02:00 | `license:flag-stale` |
| Monthly | `license:cleanup-expired` |
| Daily | `receipts:prune` |

---

## API Overview

### Public Endpoints (no auth, throttled)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/products` | List all active products |
| `GET` | `/api/v1/products/{id}` | Get a single product |
| `POST` | `/api/v1/license/activate` | Activate a license key |
| `POST` | `/api/v1/license/check` | Check license validity |
| `POST` | `/api/v1/license/pulse` | Heartbeat ping for a license |
| `POST` | `/api/v1/license/deactivate` | Deactivate a license on a device |
| `GET` | `/api/v1/license/public-key` | Fetch RSA public key for offline verification |
| `POST` | `/api/v1/webhooks/{gateway}` | Receive payment gateway webhooks |

### Authenticated Endpoints (Sanctum token required)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/user` | Get current authenticated user |
| `POST` | `/api/v1/license/history` | Fetch license activation history |
| `POST` | `/api/v1/orders/create` | Create a new purchase order |
| `POST` | `/api/v1/orders/bkash/execute` | Execute a bKash checkout |
| `POST` | `/api/v1/orders/{id}/upload-receipt` | Upload an offline payment receipt |
| `GET` | `/api/v1/notifications/preferences` | Get notification preferences |
| `POST` | `/api/v1/notifications/preferences` | Update notification preferences |

### Admin Endpoints (Sanctum + admin middleware)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/admin/analytics` | Revenue, license, and order analytics |
| `POST` | `/api/v1/admin/payments/{id}/verify` | Manually verify an offline payment |
| `POST` | `/api/v1/admin/licenses/{id}/reset` | Reset a license fingerprint |

Responses for license validation endpoints use a signed payload wrapper when signing is configured.

---

## Frontend Pages

### User Panel
- `Dashboard` — license overview and account summary
- `Licenses` — active licenses list and management
- `LicenseConfig` — per-license configuration and feature flags
- `Orders` — purchase history
- `Store` — product catalog and checkout
- `Analytics` — usage analytics for the customer
- `Profile/Edit` — profile, password, and account deletion

### Admin Panel (`/admin`)
- `Dashboard` — platform KPIs
- `Licenses` — all licenses with filters and state controls
- `LicenseDetails` — detailed license view with activation history
- `Orders` — order management and receipt verification
- `Products` — product and pricing CRUD
- `Teams` — team management
- `Analytics` — revenue and license analytics
- `Settings` — system-wide runtime settings

### Public Pages
- `Home`, `Pricing`, `Developers`, `Contact`, `Privacy`, `Terms`, `Cookies`

---

## Local Development Setup

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```
2. **Install frontend dependencies:**
   ```bash
   npm install
   ```
3. **Copy environment settings:**
   ```bash
   cp .env.example .env
   ```
4. **Generate the application key:**
   ```bash
   php artisan key:generate
   ```
5. **Generate RSA license signing keys:**
   ```bash
   php artisan license:generate-keys
   ```
6. **Run migrations and seed defaults:**
   ```bash
   php artisan migrate --seed
   ```
7. **Start all app processes (server + queue + Vite):**
   ```bash
   composer dev
   ```
   Or individually:
   ```bash
   php artisan serve
   npm run dev
   php artisan queue:work database --tries=3 --backoff=60
   php artisan schedule:work
   ```

---

## Current Verification Baseline

As of 2026-09-14, the project is verified with the full Laravel/Pest test suite:

```
php artisan test
```
- Result: **90 passed, 281 assertions, 0 failed**

---

## Core Runtime Configuration

### License Signing & Offline Verification

Required keys in `.env`:

```env
LICENSE_SIGNING_PRIVATE_KEY=<base64-encoded RSA private key>
LICENSE_SIGNING_PUBLIC_KEY=<base64-encoded RSA public key>
LICENSE_SIGNING_PUBLIC_KEYS=<JSON map of key-id → base64 public key>
LICENSE_SIGNING_KEY_ID=<active key identifier>
LICENSE_SIGNING_ALGORITHM=RS256
```

Behavioral settings:

```env
FINGERPRINT_GRACE_MODE=true
FINGERPRINT_ENFORCEMENT_DEADLINE=2026-12-31
```

### Payment Gateways

The system supports:

- **Stripe** — tokenized card charges with webhook verification
- **bKash** — tokenized checkout with customer-authorized renewals
- **Offline payments** — receipt upload with manual admin verification

> **Important:** bKash subscription renewals require **customer action** for every billing cycle. The renewal worker emails a bKash payment link; the customer must open it and complete the payment (including PIN entry) in their bKash app. The worker never attempts an unattended charge. A renewal takes effect only after the server-verified payment callback succeeds.

Keep each gateway **disabled by default** until credentials are added.

### Mail & Receipt Storage

```env
MAIL_MAILER=log         # use smtp/ses in production
FILESYSTEM_DISK=local   # use s3 in production
```

---

## Production-Safe Defaults

The project intentionally defaults to local-friendly and optional-provider-safe settings:

- `QUEUE_CONNECTION` defaults to `database` — configure a proper worker in production.
- Gateway toggles default to `off` until credentials are configured.
- Mail defaults to `log` unless a real external provider is configured.
- Receipt storage defaults to `local` disk if no external object storage is configured.
- Optional external alerting is opt-in via the `alert` log channel.

**Do not commit real secrets.** Keep `.env` outside version control.

---

## Deployment Checklist

Before production go-live:

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a valid `APP_URL` with HTTPS.
2. Configure real SMTP or transactional mail credentials.
3. Run queue workers continuously (`php artisan queue:work`).
4. Add the Laravel scheduler to system cron (`schedule:run` every minute).
5. Monitor `failed_jobs` table and route critical alerts to the `alert` log channel.
6. Rotate license-signing keys with an overlap window before retiring old keys.
7. Validate webhook secrets for Stripe and all enabled payment providers.
8. Configure working outbound email — bKash renewal links are emailed per billing cycle.
9. Run `php artisan license:check-prerequisites` to validate the environment.

---

## Monitoring & Alerts

Critical failures are logged through Laravel's `alert` channel (opt-in, local-friendly by default). In production, forward this channel to a centralized monitoring provider or webhook-based alert platform.

The app already logs queue failures and emits critical alerts for job failures.

---

## Security Notes

- License keys are hashed with a **per-record salt** before storage; legacy plaintext keys are rejected unless explicitly migrated.
- Response payloads avoid exposing raw license keys after initial issuance.
- Fingerprint enforcement is mode-aware and uses a grace period before strict enforcement.
- Key rotation metadata and revoked keys are tracked for offline signature verification.
- Webhook idempotency is enforced via `ProcessedWebhook` deduplication records.
- Audit logs provide an immutable trail for all sensitive operations.

---

## License

CoreVisys is distributed under the **MIT License**.
