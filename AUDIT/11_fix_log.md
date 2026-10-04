# Step 1 – Phase 6 Fix Log

**Date:** 2026-10-02  
**Branch:** `fix/audit-2026-10` (both repositories)  
**Status:** Phase 6 (Docs & Housekeeping) Complete

---

## 1. Commit Log Summary

### LiencesSite (Server)
1. `198e74e` — `fix(FIX-001): add license:generate-keys command; sanitize .env.example of real keys; add key rotation tests`
2. `bf80deb` — `fix(FIX-002): untrack database dumps and sqlite backups; update .gitignore`
3. `e12d1d9` — `test(FIX-003): add tests for corevisys:install command`
4. `9df4af1` — `sec: SEC-007 enforce product_code on activate/check/pulse (server + package tests)`
5. `6cf28c5` — `fix: FIX-004 implement license deactivation endpoint with domain and fingerprint verification, audit log, throttle, and tests`
6. `1fa565d` — `fix: FIX-005 remove simulated billing stub in processRenewals, enforce real payment confirmation and recurring gateway charging with tests`
7. `f48e5a9` — `fix: FIX-006 remove now() call from config/services.php and compute fallback deadline dynamically to support config:cache`
8. `21e91b9` — `fix: update Symfony dependencies to resolve 6 security advisories (process, routing, yaml)`
9. `de646d1` — `fix: FIX-006 use null deadline default and prove no rolling forward`
10. `77718a5` — `test: FIX-004 add missing deactivation edge cases`
11. `33672f1` — `chore: update league/commonmark, flysystem, phpunit, psysh`
12. `11bce2a` — `fix: safe-guard early Log facade resolution in bootstrap/app.php`
13. `f123859` — `fix(FIX-006): enforce fingerprint by default from day one with optional grace deadline override`
14. `0b80f7b` — `fix: add created_at/updated_at to Payment and License fillable to allow test seeding of historical timestamps`
15. `7b0203a` — `fix(FIX-005): use expires_at as cycle anchor, add SubscriptionBillingNotice mailable, comprehensive renewal lifecycle tests`
16. `fac4bc5` — `fix: enforce bKash server-side verification and idempotent callback per paymentID`

### LiencesInstall_VerifyPackage (Package)
1. `1e2527c` — `fix(FIX-002): add database dumps and backup ignore patterns to .gitignore`
2. `bb5ee06` — `test(FIX-001): add key revocation and new key acceptance tests for package`
3. `4384005` — `sec: SEC-007 client rejects product_code mismatch in activator/verifier (+ tests)`
4. `64c226d` — `fix: FIX-004 implement package deactivate method delegating to activator with 404/403/network error handling and tests`
5. `533b379` — `sec: SEC-007 enforce non-empty matching product_code across all paths in package`

---

## 2. Test Suite Status

- **Server (`LiencesSite`):**
  - **228 passed** (845 assertions)
  - Zero test failures, zero regressions
  - Net increase of +31 tests in `fix/audit-2026-10`

- **Package (`LiencesInstall_VerifyPackage`):**
  - **298 passed** (776 assertions)
  - Zero test failures, zero regressions
  - Net increase of +20 tests in `fix/audit-2026-10`

- **Dependency Security Audits:**
  - `composer audit` on `LiencesSite`: **0 advisories** (clean)
  - `composer audit` on `LiencesInstall_VerifyPackage`: **0 advisories** (clean)

---

## 3. Items Completed

| Item | Description | Server Status | Package Status |
|---|---|---|---|
| **FIX-001** | Production key rotation command + sanitization + tests | Complete (`198e74e`) | Complete (`bb5ee06`) |
| **FIX-002** | Untrack sqlite/database dumps and update `.gitignore` | Complete (`bf80deb`) | Complete (`1e2527c`) |
| **FIX-003** | CLI-only installer `corevisys:install` + 503 route | Complete (`e12d1d9`) | N/A |
| **FIX-004** | License deactivation endpoint + package client method | Complete (`6cf28c5`, `77718a5`) | Complete (`64c226d`) |
| **FIX-005** | Production renewal charging (remove simulated billing, cycle anchor payment) | Complete (`1fa565d`, `7b0203a`) | N/A |
| **FIX-006** | Fingerprint enforcement (enforced from day one, null deadline default, no rolling forward) | Complete (`f48e5a9`, `de646d1`, `f123859`) | N/A |
| **SEC-007** | Cross-product license isolation (server + package on all paths) | Complete (`9df4af1`) | Complete (`4384005`, `533b379`) |
| **SEC-AUDIT** | Composer security vulnerability updates (Symfony, CommonMark, Flysystem) | Complete (`21e91b9`, `33672f1`) | Complete (0 advisories) |
| **RUNBOOK** | Production rotation runbook (`14_manual_rotation_steps.md`) | Complete | Complete |
| **PHASE-5** | bKash server-side callback verification, query fallback, idempotency per paymentID | Complete (`fac4bc5`) | N/A |

---

## 4. FIX-001 Production Status

> [!IMPORTANT]
> **FIX-001 is code complete.** The `license:generate-keys` command is implemented and tested.
> However, the actual **production key rotation** (generating and deploying a new `LICENSE_SIGNING_KEY_ID`,
> `LICENSE_SIGNING_PRIVATE_KEY`, `LICENSE_SIGNING_PUBLIC_KEY`, and `APP_KEY`) is **PENDING** —
> this is a manual operation to be performed by the operator following `/AUDIT/14_manual_rotation_steps.md`.

---

## 5. Test Names Per Fix

### FIX-001 — Key Rotation & `.env` Sanitization
**Server (`KeyRotationTest.php`, `SigningKeyConfigSafetyTest.php`):**
- `license:generate-keys command outputs required env var names`
- `.env.example does not contain the old real APP_KEY`
- `.env.example does not contain real RSA private key material`
- `.env.example does not contain real RSA public key material`
- `(E) accepts a response signed with the current valid key`
- `(C) rejects a response claiming an unknown key_id`
- `(D) rejects a response whose key_id is in the revocation list`
- `never lists a revoked key id in available_keys regardless of rotation_overlap_days or available_keys`
- `refuses to serve public-key when active key itself is revoked`
- `test_license_signing_key_id_has_no_default_when_unconfigured`
- `test_missing_signing_key_id_fails_loudly_in_production`
- `test_tests_can_set_their_own_signing_key_id_config`

**Package (`KeyRevocationAndAcceptanceTest.php`):**
- `test_payload_with_revoked_key_id_is_rejected_on_activate`
- `test_payload_with_revoked_key_id_is_rejected_on_online_check`
- `test_payload_with_revoked_key_id_is_rejected_on_fast_path`
- `test_payload_with_revoked_key_id_is_rejected_on_offline_path`
- `test_payload_with_new_key_id_signed_by_new_test_key_is_accepted`

### FIX-002 — Untrack DB Dumps
No dedicated test file (`.gitignore` change verified by git status).

### FIX-003 — CLI Installer
**Server (`CorevisysInstallCommandTest.php`, `CpanelSetupRemovedTest.php`):**
- `test_install_generates_random_password_when_no_password_provided`
- `test_install_accepts_specified_password`
- `test_install_refuses_to_rerun_when_lock_file_exists_without_force`
- `test_install_with_force_allows_rerun_but_preserves_existing_admin`
- `GET /cpanel-setup returns 404`
- `POST /cpanel-setup returns 404`
- `CPanelSetupController class no longer exists`

### FIX-004 — License Deactivation
**Server (`DeactivationTest.php`):**
- `deactivation_succeeds_from_bound_domain`
- `deactivation_is_rejected_for_unauthorised_domain`
- `deactivation_is_rejected_when_fingerprint_does_not_match`
- `deactivation_returns_404_for_invalid_license_key`
- `deactivation_succeeds_from_secondary_domain_with_activation_history`
- `deactivation_of_already_deactivated_license_is_rejected`
- `license_is_reactivatable_on_new_domain_after_deactivation`
- `deactivation_endpoint_rate_limits_excessive_requests`

**Package (`DeactivatorTest.php`):**
- `deactivate_returns_true_on_server_success`
- `deactivate_returns_false_on_server_404_and_clears_cache`
- `deactivate_treats_409_already_deactivated_as_success`
- `deactivate_returns_false_on_server_403_rejection`
- `deactivate_returns_false_on_network_error`
- `deactivate_sends_fingerprint_domain_and_product_code_in_request`
- `deactivate_succeeds_without_prior_activation_when_no_key_in_storage`
- `deactivate_command_warns_that_server_binding_may_remain_on_failure`

### FIX-005 — Production Renewal Charging
**Server (`ProcessRenewalsLiveTest.php`):**
- `test_initial_purchase_payment_cannot_be_reused_for_renewal`
- `test_second_consecutive_renewal_requires_second_payment`
- `test_running_renewal_command_twice_does_not_extend_or_charge_twice`
- `test_payment_failure_moves_to_grace_and_notifies_customer`
- `test_grace_period_expiration_marks_license_expired_and_notifies_customer`
- `test_job_path_grants_grace_once_then_expires_and_no_infinite_grace`
- `test_suspended_license_is_never_renewed_or_reactivated`
- `test_revoked_license_is_never_renewed_or_reactivated`
- `test_cancelled_license_is_never_renewed_or_reactivated`
- `test_renewal_rejects_payment_for_different_license`
- `test_renewal_rejects_payment_already_consumed`
- `test_renewal_rejects_payment_with_insufficient_amount`
- `test_advance_renewal_payment_made_before_expiry_is_accepted`
- `test_stripe_managed_subscription_is_bypassed_by_renewal_service_and_job`
- `test_renewal_aligns_next_billing_at_to_new_expiry_date`
- `test_renewal_charges_bkash_recurring_subscription_when_configured`
- `test_renewal_rejects_payment_with_mismatched_currency`
- `test_renewal_payment_consumed_atomically_preventing_concurrent_double_renewal`

### FIX-006 — Fingerprint Enforcement & Config Cache Safety
**Server (`FingerprintTest.php`):**
- `test_license_binds_fingerprint_on_first_use`
- `test_license_rejects_fingerprint_mismatch`
- `test_license_rejects_missing_fingerprint_when_bound`
- `test_license_rejects_missing_fingerprint_by_default_in_standard_mode`
- `test_license_allows_missing_fingerprint_during_grace_window`
- `test_license_rejects_missing_fingerprint_after_grace_deadline`
- `test_fingerprint_grace_window_does_not_roll_forward_when_deadline_is_null`
- `test_fingerprint_grace_window_enforcement_is_cache_safe`
- `test_fingerprint_enforcement_works_with_config_cache`
- `test_http_fingerprint_enforcement_default_is_enforced`
- `test_http_fingerprint_enforcement_future_deadline_allows_grace`
- `test_http_fingerprint_enforcement_past_deadline_is_enforced`

### SEC-007 — Cross-Product License Isolation
**Server (`CrossProductLicenseTest.php`, `ProductSlugGuardTest.php`):**
- `test_activation_rejects_mismatched_product_code`
- `test_activation_accepts_matching_product_code`
- `test_check_rejects_mismatched_product_code`
- `test_check_accepts_matching_product_code`
- `test_pulse_rejects_mismatched_product_code`
- `test_pulse_accepts_matching_product_code`
- `test_activation_rejects_missing_product_code`
- `test_activation_rejects_empty_product_code`
- `test_check_rejects_missing_product_code`
- `test_check_rejects_empty_product_code`
- `test_pulse_rejects_missing_product_code`
- `test_pulse_rejects_empty_product_code`
- `test_deactivate_rejects_missing_product_code`
- `test_deactivate_rejects_empty_product_code`
- `test_deactivate_rejects_mismatched_product_code`
- `test_deactivate_accepts_matching_product_code`
- `test_admin_cannot_change_slug_when_licenses_exist_via_explicit_slug`
- `test_admin_cannot_change_name_resulting_in_new_slug_when_licenses_exist`
- `test_admin_can_update_product_details_when_licenses_exist_if_slug_is_preserved`
- `test_admin_can_change_slug_when_no_licenses_exist`
- `test_model_level_guard_throws_domain_exception_when_slug_dirtied_with_licenses`
- `test_model_level_guard_allows_slug_change_when_no_licenses`

**Package (`CrossProductPackageTest.php`):**
- `test_activation_rejects_response_with_mismatched_product_code`
- `test_online_check_rejects_response_with_mismatched_product_code`
- `test_fast_path_rejects_cached_record_with_mismatched_product_code`
- `test_offline_grace_path_rejects_cached_record_with_mismatched_product_code`
- `test_matching_product_code_is_accepted_on_activate_and_check`
- `test_activation_rejects_response_with_missing_product_code`
- `test_online_check_rejects_response_with_missing_product_code`
- `test_fast_path_rejects_cached_record_with_missing_product_code`
- `test_offline_grace_path_rejects_cached_record_with_missing_product_code`
- `test_pulse_rejects_response_with_mismatched_product_code`
- `test_pulse_rejects_response_with_missing_product_code`

### PHASE-5 — bKash Server-Side Verification & Idempotency
**Server (`BKashPaymentTest.php`, `PrFixes1To3Test.php`):**
- `test_bkash_service_ignores_database_secret_fallbacks`
- `test_create_payment_calls_grant_and_create_and_returns_bkash_url`
- `test_api_order_store_with_bkash_returns_url_and_stores_payment`
- `test_bkash_callback_executes_and_fulfills_order`
- `test_bkash_callback_does_not_fulfill_when_not_completed`
- `test_create_payment_converts_non_bdt_order_amount_to_bdt`
- `test_create_payment_throws_when_callback_url_is_not_public_https`
- `test_create_payment_throws_when_bdt_rate_missing_for_non_bdt_order`
- `test_api_execute_bkash_confirms_and_fulfills`
- `test_bkash_callback_ignores_query_parameters_and_verifies_server_side`
- `test_bkash_callback_ignores_cancel_query_parameter_when_server_confirms_completed`
- `test_bkash_callback_falls_back_to_query_payment_if_execute_fails_and_fulfills_if_completed`
- `test_bkash_callback_is_idempotent_on_duplicate_invocations_no_duplicate_license`
- `test_bkash_callback_is_idempotent_on_renewal_orders_no_duplicate_renewal`
- `test_api_execute_bkash_is_idempotent_on_repeated_calls`
- `test_order_fulfillment_service_skips_already_completed_renewal_order`
- `test_bkash_token_is_cached_and_grant_not_called_on_second_request`
- `test_bkash_token_ttl_is_expires_in_minus_60_seconds`
- `test_bkash_failed_token_response_is_not_cached`
- `test_bkash_stale_cached_token_is_forgotten_and_retried_once_on_401`

---

## 6. bootstrap/app.php Log Guard — Commit `11bce2a`

**What broke:** The original code called `Log::error()` / `Log::warning()` directly inside the
`bootstrap/app.php` boot closure (the `TRUSTED_PROXIES` missing-config warning). This ran before
the service container had fully resolved its bindings. On certain boot paths (e.g. artisan commands
that run before the HTTP kernel, or test boots that don't go through the full HTTP stack), calling
`Log::` at that early stage attempted to resolve the Log facade before the application was
bootstrapped, throwing a `BindingResolutionException` or silently swallowing the log call.

**Why the guard is needed:** Wrapping the `Log::` call with
`if (Facade::getFacadeApplication())` ensures the facade is only used when the container is
ready. If the container is not yet available (early boot / CLI bootstrap), the warning is silently
skipped — which is acceptable because the operator misconfiguration is caught by application tests
and startup checks rather than crashing the boot process.

---

## 7. composer.lock — Major Version Jumps (LiencesSite only)

All changes are in **`packages-dev`** (not runtime dependencies):

| Package | Old | New | Note |
|---|---|---|---|
| `phpdocumentor/reflection-docblock` | 5.6.6 | 6.0.3 | Dev-only; used by `ta-tikoma/phpunit-architecture-test` |
| `phpdocumentor/type-resolver` | 1.12.0 | 2.1.0 | Dev-only; transitive dep of reflection-docblock |

No major version jumps in production (`packages`) dependencies.  
**Package repo (`LiencesInstall_VerifyPackage`):** no `composer.lock` changes in this branch.

---

## 8. Backup Directory Inventory — `D:\ProjectCorevisys_BACKUP_2026-10-01`

| File | Present |
|---|---|
| `app.zip` | **NOT FOUND** |
| `bootstrap.zip` | **NOT FOUND** |
| `corevisys-license-cpanel.zip` | **NOT FOUND** |
| `database.zip` | **NOT FOUND** |

The backup directory contains two subdirectories (`LiencesSite/`, `LiencesInstall_VerifyPackage/`) — uncompressed source trees only. No zip archives exist anywhere under `D:\ProjectCorevisys_BACKUP_2026-10-01` or `D:\ProjectCorevisys`. Nothing was deleted.

---

## 9. Phase 10a — Production Readiness (2026-10-03)

### New Commits

| SHA | Description |
|-----|-------------|
| `0a7fea8` | `fix(0a): revert MustVerifyEmail contract; use @var docblock for PHPStan null guard; remove stale baseline entries; add unverified-access test` |
| `1d27f79` | `fix(prod): guard UserSeeder from production; secure session cookie default; add trusted_proxies config key; build-release.ps1 script` |
| `63e7806` | `docs(audit): write AUDIT/16_production_readiness.md (Phase 10a)` |

### Suite Counts (after 10a)

| Repo | Tests | Assertions |
|------|-------|------------|
| LiencesSite | 356 | 1330 |
| LiencesInstall_VerifyPackage | 408 | 984 |

---

## 10. Phase 10b — Secret Classification + Production Readiness Fixes (2026-10-04)

### New Commits

| SHA | Description |
|-----|-------------|
| `e73fffc` | `fix(10b): fingerprint_grace_mode default false; bKash sandbox off in prod; gateway warning in install; build-release --no-scripts fix; FingerprintTest 3 new tests` |

### Changes

| Item | File(s) | Description |
|------|---------|-------------|
| **1** | `AUDIT/17_secret_inventory.md` (new) | Full secret scan of 90 commits + 2 dangling. Real credentials found: APP_KEY + RSA key pair + MAIL_PASSWORD in commit `9025665` on `origin/main`. Rotation list included. |
| **2** | `scripts/build-release.ps1` | Removed bootstrap/cache copy step (packages.php/services.php). Zip now contains NO cache files. Added cleanup loop for stale cache files. Fixed ASCII encoding (removed Unicode em-dashes). Verified: 42.6 MB, 10711 entries, cache_runtime_files=0, env_files=0, sql_files=0, test_files=0. Throwaway boot proof: package:discover, artisan about, route:list all succeed with test keys. |
| **3** | `config/services.php` | `fingerprint_grace_mode` default changed from `true` to `false` (enforced from day one) |
| **3** | `tests/Feature/FingerprintTest.php` | Added 3 tests: `test_fingerprint_grace_mode_default_is_false`, `test_fingerprint_grace_mode_explicit_true_with_future_deadline_activates_grace`, `test_fingerprint_grace_mode_explicit_true_with_past_deadline_enforces` |
| **4** | `AUDIT/16_production_readiness.md` | 6 doc fixes: signing key format (RSA base64, not EC); smoke test 7.5 uses `X-API-Version` header + expects 403 `invalid_license_key`; deploy sequence split into first-deploy (with `corevisys:install`) and subsequent-deploy (with old-code delete + `package:discover` + `optimize:clear`); queue cron uses `--max-time=50`; FINGERPRINT_GRACE_MODE default updated to false. |
| **5** | `LiencesInstall_VerifyPackage/composer.json` | Added `guzzlehttp/guzzle: ^7.8` to `require-dev` |
| **6** | `database/seeders/SystemSettingsSeeder.php` | `gateway_bkash_sandbox` defaults `0` when `APP_ENV=production`, `1` otherwise |
| **6** | `app/Console/Commands/CorevisysInstallCommand.php` | Payment gateway status block printed after seeding: lists Stripe/bKash mode; error if bKash is sandbox |

### Suite Counts (after 10b)

| Repo | Tests | Assertions |
|------|-------|------------|
| LiencesSite | 359 | 1334 |
| LiencesInstall_VerifyPackage | 408 | 984 |

