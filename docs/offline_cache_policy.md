# Client-Side Offline License Cache Policy

**Status:** Implemented and verified in the server application; client-side verification remains an application-level responsibility in the client repo
**Audited:** 2026-09-20

This document describes the current offline authorization protocol as implemented in the server code. Local storage alone is not a trust boundary: a client must validate the signed payload before trusting any cached authorization.

## 1. Canonical payload format and offline validity window

The server canonicalizes payloads using deterministic JSON serialization with stable key ordering and UTF-8 encoding.

### 1.1 Offline validity window and timestamps
- **Default offline window:** 7 days from the last verified check (configurable via `OFFLINE_VALIDITY_DAYS` in `.env`, mapped to `config('license.offline_validity_days')`).
- **Response fields:** Successful calls to `POST /api/v1/license/activate`, `POST /api/v1/license/check`, and active `POST /api/v1/license/pulse` include:
  - `issued_at`: ISO-8601 timestamp when the signature/payload was issued.
  - `offline_valid_until`: ISO-8601 timestamp designating the end of the offline grace grant (`now() + 7 days`).
- **Suspended status behavior:** Suspended licenses received during `POST /api/v1/license/pulse` return `active: false` (or `license_status: "SUSPENDED"`) and intentionally have no `offline_valid_until`. Clients must treat a missing `offline_valid_until` as "no offline use allowed", failing closed immediately while offline.
- **Heartbeat frequency:** Pulse requests are issued by clients approximately once per month. Pulse updates the license `last_check_at` timestamp on the server without creating redundant activation log entries.

Canonicalization rule:

1. Build the payload as an associative array.
2. Sort object keys lexicographically before serialization.
3. Serialize using `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
4. Use the exact resulting JSON string as the signing input.

The current payload fields are defined in the verification helper and test suite. The same canonical payload format is used in offline policy validation:

- [../app/Support/OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php)
- [../tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php)
- [../config/license.php](../config/license.php)

## 2. Public key distribution

The server exposes the active public key and metadata through:

- `GET /api/v1/license/public-key`

The current implementation returns the active key metadata and supports rotation overlap and revocation tracking. This is part of the verified offline policy and is covered in the feature tests.

## 3. Key ID and rotation model

The server supports multiple configured public keys and returns the active key metadata through the public-key endpoint.

Operational rules:

1. `key_id` identifies the key used to sign the current payload.
2. The response includes active key metadata and the available key list.
3. Rotation overlap remains valid for the configured window before the old key is retired.
4. Revoked keys are handled explicitly and must not be accepted as valid signers.

Relevant references:

- [../app/Services/LicenseService.php](../app/Services/LicenseService.php)
- [../app/Support/OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php)
- [../tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php)

## 4. Revocation and compromise handling

Revocation handling is implemented and tested as part of the server policy:

- revoked or missing keys fail closed
- old keys remain usable only during the configured overlap window
- legacy plaintext-only rows are rejected
- clients must reject revoked `key_id` values even if the signature itself is otherwise valid

This is no longer a pending item for the server-side protocol; the server-side logic is implemented and validated.

## 5. Reference client-side verification routine

The sample verification helper is available at:

- [../app/Support/OfflineLicenseVerification.php](../app/Support/OfflineLicenseVerification.php)

The helper includes the canonicalization and signature verification logic used by the tests. A client should enforce:

- reject expired offline payloads
- treat missing `offline_valid_until` as no offline use allowed (fail closed immediately)
- reject unknown or revoked `key_id`
- reject mismatched `client_id`, `license_id`, or `license_type`
- verify the payload before trusting the local cache

## 6. Server failure policy

The server now fails closed:

- missing signing key -> controlled `503`
- invalid private key -> controlled `503`
- signing failure -> controlled `503`
- no successful signature is returned if signing cannot complete

This is verified in the current offline policy tests.

## 7. Tests now covering the protocol

The current suite includes verification for:

- deterministic canonical payload output
- active public-key endpoint metadata
- key rotation overlap behavior
- revoked key rejection
- fail-closed signing behavior
- rejection of legacy plaintext-only license rows

See:

- [../tests/Feature/OfflinePolicyTest.php](../tests/Feature/OfflinePolicyTest.php)

## 8. Implementation status

The following parts are now implemented and verified in the application:

- deterministic canonical payload format
- public-key endpoint and metadata exposure
- key ID support and overlap handling
- revoked-key rejection
- fail-closed server signing behavior
- configurable offline validity window (default 7 days via `config/license.php`) with `offline_valid_until` and `issued_at` timestamps in responses
- monthly client heartbeat (`POST /api/v1/license/pulse`) renewing `offline_valid_until` without activation row pollution
- background scheduler overdue-license reporting (`license:flag-stale` — report-only, never changes DB status)
- automated tests covering the protocol contract

This is no longer listed as pending. The remaining responsibility is external to this repo: the client-side application or SDK must use the published metadata and verify the signature before trusting any offline authorization data.
