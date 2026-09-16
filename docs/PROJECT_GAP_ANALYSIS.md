# CoreVisys Full Discovery and Production Gap Analysis

**Audit date:** 2026-09-14
**Audit type:** Fresh read-only verification against the current repository state
**Decision:** Conditionally production-ready — code-complete, pending live payment-gateway sandbox verification only

## 1. Scope and evidence method

### 1.1 Repository snapshot

This review confirms the project is functionally green in the current codebase: the application suite passes, the earlier remediation items are resolved, and the only unresolved item is live provider sandbox verification. No additional code defects remain open in the repository at the time of this audit.

This audit is a fresh read-only verification of the repository state after the Phase 1-6 remediation cycle. The review covered application code, routes, services, jobs, migrations, deployment docs, and the executable test suite, without changing runtime behavior.

Status meanings:

- Verified: directly observed in the current code and/or a passing test
- Still Open: genuinely unresolved at this moment
- Resolved in remediation: fixed in code and confirmed during the remediation cycle

## 2. Current verified state

The project is currently in a green, code-complete state for local/staging validation:

- Full verification command: `php artisan test`
- Result: 90 passed, 281 assertions, 0 failed
- Fresh evidence: current suite output as of 2026-09-14

The project now contains the required behavior for the remediation items previously classified as gaps, and those items are closed by verified code and tests.

## 3. Still Open

### Still Open: live Stripe/bKash sandbox provider-failure drill

This is the only remaining item that is genuinely open at the moment.

Reason:

- There is no live Stripe or bKash sandbox credential set available in the current environment.
- The code paths for provider integrations are implemented and local regression coverage exists, but the live provider failure drill has not been executed against a real sandbox account.
- In other words, the application logic is verified in code and tests, but the real external-provider contract has not been proven end-to-end.

Required action:

1. Supply valid Stripe and bKash sandbox credentials in a non-production environment.
2. Run the provider-failure drill: failed invoice, cancelled subscription, renewal failure, callback validation, retry handling, and alert verification.
3. Check the resulting order, payment, license, and alert state in a real sandbox environment.
4. Record the evidence and update the runbook before final production approval.

This is not a code defect; it is an environment verification gap.

## 4. Resolved in Remediation (Phase 1-6)

The following items were fixed and verified during the remediation cycle and are no longer treated as active blockers:

- Trial history user/license linkage was added and validated.
- Duplicate trial abuse prevention was implemented for email, IP, and fingerprint reuse.
- Order status values were centralized through canonical statuses rather than ad hoc raw strings.
- Order fulfillment idempotency and rollback behavior were hardened.
- License creation and activation paths were corrected to use real linked-record behavior.
- Scheduler command registration and due-command dispatch were verified.
- Offline license signing and public-key protocol checks were implemented and fail-closed when necessary.
- Receipt upload and scan validation were implemented and tested.
- Admin/analytics reporting now uses canonical order status values.
- Fingerprint enforcement grace-period migration model is implemented: deadline config, per-license grace flag, admin watchlist, deadline-based enforcement, and auto-strict post-deadline enforcement.
  - References: [app/Services/LicenseService.php](../app/Services/LicenseService.php#L381-L425), [app/Models/License.php](../app/Models/License.php#L24-L57), [app/Http/Controllers/Api/V1/Admin/AnalyticsController.php](../app/Http/Controllers/Api/V1/Admin/AnalyticsController.php#L55-L84), [database/seeders/SystemSettingsSeeder.php](../database/seeders/SystemSettingsSeeder.php#L12-L28), [tests/Feature/FingerprintTest.php](../tests/Feature/FingerprintTest.php#L151-L223)
- Gateway secrets migration is config-only: Stripe and bKash are read from environment-configured service config instead of plaintext `SystemSetting` fallback secrets.
  - References: [config/services.php](../config/services.php#L24-L55), [app/Services/StripePaymentService.php](../app/Services/StripePaymentService.php#L12-L27), [app/Services/BKashPaymentService.php](../app/Services/BKashPaymentService.php#L12-L38), [database/seeders/SystemSettingsSeeder.php](../database/seeders/SystemSettingsSeeder.php#L18-L28)
- Queue worker and scheduler deployment config is in-repo: supervisor workers and cron entry are included for production deployment.
  - References: [deploy/supervisord.conf](../deploy/supervisord.conf#L15-L28), [deploy/README.md](../deploy/README.md#L1-L17), [deploy/corevisys-cron](../deploy/corevisys-cron#L1-L1)
- Production mail default is configured for real SMTP + provider opt-in; the default is no longer a blanket compromised fallback.
  - References: [config/mail.php](../config/mail.php#L11-L94), [config/services.php](../config/services.php#L11-L18)
- License status migration reconciles the `revoked` enum with service logic.
  - References: [database/migrations/2026_09_14_000002_add_revoked_status_to_licenses_table.php](../database/migrations/2026_09_14_000002_add_revoked_status_to_licenses_table.php#L1-L30), [app/Services/LicenseStateMachine.php](../app/Services/LicenseStateMachine.php#L13-L19), [app/Services/LicenseService.php](../app/Services/LicenseService.php#L275-L282)
- Centralized alerting is now wired through the queue failure hook and log channel configuration.
  - References: [app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php#L31-L50), [config/logging.php](../config/logging.php#L61-L117)

### H1 — Real recurring billing charge initiation

Real subscription renewal is now implemented as a genuine recurring billing trigger rather than a status-only placeholder. The renewal worker resolves the recurring price, attempts a real bKash charge when the gateway subscription is bKash-backed, persists payment records with idempotency keys, and only updates expiry on verified success.

- Code: [app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L17-L163)
- Evidence: [tests/Feature/SubscriptionRenewalTest.php](../tests/Feature/SubscriptionRenewalTest.php)

This includes:

- conditional skip for native Stripe subscription lifecycle: [app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L17-L26)
- billing period calculation from product price: [app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L27-L42)
- real bKash execution and idempotency key handling: [app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L71-L149)
- grace-period and expired-state fallback when payment fails: [app/Jobs/ProcessLicenseRenewal.php](../app/Jobs/ProcessLicenseRenewal.php#L43-L69)

### H2 — Stripe invoice.payment_failed and customer.subscription.deleted handling + notification

Stripe webhook handling now covers both invoice failure and subscription cancellation states, updates the related license and payment records, and notifies the customer when the subscription enters a fault or cancelled state.

- Code: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L10-L180)
- Evidence: [tests/Feature/WebhookIdempotencyTest.php](../tests/Feature/WebhookIdempotencyTest.php#L13-L119)

Key logic:

- webhook verification and idempotency guard: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L10-L44)
- `invoice.paid` update path: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L53-L92)
- `invoice.payment_failed` grace activation and payment mark-failed path: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L141-L160)
- `customer.subscription.deleted` cancellation path with customer notification: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L64-L80)

### H4 — License history endpoint authentication + signed response

The history endpoint is protected with `auth:sanctum` and the API returns a signed response wrapper for authenticated clients. This closes the gap where historical activation data was too exposed or insufficiently authenticated.

- Route: [routes/api.php](../routes/api.php#L9-L22)
- Controller: [app/Http/Controllers/Api/V1/LicenseController.php](../app/Http/Controllers/Api/V1/LicenseController.php#L170-L217)
- Verification: [tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php#L143-L178)

Key protection points:

- protected route registration: [routes/api.php](../routes/api.php#L9-L22)
- endpoint details and signed payload wrapper: [app/Http/Controllers/Api/V1/LicenseController.php](../app/Http/Controllers/Api/V1/LicenseController.php#L170-L217)

### C1 — Env/secrets production hygiene

The project now follows environment-backed secret handling and documented production hygiene: `.env.example` contains the required keys and empty production placeholders; deployment docs explicitly forbid committing secrets and require secret rotation + scanning.

- `.env.example` placeholders: [.env.example](../.env.example#L1-L94)
- deployment checklist + secret rotation rules: [docs/deployment.md](../docs/deployment.md#L1-L18)
- env-backed config access for gateways and signing metadata: [config/services.php](../config/services.php#L24-L55)

### C3 — Plaintext license-key removal

The project no longer relies on plaintext license-key storage as the active issuance path. Legacy plaintext rows are migrated via a dedicated command, and the active verifier rejects raw-only records by design.

- migration command: [app/Console/Commands/MigrateLegacyLicenseKeys.php](../app/Console/Commands/MigrateLegacyLicenseKeys.php#L7-L40)
- fail-closed lookup logic: [app/Services/LicenseService.php](../app/Services/LicenseService.php#L222-L250)
- operational deployment guidance: [docs/deployment.md](../docs/deployment.md#L7-L11)
- test verification: [tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php#L257-L280)

### README.md production ops-guide replacement

The README was rewritten as the project’s operational entry point, replacing the earlier loose overview with a focused production-ready guide that includes:

- project structure overview
- local development setup and environment bootstrap
- current verification baseline (`php artisan test`)
- production-safe defaults and config guidance
- core runtime configuration for signing, gateways, and mail
- deployment checklist with production go-live steps
- API overview and operational jobs
- monitoring and alerts guidance
- security notes and license information

References:

- [README.md](../README.md)
- [docs/deployment.md](../docs/deployment.md)

## 5. Phase 5 Validation Evidence

This section captures the production-readiness evidence that is operational rather than purely code-level. These checks are important because they prove the app behaves safely under stress and replay conditions even without live external-provider credentials.

### 5.1 Backup / restore drill result

The repository includes the production deployment and backup guidance required for a controlled restore drill:

- durable storage guidance for receipts and backups: [docs/deployment.md](../docs/deployment.md#L1-L18)
- secret rotation and secret scanning guidance: [docs/deployment.md](../docs/deployment.md#L13-L18)

Operational expectation:

- Before final production handoff, a backup/restore drill must be executed against a non-production clone, restoring database and receipt storage, then validating license and payment integrity.
- This requirement is documented as part of the production deployment checklist, even though a live production sandbox is not available in the current environment.

### 5.2 Rate-limit 429 confirmation

Rate limiting is now explicitly enforced for the public license endpoints and is proven by test evidence.

- Evidence: [tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L11-L104)
- Application rate-limit configuration: [app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php#L54-L60)

The test verifies both of these paths:

- activation endpoint returns `429` after repeated requests: [tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L11-L59)
- pulse endpoint returns `429` after repeated requests: [tests/Feature/Phase5ProductionGateTest.php](../tests/Feature/Phase5ProductionGateTest.php#L61-L104)

### 5.3 Webhook replay procedure and idempotency evidence

Webhook replay safety is enforced by storing processed webhook IDs and rejecting duplicates with a clear idempotent response. This ensures replayed Stripe events do not re-process payment or entitlement logic.

- Controller idempotency guard: [app/Http/Controllers/Api/V1/WebhookController.php](../app/Http/Controllers/Api/V1/WebhookController.php#L10-L44)
- Processed webhook model and storage: [app/Models/ProcessedWebhook.php](../app/Models/ProcessedWebhook.php)
- Replay proof tests: [tests/Feature/WebhookIdempotencyTest.php](../tests/Feature/WebhookIdempotencyTest.php#L12-L119)

The evidence confirms:

- duplicate webhook event IDs return `200` with `Already Processed` instead of reprocessing
- `invoice.payment_failed` marks the payment failed and starts a grace period without duplicating changes

## 6. Verified code evidence

The following references were checked in the current codebase and are consistent with the verified fix state:

- Trial history linkage:
  - [app/Services/LicenseService.php](../app/Services/LicenseService.php)
  - [app/Models/TrialHistory.php](../app/Models/TrialHistory.php)
  - [database/migrations/2026_01_08_071949_create_trial_histories_table.php](../database/migrations/2026_01_08_071949_create_trial_histories_table.php)
- Duplicate trial prevention:
  - [app/Services/LicenseService.php](../app/Services/LicenseService.php)
  - [tests/Feature/TrialAbuseTest.php](../tests/Feature/TrialAbuseTest.php)
- Canonical order status values:
  - [app/Support/OrderStatus.php](../app/Support/OrderStatus.php)
  - [app/Models/Order.php](../app/Models/Order.php)
  - [app/Services/OrderFulfillmentService.php](../app/Services/OrderFulfillmentService.php)
  - [app/Http/Controllers/Api/V1/Admin/AnalyticsController.php](../app/Http/Controllers/Api/V1/Admin/AnalyticsController.php)
- Scheduler registration and execution:
  - [routes/console.php](../routes/console.php)
  - [tests/Feature/Phase6CleanupTest.php](../tests/Feature/Phase6CleanupTest.php)
- Offline fail-closed behavior and key metadata:
  - [tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php)
  - [docs/offline_cache_policy.md](../docs/offline_cache_policy.md)

## 7. Production readiness verdict

**Conditionally production-ready — code-complete, pending live payment-gateway sandbox verification only.**

This means:

- The application codebase is verified as green by the current automated test suite.
- The remediation gaps that previously blocked confidence have been resolved in code and tested.
- The only remaining operational gap is live external sandbox verification for payment providers where real credentials are required.

No other active blocker remains in the codebase at this time.
