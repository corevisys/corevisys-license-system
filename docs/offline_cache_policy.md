# Client/Server Offline Cache Policy

**Status:** Implemented and verified across the reference server and client SDK
**Audited:** 2026-09-20

This policy describes the shared offline authorization contract. The server issues signed payloads; the client validates the signature and applies the offline boundary before trusting local state. Neither side's documentation is evidence without the corresponding source and tests.

## 1. Shared signed response contract

The envelope is:

```text
success, status, message, data, signature, key_id, algorithm
```

For activate, check, and pulse, `data` contains:

```text
status, license_id, product_code, license_type, expires_at,
features, issued_at, offline_valid_until, is_grace_period
```

The shared contract fixture is [license-response-contract.json](../../tests/Fixtures/license-response-contract.json). It is asserted by the client test [LicenseActivationTest.php](../../tests/Feature/LicenseActivationTest.php#L14-L22) and the server test [OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php#L16-L24).

Any future contract change must update both codebases, this fixture, and both test suites in one change.

## 2. Canonicalization and signing

Both implementations recursively normalize values, preserve list order, sort associative keys, and serialize with:

```text
JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
```

Server implementation: [OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php#L7-L18) and [OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php#L103-L125).

Client implementation: [LicenseResponse.php](../../src/DTOs/LicenseResponse.php#L61-L64) and [LicenseResponse.php](../../src/DTOs/LicenseResponse.php#L98-L115).

The former shallow-versus-recursive canonicalization mismatch is resolved.

Only RSA-SHA256 is supported. The server uses `openssl_sign(..., OPENSSL_ALGO_SHA256)` and reports the fixed `RSA-SHA256` label. Ed25519 support was evaluated and removed to keep one production-verified trust path. `ext-sodium` is not a package dependency.

## 3. Offline validity and timestamps

The server default is seven days from issuance, configured by `config('license.offline_validity_days')` in [license.php](../config/license.php#L1-L6).

- Active activate/check/pulse payloads include `issued_at` and `offline_valid_until`.
- Suspended pulse payloads include `status: suspended` and `offline_valid_until: null`.
- `expires_at` remains an independent license-expiry boundary.

The client stores the server-issued fields and uses them in cache fallback. It requires both `offline_valid_until` and `expires_at` to permit an active cached record. The local `grace_period` configuration can only shorten the server-issued window and cannot extend it. A cached `status: active` value alone is insufficient.

Client checks: [LicenseVerifier.php](../../src/Services/LicenseVerifier.php#L139-L156), [LicenseVerifier.php](../../src/Services/LicenseVerifier.php#L200-L208), and [LicenseVerifier.php](../../src/Services/LicenseVerifier.php#L221-L236).

## 4. Public keys, rotation, and revocation

The public-key endpoint publishes the active key, available overlapping keys, and revoked IDs in [LicenseController.php](../app/Http/Controllers/Api/V1/LicenseController.php#L158-L176).

The client refreshes and caches the metadata, resolves the response's exact `key_id`, and checks `revoked_key_ids` before trusting a cached key in [SignedPayloadVerifier.php](../../src/Services/SignedPayloadVerifier.php#L145-L187). An unknown or revoked key fails closed, including a key that was previously cached as trusted.

## 5. API version

The client default is semantic version `1.0.0`, configured in [corevisys-license.php](../../config/corevisys-license.php). It sends `X-API-Version: 1.0.0`; the server middleware compares that value with `version_compare()` in [CheckClientVersion.php](../app/Http/Middleware/CheckClientVersion.php#L20-L31).

## 6. Fail-closed behavior

The server returns controlled `503` responses when signing configuration or private-key parsing fails. The client rejects unsigned responses, malformed signatures, unknown/revoked keys, invalid cached signatures, expired offline boundaries, and expired licenses.

## 7. Verification evidence

Current full-suite results from 2026-09-20:

```text
Client: 45 tests passed, 77 assertions
Server: 110 tests passed, 398 assertions
```

The remediation also included a real local HTTP run against a booted server: activate -> check -> pulse, suspended-license rejection, unknown-key rejection, previously-cached revoked-key rejection, tampered cached payload rejection, and expired offline-boundary rejection. This live round-trip is the required evidence standard for future changes to this policy.
