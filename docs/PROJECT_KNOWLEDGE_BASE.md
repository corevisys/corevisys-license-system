# CoreVisys Project Knowledge Base

**Document status:** Canonical discovery baseline
**Last audited:** 2026-09-13
**Scope:** Laravel application, license platform, customer dashboard, admin workflows, payments, jobs, operations, tests, and documentation

## 1. Executive Summary

CoreVisys is a Laravel 12 application that sells and manages software licenses. It provides a Vue/Inertia web dashboard, a public versioned license API, authenticated checkout, Stripe and bKash payment flows, manual/offline receipt approval, license activation and binding, subscription/trial concepts, admin controls, audit logging, and background commands/jobs.

The product is **feature-complete enough for continued staging work but not production-ready**. The most important blockers are exposed development environment state, an inconsistent offline-signature trust model, plaintext license-key persistence, a renewal route defect, incomplete payment/subscription failure handling, and missing production operations around queues, scheduler, backups, storage, and monitoring.

The test suite provides meaningful coverage, but the current run is not green: **64 tests passed and 3 failed, with 184 assertions**. The failing bKash tests require a public HTTPS application URL; this is a test configuration issue, but it still means CI must explicitly configure that prerequisite or mock the callback URL.

## 2. Technology and Entry Points

| Area | Current implementation | Evidence |
|---|---|---|
| Backend | Laravel 12, PHP 8.2+, Eloquent, Blade/Inertia controllers | `composer.json`, `app/` |
| Frontend | Vue 3, Inertia 2, Vite 7, Tailwind 4, Axios | `package.json`, `resources/js/`, `vite.config.js` |
| Auth | Session auth for web; Sanctum for API tokens | `routes/auth.php`, `routes/api.php`, `config/sanctum.php` |
| Database | MySQL in local `.env`; SQLite in PHPUnit | `.env`, `phpunit.xml`, `database/migrations/` |
| Payments | Stripe Checkout/webhooks, bKash tokenized checkout, manual receipt upload | `StripePaymentService.php`, `BKashPaymentService.php`, `WebhookController.php` |
| Queue/cache/session | Database-backed by local defaults; sync/array in tests | `.env`, `config/queue.php`, `phpunit.xml` |
| Scheduled work | Laravel scheduler commands run daily/monthly when an external scheduler invokes them | `routes/console.php` |
| License signing | HMAC activation field plus RSA/OpenSSL response envelope | `LicenseService.php`, `LicenseController.php` |

## 3. Product Capabilities

### 3.1 Public license API

Base path: `/api/v1`.

| Endpoint | Purpose | Side effects | Current status |
|---|---|---|---|
| `GET /products` | List products | None | Implemented |
| `GET /products/{id}` | Show product | None | Implemented |
| `POST /license/activate` | Validate, bind, and activate a license | Activation row, binding, last-check update | Implemented; security hardening required |
| `POST /license/check` | Read-only validity check | None intended | Implemented; fingerprint is not checked |
| `POST /license/pulse` | Heartbeat | Updates `last_check_at` | Implemented |
| `POST /license/history` | Return activation history | None | Implemented; sensitive data exposure risk |
| `POST /webhooks/{gateway}` | Payment webhook intake | Fulfillment and processed-event row | Stripe implemented; failure events mostly log only |

`CheckClientVersion` provides an API kill switch and minimum-version enforcement through `SystemSetting`. `activate` and `pulse` are allowed without the version header for backward compatibility; other versioned API calls can require `X-API-Version`.

### 3.2 Customer workflows

- Registration, login, logout, email verification, password reset, password confirmation, password update, and profile deletion.
- Authenticated product purchase and payment creation.
- Stripe Checkout redirect and success/cancel routes.
- bKash create/execute/callback flow.
- Manual/offline receipt upload and admin approval.
- License list, configuration payload, renewal, upgrade, invoice view, and license history.
- Notification preference storage.
- Team assignment support exists in the data model and tests.

Evidence: `routes/web.php`, `routes/api.php`, auth controllers, `OrderController.php`, and feature tests.

### 3.3 Admin workflows

- Admin dashboard and analytics.
- Order review and verification.
- Product and price management.
- System settings management.
- License status management, reset/binding reset, detail/history views.
- Payment verification.
- Team and customer/license administration.
- Audit log entries for important license/payment/reset actions.

Web admin routes use `can:admin`. API admin routes currently rely on `auth:sanctum` plus controller checks and should be normalized to explicit middleware/policies.

## 4. License Domain Rules

### States

The state machine allows:

```text
inactive -> active | suspended
active -> expired | suspended
expired -> active | suspended
suspended -> active | expired
```

There is no first-class `revoked` database state in the visible migration even though service code checks for `revoked`; status values must be reconciled before production.

### Activation and binding

- A license is resolved by plaintext key first, then unsalted hash, then a salted scan.
- First activation binds domain, IP, and optional fingerprint.
- Activation limits count successful distinct domains.
- Existing bindings can activate again.
- Fingerprint mismatch is rejected only when the client supplies a fingerprint; omission is intentionally backward-compatible and therefore a bypass of strict fingerprint enforcement.
- Trial abuse checks email, IP, and optionally fingerprint history, but history is not linked to a user or license.
- Grace-period expiry is supported.
- Reset clears binding data, marks previous successful activations failed, restores the limit on the next activation, and writes an audit record.

### Security decision required

The current service writes `license_key` in plaintext and exposes the raw key to the immediate fulfillment response/UI. The migration history includes a security upgrade but a later migration reintroduced the column. Choose one supported issuance/recovery model, remove the plaintext lookup path, and migrate existing records before launch.

## 5. Payment and Fulfillment Rules

- Stripe Checkout sessions are created by `StripePaymentService`.
- Stripe webhooks verify the `Stripe-Signature` header and deduplicate by event ID in `processed_webhooks`.
- `checkout.session.completed` calls `OrderFulfillmentService`.
- bKash requires configured credentials and rejects non-public/non-HTTPS callback URLs.
- Currency conversion stores exchange-rate/base-amount information.
- Manual/offline payments use receipt uploads and admin approval.
- Fulfillment is intended to be idempotent, but the order is marked completed before license generation. A later license failure can leave payment/order state inconsistent.
- Stripe invoice failure and subscription deletion events currently log rather than update payment/license state.
- Subscription renewal currently has a scheduled path, but the payment success path is not a real provider charge/reconciliation flow.

## 6. Background Work and Operations

Scheduled commands:

| Schedule | Command | Observed behavior |
|---|---|---|
| Daily 00:00 | `license:renew-subscriptions` | Dispatches renewal jobs; provider billing is incomplete |
| Daily 01:00 | `license:notify-expiring` | Dispatches notification jobs |
| Monthly | `license:cleanup-expired` | Audits old licenses but does not archive/delete them |

`SendExpiryNotification` logs intended email/SMS/push delivery; it does not send through mail, SMS, or push providers. Database queues therefore need an always-on worker, failed-job handling, retry policy, and alerting. No supervisor/container/cron deployment configuration is present in the repository.

## 7. Data and Configuration Inventory

Core tables include users, products, product prices, orders, order items, payments, licenses, activation history, license resets, audit logs, teams, notification preferences, exchange rates, trial history, processed webhooks, system settings, sessions, cache, and jobs.

Important environment/configuration concerns:

- The current `.env` is local development state with `APP_ENV=local`, `APP_DEBUG=true`, local HTTP URLs, database queue/cache/session, local filesystem, and a populated application/signing key. Treat all present credentials/keys as compromised if this file is shared or committed.
- Gateway and signing settings need an explicit secret-management policy and rotation procedure.
- Mail defaults to the log driver, not a delivery provider.
- Receipts default to local disk; production needs durable private object storage, access control, retention, and malware/content validation.
- Logging defaults to local file output at debug level; production needs centralized logs and alerting.

## 8. Test Baseline

Command run on 2026-09-13:

```text
php artisan test
64 passed, 3 failed, 184 assertions
```

The three failures are in `BKashPaymentTest` and arise because the callback URL resolves to a non-public/non-HTTPS value during the test. The suite covers authentication, license flow, state machine, grace periods, fingerprints, trials, queues, versioning, analytics, team behavior, offline payment, webhooks, and currency conversion. Coverage is strongest on happy paths and field presence; it does not yet prove offline cryptographic verification, transactional rollback, authorization on every sensitive API path, or real provider failure reconciliation.

## 9. Production Readiness Verdict

**Current verdict: Not production-ready.**

The application is suitable for local development and controlled staging after configuring services. It should not receive real customer/payment traffic until all Critical and High items in `PROJECT_GAP_ANALYSIS.md` are closed and the release checklist passes in a production-like environment.

## 10. Source of Truth Rules

When future work changes behavior:

1. Update the owning code and its focused test.
2. Update this document if the capability, contract, data model, or operations requirement changes.
3. Update `PROJECT_GAP_ANALYSIS.md` by moving the item only when executable evidence supports the new status.
4. Update `offline_cache_policy.md` whenever the signed payload, public key, clock, expiry, or revocation behavior changes.
