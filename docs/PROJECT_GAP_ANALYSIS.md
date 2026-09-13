# CoreVisys Full Discovery and Production Gap Analysis

**Audit date:** 2026-09-13
**Audit type:** Evidence-based repository discovery
**Decision:** Not production-ready

## 1. Scope and Evidence Method

Reviewed the complete `docs/` folder plus the application entrypoints, routes, controllers, services, jobs, console commands, scheduler, models, migrations, configuration, environment file, frontend entrypoints, tests, and relevant logs. The status labels below mean:

- **Verified:** directly observed in code and/or an executable test.
- **Partial:** a path exists but is incomplete, permissive, or only simulated.
- **Unverified:** the repository does not provide enough operational evidence.
- **Blocker:** must be resolved before real production traffic.

## 2. Current Stage

| Stage | Result | Evidence |
|---|---|---|
| Product scope | Broad working prototype / staging candidate | License, payments, dashboard, admin, teams, trials, and tests exist |
| Local development | Working with configured local dependencies | Laravel/Vite scripts and local `.env` exist |
| Automated verification | Partial | `php artisan test`: 64 passed, 3 failed, 184 assertions |
| Staging readiness | Conditional | Requires real gateway, HTTPS callback, queue worker, scheduler, storage, and secrets |
| Production readiness | **No** | Critical security, consistency, and operations gaps remain |

## 3. Verified Capabilities

### Implemented with meaningful tests

- Authentication and password/account workflows.
- Product, pricing, order, payment, license, team, notification, exchange-rate, audit, and webhook data paths.
- License activation, domain binding, activation limits, fingerprints, grace period, reset, state transitions, and trial-abuse checks.
- Stripe webhook signature validation and duplicate-event handling.
- bKash create/execute/callback paths, subject to public HTTPS configuration.
- Offline/manual receipt upload and admin approval.
- API version enforcement and kill switch.
- Queue dispatch tests and subscription renewal command tests.

### Implemented only partially

- Offline licensing has a server signature envelope, but the client verification contract is not complete.
- Subscription renewal schedules jobs but does not perform real recurring provider billing.
- Expiry notifications log delivery intent instead of sending notifications.
- Cleanup audits old licenses instead of archiving/deleting them.
- Admin API checks exist but are not expressed consistently as route middleware/policies.

## 4. Release Blockers

### Critical

| ID | Finding | Evidence | Required exit condition |
|---|---|---|---|
| C1 | Development environment and secrets are present in `.env`: debug enabled, local URL, application key, and license private key. | `.env`, `config/app.php`, `config/services.php` | Remove from source/shared storage, rotate `APP_KEY`, signing key, gateway/database credentials, set production env, disable debug, verify secret scanning. |
| C2 | Offline trust is unsafe/inconsistent. The HMAC `signature` uses `app.key`; the RSA-style `server_signature` can become `MISSING_KEY`, `INVALID_KEY`, or `SIGNING_FAILED` while the endpoint still returns success. | `LicenseService.php`, `LicenseController.php`, `offline_cache_policy.md` | Use one asymmetric protocol, canonical payload, key ID, public-key distribution, fail-closed signing, client verification tests, clock/revocation rules. |
| C3 | Plaintext license keys are stored and looked up before hashes; full keys are returned for display. | `LicenseService.php`, `License.php`, license migrations, `routes/web.php` | Remove plaintext persistence and lookup, define one-time issuance/recovery, migrate existing keys, redact logs/UI, add compromise tests. |
| C4 | Fulfillment marks an order completed before license generation. A license failure can leave an apparently paid/completed order without a license. | `OrderFulfillmentService.php`, Stripe success route, `WebhookController.php` | Use a transaction and idempotency constraints; only finalize order/payment after license issuance succeeds; add rollback/retry tests. |
| C5 | Renewal route references `$request` without declaring/injecting it. Renewal can fail at runtime. | `routes/web.php` renewal closure | Inject `Illuminate\Http\Request`, add a request test for both Stripe and bKash renewal initiation. |

### High

| ID | Finding | Required work |
|---|---|---|
| H1 | Recurring renewal is not real billing; success is effectively assumed in the renewal path. | Integrate provider subscription/charge APIs, reconcile transaction IDs, handle retries, cancellation, grace, and failed payment transitions. |
| H2 | Stripe `invoice.payment_failed` and `customer.subscription.deleted` only log. | Update payment/license state, schedule customer notice, enforce grace/revocation, and test each event idempotently. |
| H3 | Fingerprint can be omitted; `check` and `pulse` do not enforce it. | Make enforcement mode explicit, document compatibility mode, and test strict clients against omitted/mismatched fingerprints. |
| H4 | Public history endpoint exposes IP, domain, failure reason, and timestamps to anyone possessing a key. | Minimize fields, authenticate or sign requests, rate-limit, redact sensitive values, and define retention. |
| H5 | Gateway secrets are stored as plaintext `system_settings`. | Move secrets to environment/secret manager or encrypted-at-rest settings; restrict admin reads and rotate. |
| H6 | Database queue and scheduler require external workers/cron; no deployment process config or failed-job alerting exists. | Add worker supervisor/container process, scheduler cron, retries/backoff, failed-job dashboard/alerts, and runbook. |
| H7 | Receipt files use local storage by default. | Use private durable object storage, signed retrieval, size/MIME validation, malware scanning, retention, and backup policy. |
| H8 | Mail defaults to log driver and notification job only logs delivery. | Configure real mail provider and implement/verify email delivery; add SMS/push only when supported and observable. |

### Medium

- API admin routes use `auth:sanctum` without a shared admin middleware/policy boundary; normalize authorization and add denial tests for every sensitive endpoint.
- Order creation should reject inactive products and accept a validated `product_price_id` rather than silently selecting the first full price.
- Receipt upload should reject completed/cancelled orders and duplicate submissions.
- Migration/status vocabulary is inconsistent: service code checks `revoked`, while visible schema values do not include it; order status history also needs one canonical enum.
- Trial history lacks user/license linkage, reducing attribution and making legitimate reactivation policy unclear.
- Cleanup command does not archive/delete despite its description and option names.
- Logging is local/debug-oriented, with no centralized alerting, metrics, webhook replay view, or queue health signal.
- Root `README.md` is stock Laravel documentation and does not describe this product, setup, deployment, API, payments, or operations.

## 5. Test and Verification Gaps

Current command result:

```text
php artisan test
64 passed, 3 failed, 184 assertions
```

The failed tests are in `BKashPaymentTest`; the callback URL guard correctly rejects the local/non-public URL. CI must either set a public HTTPS test URL or isolate this integration behind a deterministic fake/configuration. The suite is not a release gate until it is green.

Add tests for:

1. Missing/invalid signing key must fail closed, never return a successful license response.
2. A client can verify the exact offline payload using the published public key.
3. Fulfillment rolls back order/payment state when license issuance fails.
4. Duplicate webhook delivery cannot create duplicate licenses/payments.
5. Renewal initiates successfully with both supported gateways.
6. Failed invoice and cancelled subscription transition state correctly.
7. Admin API endpoints deny authenticated non-admin users.
8. Omitted/mismatched fingerprint behavior under each enforcement mode.
9. Inactive product, invalid price, completed order receipt upload, and duplicate receipt cases.
10. Queue retry, failed job, scheduler invocation, backup/restore, and object-storage access.

## 6. Prioritized Remediation Roadmap

### P0: Before any production traffic

1. Rotate all exposed/shared secrets and remove `.env` from version control/shared artifacts.
2. Disable debug and configure production URL, HTTPS, mail, database, storage, queue, cache, and logging.
3. Replace the offline protocol with fail-closed asymmetric verification and update clients/docs/tests.
4. Remove plaintext license-key storage and full-key rendering.
5. Make payment fulfillment transactional, idempotent, and retry-safe.
6. Fix renewal request injection and add focused route tests.

### P1: Before paid subscription launch

1. Implement real recurring payment/reconciliation and provider failure webhooks.
2. Add explicit admin middleware/policies and ownership checks.
3. Configure queue workers, scheduler, retries, failed-job alerts, and deployment runbook.
4. Implement real email delivery and define notification failure behavior.
5. Move receipt storage to private durable storage with retention and scanning.

### P2: Before scale-up

1. Centralize logs/metrics/traces and add operational dashboards.
2. Add backup, restore-drill, disaster-recovery, and webhook replay procedures.
3. Resolve status/enum/migration inconsistencies and add database constraints.
4. Replace the stock README with a customer/operator setup guide.
5. Add load, abuse, rate-limit, and long-running queue tests.

## 7. Definition of Done for Production

- `php artisan test` is green in clean CI with production-like service configuration.
- No development `.env` or live secret is committed, logged, or returned.
- Offline clients verify a canonical asymmetric payload and handle expiry/revocation safely.
- Payment, webhook, fulfillment, renewal, cancellation, refund/failure, and duplicate-delivery paths are idempotent and tested.
- Every admin/payment/license operation has an explicit authorization and audit trail.
- Queue worker, scheduler, storage, mail, backups, monitoring, alerting, rollback, and incident runbooks are deployed and exercised.
- Security review confirms key handling, file uploads, rate limits, PII exposure, and retention policy.

## 8. Enhanced Reusable Audit Prompt

### Bangla

> এই Laravel প্রজেক্টের একটি সম্পূর্ণ, read-only, evidence-based discovery ও production-readiness audit করুন। প্রথমে `docs/`, root config, `.env.example`/environment files, `composer.json`, `package.json`, routes, middleware, controllers, services, jobs, console commands, scheduler, models, migrations, policies/gates, frontend entrypoints, tests, logs এবং deployment-related files পরিদর্শন করুন। কোনো গুরুত্বপূর্ণ surface বাদ দেবেন না।
>
> রিপোর্টে অবশ্যই দিন: (1) product capability ও architecture map, (2) প্রতিটি public/auth/admin endpoint-এর purpose, auth, validation, side effect ও rate limit, (3) data model ও migration consistency, (4) executable test command, exact pass/fail count ও failure classification, (5) security audit: secrets, plaintext PII/license keys, signing/verification, authorization, uploads, rate limits, replay/idempotency, (6) payment ও webhook state machine, (7) queue/scheduler/storage/mail/backup/monitoring operational readiness, (8) docs বনাম code inconsistency, (9) Critical/High/Medium/Low issue table, (10) exact remediation order, exit criteria এবং production go/no-go verdict।
>
> প্রতিটি material finding-এর পাশে workspace-relative file path এবং exact line reference দিন। প্রতিটি claim-কে `Verified`, `Partial`, `Unverified`, অথবা `Assumption` হিসেবে label করুন। Test failure হলে code bug, environment prerequisite এবং missing test আলাদা করুন। Happy path দেখে production-ready বলবেন না; negative path, rollback, retry, duplicate delivery, key rotation, backup restore এবং real provider failure যাচাই করুন। কোনো file edit করবেন না; শেষে 10-15 লাইনের একটি reusable follow-up prompt দিন যাতে পরের audit-এ আগের findings পুনরায় হারিয়ে না যায়।

### English

> Perform a complete, read-only, evidence-based discovery and production-readiness audit of this Laravel project. Inspect every relevant surface: `docs/`, root configuration, environment examples and environment files, Composer/NPM manifests, routes, middleware, controllers, services, jobs, console commands, scheduler, models, migrations, policies/gates, frontend entrypoints, tests, logs, and deployment-related files. Do not omit a material surface.
>
> Report: (1) product capability and architecture map, (2) every public/authenticated/admin endpoint with purpose, auth, validation, side effects, and rate limits, (3) data-model and migration consistency, (4) the exact executable test command and pass/fail counts with failure classification, (5) security findings covering secrets, plaintext PII/license keys, signing/verification, authorization, uploads, rate limits, replay/idempotency, (6) payment and webhook state machines, (7) queue/scheduler/storage/mail/backup/monitoring readiness, (8) documentation/code inconsistencies, (9) Critical/High/Medium/Low issue table, (10) ordered remediation plan, exit criteria, and a production go/no-go verdict.
>
> Cite a workspace-relative file and exact line reference for every material claim. Label each claim `Verified`, `Partial`, `Unverified`, or `Assumption`. Separate code defects from environment prerequisites and missing tests. Do not declare production readiness from happy paths; verify negative paths, rollback, retries, duplicate delivery, key rotation, backup restore, and real provider failures. Do not edit files. End with a short reusable follow-up prompt that preserves every finding for the next audit.
