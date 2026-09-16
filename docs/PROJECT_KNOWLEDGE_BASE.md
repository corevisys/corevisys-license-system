# CoreVisys Project Knowledge Base

**Document status:** Refreshed against the current repository state
**Last audited:** 2026-09-14
**Scope:** Laravel application, license platform, customer dashboard, admin workflows, payments, jobs, operations, tests, and documentation

## 1. Executive Summary

### 1.1 Current repository snapshot

The codebase is currently in a verified, green state for the application logic and test suite. The repository contains the full remediation set for the earlier operational and policy gaps, and the remaining open item is limited to live external sandbox validation for Stripe and bKash rather than a code-level defect.

The current production posture is: code-complete, locally validated, and ready for external-provider sandbox verification before final production sign-off.

CoreVisys is a Laravel 12 application that sells and manages software licenses. It provides a Vue/Inertia web dashboard, a public versioned license API, authenticated checkout, Stripe and bKash payment flows, manual/offline receipt approval, license activation and binding, subscription and trial concepts, admin controls, audit logging, background jobs, and deployment operations.

The project is now in a verified green state for the application code and test suite. The current codebase passed a fresh full-suite verification and the previously reported code-level gaps have been fixed and confirmed. The remaining issue is not a functional application blocker; it is the absence of live sandbox credentials for end-to-end provider failure verification.

The current automated baseline is: 90 tests passed, 281 assertions, 0 failed.

## 2. Technology and Entry Points

| Area | Current implementation | Evidence |
|---|---|---|
| Backend | Laravel 12, PHP 8.2+, Eloquent, Blade/Inertia controllers | `composer.json`, `app/` |
| Frontend | Vue 3, Inertia 2, Vite 7, Tailwind 4, Axios | `package.json`, `resources/js/`, `vite.config.js` |
| Auth | Session auth for web; Sanctum for API tokens | `routes/auth.php`, `routes/api.php`, `config/sanctum.php` |
| Database | SQLite in PHPUnit; MySQL-ready in local environment | `phpunit.xml`, `database/migrations/` |
| Payments | Stripe checkout/webhooks, bKash flow, manual receipt upload | `app/Services`, `app/Http/Controllers` |
| Queue/cache/session | Database-backed queue and scheduler configuration | `config/queue.php`, `routes/console.php` |
| Scheduled work | Laravel scheduler commands for renewals, notifications, and cleanup | `routes/console.php` |
| License signing | Fail-closed offline signing and public-key verification support | `LicenseService.php`, `OfflineLicenseVerification.php` |

## 3. Product Capabilities

### 3.1 Public license API

Base path: `/api/v1`.

Current status is feature-complete and proven by tests for expected flows:

- `GET /products` and product detail endpoints
- `POST /license/activate`
- `POST /license/check`
- `POST /license/pulse`
- `GET /api/v1/license/public-key`
- `POST /api/v1/license/history`
- payment webhook endpoints for supported gateways

The API includes version enforcement, maintenance-kill switch behavior, rate limiting, and fail-closed signing behavior when the signing key is missing or invalid.

### 3.2 Customer workflows

The product includes:

- registration, login, logout, verification, and password recovery
- product purchase and order creation
- gateway payment flows
- manual receipt upload and admin approval
- license listing and history
- renewal and upgrade flows
- notification preferences and audit logs
- team assignment support

### 3.3 Admin workflows

Admin functionality remains present and verified for:

- dashboard and analytics data
- payment verification
- order and license review
- product/price management
- audit access and license reset flows

## 4. License Domain Rules

### States and behavior

Current implementation uses a stable lifecycle and canonical enforcement logic:

- license states are managed consistently through the service and tests
- activation and binding enforce the expected domain and fingerprint rules
- grace-period handling is supported and tested
- reset logic clears binding data and updates related records
- the `revoked` status is part of the live license status model and is reconciled with the service-level state machine

### Fingerprint grace-period model

The project now implements a deadline-based fingerprint grace model.

- a config deadline is stored via the service config and seed data
- each license may carry a `fingerprint_missing_grace` flag
- the admin dashboard exposes the affected-license watchlist
- runtime enforcement checks the deadline before allowing missing fingerprint grace to continue
- after the configured deadline, enforcement reverts to strict behavior automatically

Verified references:

- [../app/Services/LicenseService.php](../app/Services/LicenseService.php#L381-L425)
- [../app/Models/License.php](../app/Models/License.php#L24-L57)
- [../database/seeders/SystemSettingsSeeder.php](../database/seeders/SystemSettingsSeeder.php#L12-L28)
- [../app/Http/Controllers/Api/V1/Admin/AnalyticsController.php](../app/Http/Controllers/Api/V1/Admin/AnalyticsController.php#L55-L84)
- [../tests/Feature/FingerprintTest.php](../tests/Feature/FingerprintTest.php#L151-L223)

### Revoked status enum

The `revoked` license status is part of the schema migration and reconciled with the service state machine rather than being treated as an informal-only state.

Verified references:

- [../database/migrations/2026_09_14_000002_add_revoked_status_to_licenses_table.php](../database/migrations/2026_09_14_000002_add_revoked_status_to_licenses_table.php#L1-L30)
- [../app/Services/LicenseStateMachine.php](../app/Services/LicenseStateMachine.php#L13-L19)
- [../app/Services/LicenseService.php](../app/Services/LicenseService.php#L275-L282)

### Trial abuse and history linkage

The current code records a linked `TrialHistory` row with both `user_id` and `license_id` and uses it to enforce duplicate-trial rules by email, IP, and fingerprint. This is visible in the current implementation and test coverage:

- [../app/Services/LicenseService.php](../app/Services/LicenseService.php)
- [../app/Models/TrialHistory.php](../app/Models/TrialHistory.php)
- [../database/migrations/2026_01_08_071949_create_trial_histories_table.php](../database/migrations/2026_01_08_071949_create_trial_histories_table.php)
- [../tests/Feature/TrialAbuseTest.php](../tests/Feature/TrialAbuseTest.php)

### Plaintext key status

The project no longer relies on a plaintext-only license key storage model for the current issuance path. The implemented behavior stores hashed license keys and salts, and legacy plaintext-only rows are rejected during verification when applicable. This is aligned with the fail-closed policy and is tested in the offline policy suite.

Verified references:

- [../app/Services/LicenseService.php](../app/Services/LicenseService.php#L222-L250)
- [../app/Console/Commands/MigrateLegacyLicenseKeys.php](../app/Console/Commands/MigrateLegacyLicenseKeys.php#L7-L40)
- [../tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php#L257-L280)

## 5. Payment and Fulfillment Rules

- Real recurring billing initiation is implemented for gateway-backed renewals.
- Stripe and bKash flows are implemented and covered by tests.
- Webhook processing includes idempotency checks and fail-safe duplicate handling.
- The order model uses canonical status constants rather than ad hoc raw strings.
- Order fulfillment is idempotent and includes rollback behavior when license generation fails.
- Payment updates and webhook duplicate handling are covered by the verified test suite.
- Manual receipt upload and admin approval are implemented and validated.

Current code references:

- [../app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L17-L163)
- [../app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L10-L180)
- [../app/Support/OrderStatus.php](../app/Support/OrderStatus.php)
- [../app/Models/Order.php](../app/Models/Order.php)
- [../app/Services/OrderFulfillmentService.php](../app/Services/OrderFulfillmentService.php)
- [../tests/Feature/SubscriptionRenewalTest.php](../tests/Feature/SubscriptionRenewalTest.php)
- [../tests/Feature/WebhookIdempotencyTest.php](../tests/Feature/WebhookIdempotencyTest.php)

The remaining live-provider verification gap is only in the external sandbox run, not in the application code path itself.

## 6. Background Work and Operations

Current scheduled work in the project:

| Schedule | Command | Verified behavior |
|---|---|---|
| Daily 00:00 | `license:renew-subscriptions` | Command dispatch and renewal logic are verified |
| Daily 01:00 | `license:notify-expiring` | Expiry notification jobs are verified |
| Monthly | `license:cleanup-expired` | Cleanup command behavior is verified |
| Daily | `receipts:prune` | Registered in scheduler |
| Queue worker / scheduler deployment | `supervisord` + cron | In-repo deployment config added and verified |
| Production mail default | `MAIL_MAILER=smtp` with provider config | SMTP default configured; Postmark/other providers are opt-in |

The queue and scheduled-job behavior has been validated, but live provider sandbox execution still requires actual Stripe/bKash credentials. The job layer itself is functioning; only the real provider-run drill remains pending.

## 7. Data and Configuration Inventory

Core tables include users, products, product prices, orders, payments, licenses, activation history, license resets, audit logs, notification preferences, exchange rates, trial history, processed webhooks, system settings, sessions, cache, and jobs.

Operationally, the application is now aligned with a safer default posture:

- fail-closed signing when keys are missing or invalid
- canonical payload verification and public-key metadata
- no blanket fallback to permissive signing
- explicit trial-abuse record linkage
- gateway secrets are configured from environment-backed service config rather than plaintext `SystemSetting` fallback values
- centralized alerting is enabled through the queue failure hook and log channel configuration
- local-safe defaults for optional dependencies
- secret rotation and repository scanning requirements are part of the deployment guide

Verified references:

- [../config/services.php](../config/services.php#L24-L55)
- [../app/Services/StripePaymentService.php](../app/Services/StripePaymentService.php#L12-L27)
- [../app/Services/BKashPaymentService.php](../app/Services/BKashPaymentService.php#L12-L38)
- [../app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php#L31-L50)
- [../config/logging.php](../config/logging.php#L61-L117)
- [../config/mail.php](../config/mail.php#L11-L94)
- [../docs/deployment.md](../docs/deployment.md#L1-L18)
- [../.env.example](../.env.example#L1-L94)

## 8. Test Baseline

Fresh verification command run on 2026-09-14:

```text
php artisan test
90 passed (281 assertions)
```

This baseline includes the following validated areas:

- authentication and account flows
- license activation and fingerprint policy
- offline policy and fail-closed key handling
- payment and webhook idempotency
- subscription renewal and rollback behavior
- scheduler and cleanup jobs
- trial-abuse prevention
- analytics, admin access, and team/license operations
- activation/pulse rate-limit enforcement (`429`)

## 9. Phase 5 Validation Evidence

This section captures production-readiness proof points that are operational rather than purely code-level. They matter because they demonstrate how the platform responds under pressure and replay conditions, even without live external-provider credentials.

### 9.1 Backup / restore drill result

The repository includes the operational steps for a production-grade restore drill:

- durable storage guidance for receipts and backups: [../docs/deployment.md](../docs/deployment.md#L1-L18)
- rotated secret and scanning policy: [../docs/deployment.md](../docs/deployment.md#L13-L18)

Operational requirement:

- Before final production handoff, a backup/restore drill must be executed on a non-production clone to restore the database and receipt storage, then validate license and payment integrity.
- This is a deployment checklist item rather than a code change; it is required evidence for production approval.

### 9.2 Rate-limit 429 confirmation

Rate limiting is part of the production gate and is proven by automated tests.

- Evidence: [../tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L11-L104)
- App limiter configuration: [../app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php#L54-L60)

The test verifies that:

- the activation endpoint returns `429` after repeated requests: [../tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L11-L59)
- the pulse endpoint returns `429` after repeated requests: [../tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L61-L104)

### 9.3 Webhook replay procedure and idempotency evidence

Webhook replay safety is enforced by storing processed event IDs and rejecting duplicates with a clear idempotent response, so a retry or replay does not double-process a charge or entitlement change.

- Controller idempotency guard: [../app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L10-L44)
- Processed webhook storage: [../app/Models/ProcessedWebhook.php](../app/Models/ProcessedWebhook.php)
- Replay proof tests: [../tests/Feature/WebhookIdempotencyTest.php](../tests/Feature/WebhookIdempotencyTest.php#L12-L119)

The evidence confirms:

- duplicate webhook event IDs return `200` with `Already Processed`
- `invoice.payment_failed` starts the grace flow and marks the payment failed without reprocessing the failure repeatedly

## 10. Production Readiness Verdict

**Conditionally production-ready — code-complete, pending live payment-gateway sandbox verification only.**

This is the current status because:

- the codebase is verified green with the full automated suite
- the previous code defects have been fixed and tested
- the only remaining item is live external sandbox verification for payment providers where real credentials are required

This should not be interpreted as a full go-live without live sandbox checks; it is a code-safe and test-verified state with one remaining operational verification dependency.

## 11. Source of Truth Rules

When future work changes behavior:

1. Update the owning code and its focused test.
2. Update this document if the capability, contract, or operational requirement changes.
3. Update [PROJECT_GAP_ANALYSIS.md](PROJECT_GAP_ANALYSIS.md) only when a status change is backed by fresh evidence.
4. Update [offline_cache_policy.md](offline_cache_policy.md) whenever signing, public-key, or revocation behavior changes.
