# CoreVisys Project Knowledge Base

**Document status:** Refreshed against the current repository state
**Last audited:** 2026-09-20
**Scope:** Laravel application, public license API, reference signing implementation, customer/admin workflows, payments, jobs, operations, tests, and the separate client SDK contract

## 1. Executive summary

CoreVisys is a Laravel 12 license server with a separate Laravel client SDK in the repository root. The server and client are one interoperability contract, not two independently sufficient systems.

The current automated baselines are:

```text
Server: 110 tests passed, 398 assertions
Client: 45 tests passed, 77 assertions
```

These are post-fix counts. Historical 90/281 server and 41/71 client numbers are stale; the increase is due to contract, rotation, revocation, offline-policy, and algorithm-validation coverage.

## 2. Public license API

Base path: `/api/v1`.

- `POST /license/activate`
- `POST /license/check`
- `POST /license/pulse`
- `GET /license/public-key`
- `POST /license/history`

The API applies version enforcement, rate limiting, maintenance-kill behavior, and fail-closed signing behavior.

## 3. Cross-project contract note

The server and the separate client SDK MUST evolve together. Their shared response contract is recorded in [license-response-contract.json](../../tests/Fixtures/license-response-contract.json), which is read and asserted by both [LicenseActivationTest.php](../../tests/Feature/LicenseActivationTest.php) and [OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php).

The canonical signed envelope is:

```text
success, status, message, data, signature, key_id, algorithm
```

Activate/check/pulse data is:

```text
status, license_id, product_code, license_type, expires_at,
features, issued_at, offline_valid_until, is_grace_period
```

The client sends `X-API-Version: 1.0.0` by default. Both sides recursively sort associative payload keys and use identical JSON encoding flags before RSA-SHA256 signing/verification. Any change to response shape, field names, algorithm, key metadata, or offline semantics MUST update both codebases, the shared fixture, and both test suites in the same change.

Only RSA-SHA256 is supported by design. Ed25519 was evaluated and removed to keep one production-verified trust path; it is not a future TODO. `ext-sodium` is not a Composer dependency.

## 4. Server signing and key policy

The server signs canonical `data` using `openssl_sign(..., OPENSSL_ALGO_SHA256)` in [LicenseController.php](../app/Http/Controllers/Api/V1/LicenseController.php#L260-L283). It reports the fixed `RSA-SHA256` label from [OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php#L5-L25); signing is not configurable as a second algorithm path.

The public-key endpoint publishes the active key, available rotation keys, overlap metadata, and revoked key IDs in [LicenseController.php](../app/Http/Controllers/Api/V1/LicenseController.php#L158-L176). The client resolves the response's exact `key_id` and checks refreshed revocation metadata before trusting cached keys.

## 5. Offline policy

The default server offline window is seven days from issuance, configured by [license.php](../config/license.php#L1-L6). Active responses carry `issued_at` and `offline_valid_until`; suspended pulse responses carry `status: suspended` with `offline_valid_until: null`.

The client treats the server-issued boundary as authoritative. Its local `grace_period` setting may shorten that window but never extend it. Offline cache fallback requires both `offline_valid_until` and `expires_at` checks, plus a valid cached signature; `status: active` alone is not trusted.

See [offline_cache_policy.md](offline_cache_policy.md) and [LicenseVerifier.php](../../src/Services/LicenseVerifier.php#L139-L156).

## 6. Verification evidence

A real local server/client run was executed on 2026-09-20. It covered activate -> check -> pulse with real RSA signatures, suspended-license rejection, unknown-key rejection, previously-cached revoked-key rejection, tampered cached payload rejection, and expired offline-boundary rejection. Future contract changes must meet this live round-trip evidence standard before being marked resolved.

## 7. Source of Truth Rules

1. Update the owning code and its focused test.
2. Update this document if the capability, contract, or operational requirement changes.
3. Update [PROJECT_GAP_ANALYSIS.md](PROJECT_GAP_ANALYSIS.md) only when a status change is backed by fresh evidence.
4. Update [offline_cache_policy.md](offline_cache_policy.md) whenever signing, public-key, or revocation behavior changes.
5. Any change affecting the client-server response contract, signing algorithm, or offline semantics must update `license-response-contract.json` and both test suites in the same change, and must be validated with a live local round-trip test before being marked resolved in documentation.

## 8. Remaining operational items

External payment-provider sandbox verification still requires real Stripe/bKash credentials. That is separate from the verified license client/server contract. Production deployment must also provide the RSA signing private key and corresponding public key metadata.
