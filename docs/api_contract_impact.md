# Cumulative API Contract Impact & Migration Guide (Rounds 1–5)

This document tracks all architectural, performance, and reliability changes made across the Corevisys License System remediation (Rounds 1 through 5), assessing contract impact, breaking risk, client compatibility, and operational requirements.

---

## 1. Public Contract & Behavioral Scope

While endpoint URLs, parameters, and successful response shapes were strictly preserved, three specific behavioral and operational changes apply:

> [!IMPORTANT]
> **Conditional & Additive Changes:**
> 1. **Activation Limit Enforcement:** The activation limiter now counts **distinct requested domains** rather than total historical log rows. Reactivations from the same domain no longer burn activation slots.
> 2. **License History Default Cap:** `POST /api/v1/license/history` now caps unpaginated responses at **100 rows** (previously unbounded). Optional pagination query/body parameters (`page`, `per_page`) and HTTP response headers (`X-Total-Count`, `X-Page`, `X-Per-Page`, `X-Total-Pages`) are available.
> 3. **Reverse Proxy Configuration (`TRUSTED_PROXIES`):** In production/staging, `TRUSTED_PROXIES` must be configured in `.env`. By default, no proxies are trusted (`[]`), preventing client IP spoofing while isolating fallback rate-limiting buckets.

---

## 2. Cumulative Remediation Matrix (Rounds 1–5)

| # | Feature / Change | Endpoint & Files | Pre-Existing / Original Behavior | Remediated Behavior | Contract Impact |
|---|------------------|------------------|----------------------------------|----------------------|-----------------|
| **1** | **Deterministic License Lookup (`lookup_hash`)** | `POST /api/v1/license/activate`<br>`POST /api/v1/license/check`<br>`POST /api/v1/license/pulse`<br>[LicenseService.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Services/LicenseService.php) | Full-table scan in PHP decrypting every row's salt/hash. | Indexed $O(1)$ query via HMAC-SHA256(`license_key`, `pepper`). Falls back and lazy-backfills legacy rows. | **None** (Transparent internal optimization) |
| **2** | **Activation Limit Domain Counting** | `POST /api/v1/license/activate`<br>[LicenseService.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Services/LicenseService.php) | Counted raw rows in `license_activations`, penalizing legitimate server restarts or reactivations. | Counts `COUNT(DISTINCT request_domain)`. Reactivating the same domain does not exhaust limits. | **Conditional** (Bug fix; requests previously blocked may now succeed) |
| **3** | **License History Endpoint Cap & Pagination** | `POST /api/v1/license/history`<br>[LicenseController.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Http/Controllers/Api/V1/LicenseController.php) | Loaded all historical rows into memory. No pagination support. | Capped at 100 rows by default. Clients passing `page` and `per_page` receive pagination slice and `X-Total-Count` headers. | **Conditional / Additive** (Cap applies only if client requested >100 rows without pagination) |
| **4** | **System Settings Caching & Missing Key Sentinel** | Multiple endpoints / middleware<br>[SystemSetting.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Models/SystemSetting.php) | Repeated `SELECT * FROM system_settings WHERE key = ?` on every request. | Cached via `SystemSetting::getCached()`. Missing/null keys cache sentinel `__COREVISYS_SETTING_MISSING__` to eliminate query stampedes. | **None** (Transparent internal optimization) |
| **5** | **Webhook Idempotency & Transaction Safety** | `POST /api/v1/webhooks/{gateway}`<br>[WebhookController.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Http/Controllers/Api/V1/WebhookController.php) | Unique `(gateway, event_id)` existed on `processed_webhooks`, but inserts happened outside transaction or swallowed business errors. | `insertOrIgnore` with existence check and business logic in a single `DB::transaction`. Exceptions propagate untouched so gateway retries. Duplicate returns `200 {"message": "Already Processed"}`. | **None** (Preserves exact duplicate response and retry semantics) |
| **6** | **Receipt Upload Deduplication (`receipt_hash`)** | `POST /api/v1/orders/{id}/upload-receipt`<br>[OrderController.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Http/Controllers/Api/V1/OrderController.php) | Allowed identical receipts to be repeatedly attached to pending orders. | Enforces unique `receipt_hash` (SHA-256). Duplicate uploads return `400` with `{"status": false, "message": "This receipt has already been submitted."}`. | **Conditional** (Identical receipt re-upload returns 400 instead of succeeding) |
| **7** | **Rate Limiter Client IP / Trusted Proxies** | `app/Http/Middleware`<br>[bootstrap/app.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/bootstrap/app.php)<br>[LicenseService.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Services/LicenseService.php) | Trusted all proxies (`*`), allowing IP spoofing via `X-Forwarded-For`. CLI/queue passed `127.0.0.1`. | Default `TRUSTED_PROXIES=[]`. CLI/queue passes `null` IP (skips IP bucket). Boot logs `Log::error` if unset in production. | **Conditional** (Ops must specify reverse proxy CIDRs in `.env`) |
| **8** | **Early Database Protection Guard** | [tests/TestCase.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/tests/TestCase.php) | Ran after `parent::setUp()`, allowing `RefreshDatabase` to wipe tables before aborting. | Runs in `createApplication()` before traits boot. Restricts non-SQLite databases strictly to `*_test` suffix. Throws `RuntimeException`. | **None** (Test harness safety only) |
| **9** | **Bulk Pepper Recovery via `key_encrypted`** | `php artisan license:migrate-legacy-keys`<br>[MigrateLegacyLicenseKeys.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Console/Commands/MigrateLegacyLicenseKeys.php) | Only inspected legacy plaintext `license_key` column. | Decrypts `key_encrypted` using `APP_KEY` to recalculate `lookup_hash` in bulk during pepper rotation. | **None** (CLI maintenance command) |
| **10** | **Webhook `event_id` Input Validation** | `POST /api/v1/webhooks/stripe`<br>[WebhookController.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Http/Controllers/Api/V1/WebhookController.php) | Untyped `event_id` allowed empty strings or >255 chars to reach `insertOrIgnore`. | Validates `event_id` is a non-empty string <= 255 chars before attempting insert. Missing/invalid values return pre-remediation 400 `{"message": "Invalid Payload"}` without touching DB. | **None** (Standard invalid-payload error) |
| **11** | **Destructive Commands Prohibition in Production** | `AppServiceProvider::boot()`<br>[AppServiceProvider.php](file:///e:/Corevisys/htdocs/CoreVisys%20License%20Project/app/Providers/AppServiceProvider.php) | Destructive Artisan commands (`migrate:fresh`, `db:wipe`, etc.) could be executed if unflagged. | Registers `DB::prohibitDestructiveCommands($this->app->isProduction())` blocking table destruction in production. | **None** (Production environment safety) |

---

## 3. Route & Parameter Verification Note

- **History Endpoint Specification:**
  - **Method:** `POST`
  - **URI:** `/api/v1/license/history` (singular `/license`, not `/licenses`)
  - **License Key Parameter:** Sent in the **POST body / JSON payload** (`{"license_key": "..."}`), **NEVER in the URL path or query string**.
  - All public license endpoints (`/license/activate`, `/license/check`, `/license/pulse`, `/license/history`) pass sensitive keys in the POST request body.
- **Receipt Upload Response:**
  - Duplicate receipt rejection preserves the original status and payload:
    ```json
    HTTP/1.1 400 Bad Request
    {
      "status": false,
      "message": "This receipt has already been submitted."
    }
    ```

---

## 4. Test Suite Progression Across Rounds

| Milestone / Round | Test Count | Pass Rate | Additions / Key Focus Areas |
|-------------------|------------|-----------|-----------------------------|
| **Baseline (Pre-Remediation)** | **129 passed** | 100% | Existing test suite before remediation began |
| **Round 1 Completion** | **140 passed** (+11) | 100% | Lookup hash indexing, base activation, payment pipelines |
| **Round 2 Completion** | **153 passed, 1 skipped** (+13 passed) | 99.4% | Webhook transaction handling, receipt hash uniqueness, client versioning |
| **Round 3 Completion** | **156 passed, 0 skipped** (+3 passed) | 100% | Fallback rate limiting, deploy checklist verification, system setting caching, skipped test fixed |
| **Round 4 Hardening** | **172 passed** (+16) | 100% | MySQL `INSERT IGNORE` existence guard, sentinel caching, TestCase guard, proxy security, pepper categories |
| **Round 5 Completion** | **173 passed** (+1) | 100% | Early TestCase guard in `createApplication()`, bulk `key_encrypted` pepper recovery command & test |
| **Final Hardening** | **177 passed** (+4) | 100% | Pre-insert `event_id` validation (null, empty, >255 chars), `DB::prohibitDestructiveCommands` in production |

---

## 5. Reconciled List of Tests Added (48 Total Since Baseline)

### Round 1 (+11 Tests)
1. `test_find_by_key_resolves_new_license_via_lookup_hash`
2. `test_find_by_key_falls_back_and_backfills_legacy_license_with_null_lookup_hash`
3. `test_find_by_key_returns_null_for_wrong_key`
4. `test_process_renewals_queries_product_prices_in_batch_outside_loop`
5. `test_activate_counts_distinct_request_domain_correctly`
6. `test_public_key_error_path_is_not_cached_and_succeeds_after_key_becomes_available`
7. `test_public_key_is_cached_on_success`
8. `test_system_setting_cache_invalidation_on_save_and_delete`
9. `test_history_default_returns_flat_array_capped_at_100`
10. `test_history_pagination_parameters_return_slice_and_headers`
11. `test_processed_webhooks_has_no_external_id_column`

### Round 2 (+13 Passed, 1 Skipped Tests)
12. `test_webhook_business_logic_failure_rolls_back_processed_webhook_row`
13. `test_webhook_logic_exception_leaves_no_processed_row_and_retry_succeeds`
14. `test_webhook_true_duplicate_returns_already_processed`
15. `test_duplicate_receipt_upload_returns_400_via_unique_constraint`
16. `test_receipt_upload_failure_rolls_back_and_retry_succeeds`
17. `test_find_by_key_skips_fallback_entirely_when_no_null_lookup_hash_rows_exist`
18. `test_find_by_key_returns_null_when_fallback_rate_limit_exceeded`
19. `test_find_by_key_legacy_within_rate_limit_finds_and_backfills`
20. `test_get_license_pepper_throws_when_pepper_is_missing`
21. `test_get_license_pepper_throws_when_pepper_is_empty_string`
22. `test_find_by_key_throws_when_pepper_is_missing`
23. `test_check_unique_prerequisites_passes_when_no_duplicates`
24. `test_check_unique_prerequisites_fails_when_duplicate_receipt_hash_exists`

### Round 3 (+3 Passed, Skipped Resolved -> 156 Total)
25. `test_check_unique_prerequisites_fails_when_duplicate_gateway_event_id_exists`
26. `test_reset_lookup_hashes_requires_force_flag_and_typed_confirmation`
27. `test_migrate_legacy_keys_reports_backfilled_and_remaining_null_counts`

### Round 4 (+16 Tests)
28. `test_webhook_generic_exception_in_logic_rolls_back_and_retry_succeeds`
29. `test_webhook_unique_violation_on_different_table_not_swallowed`
30. `test_webhook_true_duplicate_returns_200_already_processed`
31. `test_webhook_suppressed_insert_existence_check_guard_logic`
32. `test_receipt_upload_receipt_hash_violation_returns_400`
33. `test_receipt_upload_unrelated_unique_violation_is_rethrown_by_audit`
34. `test_untrusted_xff_not_used_for_rate_limit`
35. `test_trusted_proxy_xff_is_used_for_rate_limit`
36. `test_findbykey_null_ip_skips_rate_limit_counter`
37. `test_get_cached_reads_db_once_then_serves_from_cache`
38. `test_get_cached_sentinel_for_missing_key_prevents_repeated_db_queries`
39. `test_get_cached_invalidated_on_model_update`
40. `test_get_cached_falls_back_when_cache_unavailable`
41. `test_testcase_guard_throws_runtime_exception_for_non_test_database`
42. `test_license_with_key_encrypted_can_recover_lookup_hash_after_rotation`
43. `test_pepper_rotation_recovery_category_counts`

### Round 5 & Final Hardening (+5 Tests -> 177 Total)
44. `test_license_with_key_encrypted_is_recovered_in_bulk_under_new_pepper`
45. `test_webhook_null_event_id_creates_no_row_and_returns_invalid_payload`
46. `test_webhook_empty_string_event_id_creates_no_row_and_returns_invalid_payload`
47. `test_webhook_over_long_event_id_creates_no_row_and_returns_invalid_payload`
48. `test_destructive_database_commands_are_prohibited_in_production`

---

## 6. Pepper Rotation & License Data Categories

| Category | Definition | Current Local DB Row Count | Recovery Path on Rotation |
|----------|------------|----------------------------|---------------------------|
| **Category A** | `lookup_hash` NOT NULL<br>`key_encrypted` NOT NULL | **0 rows** (local DB wiped by earlier test run) | Bulk recomputation via `license:migrate-legacy-keys` after pepper rotation |
| **Category B** | `lookup_hash` NULL<br>`key_encrypted` NOT NULL | **0 rows** (local DB wiped by earlier test run) | Bulk recomputation via `license:migrate-legacy-keys` OR lazy recovery on next client request |
| **Category C** | `lookup_hash` NULL<br>`key_encrypted` NULL | **0 rows** (local DB wiped by earlier test run) | Lazy recovery ONLY: re-indexed when client presents raw key |

