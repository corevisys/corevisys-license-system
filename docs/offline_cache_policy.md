# Client-Side Offline License Cache Policy

**Status:** Current contract documented; production protocol is not yet approved
**Audited:** 2026-09-13

This document describes what the server currently returns and the security contract required before offline licensing is used for real commercial enforcement. Local storage alone is not a trust boundary: a client that cannot verify a server signature can modify the cached expiry and grant access indefinitely.

## 1. Current Server Response

`POST /api/v1/license/activate` currently returns a response shaped like this:

```json
{
    "status": "success",
    "data": {
        "license_status": "active",
        "type": "full",
        "license_type": "full",
        "expires_at": "2026-12-31T23:59:59Z",
        "signature": "hmac_sha256_string",
        "offline_valid_until": "2026-01-09T10:00:00Z"
    },
    "payload": "base64(canonical-json)",
    "server_signature": "base64(openssl-sha256-signature)"
}
```

The endpoint also sends:

```text
Cache-Control: max-age=3600, private
```

The `signature` field is an HMAC generated with `app.key`. The `payload` and `server_signature` fields are a separate OpenSSL/RSA-style envelope generated from `LICENSE_SIGNING_PRIVATE_KEY`. They are not interchangeable. The current client contract does not define public-key distribution, key IDs, canonical JSON rules, or cryptographic verification behavior.

## 2. Current Security Limitations

- A client must not treat `offline_valid_until` as trustworthy without verifying `server_signature`.
- If the signing key is missing, invalid, or signing fails, the current server may still return HTTP success with marker values such as `MISSING_KEY`, `INVALID_KEY`, or `SIGNING_FAILED`. This is fail-open behavior and is a production blocker.
- The HMAC cannot be safely verified by an untrusted client because distributing `app.key` would expose the server secret.
- `Cache-Control` controls HTTP caching only; it does not protect application storage from tampering.
- The current feature test checks field presence and headers, not cryptographic verification.

## 3. Required Production Protocol

Before enabling offline enforcement, implement one asymmetric protocol, preferably Ed25519 or RSA-PSS:

1. Server builds a canonical payload with stable field ordering and explicit UTF-8 encoding.
2. Payload includes `license_id`, `license_status`, `license_type`, `expires_at`, `offline_valid_until`, `issued_at`, `key_id`, `client_id` or binding, and a protocol version.
3. Server signs the exact payload with a private key held outside the repository and secret manager access controls.
4. Server returns `payload`, `signature`, `algorithm`, `key_id`, and protocol version. Do not return ambiguous HMAC and asymmetric fields under similar names.
5. Client embeds or securely retrieves the matching public key and verifies the signature before reading any authorization field.
6. Missing, malformed, expired, mismatched, or unverifiable data blocks offline access and triggers an online check.
7. Key rotation supports multiple active public keys by `key_id`; old keys remain available only for a documented overlap period.
8. Revocation behavior is documented. Offline mode cannot instantly revoke a cached license, so the maximum offline window must be an explicit business/security decision.

## 4. Client Behavior

### Online success

1. Require HTTP success and the expected protocol version.
2. Verify the asymmetric signature over the exact decoded payload.
3. Verify the license/client binding and ensure the local clock is within the allowed clock-skew policy.
4. Store the verified payload and metadata in protected storage.
5. Set the local state to active, grace, or blocked according to verified fields.

### Network failure

1. Load the cached payload and signature.
2. Verify the signature again; never trust a previously verified boolean alone.
3. Reject if `now >= offline_valid_until`, the payload is malformed, the binding differs, or the key ID is unsupported.
4. If valid, allow the explicitly limited offline mode and record the last successful online verification.
5. If invalid, block protected functionality and request reconnection.

### Online re-check

Call `POST /api/v1/license/check` at application startup and at least once every 24 hours, with a shorter interval for high-risk products. The server check must apply the same binding, status, expiry, grace, and revocation rules as activation. `check` and `pulse` currently do not enforce fingerprints; this must be resolved by an explicit enforcement-mode contract.

## 5. Server Failure Policy

The server must fail closed for signed responses:

- Missing signing key: return a controlled 5xx and alert operations.
- Invalid private key: return a controlled 5xx and alert operations.
- Signing failure: return a controlled 5xx and do not issue offline authorization data.
- Never use `MISSING_KEY`, `INVALID_KEY`, or `SIGNING_FAILED` as a usable signature.

## 6. Required Tests

- Client accepts a valid signature and rejects one-byte payload changes.
- Client rejects expired `offline_valid_until` and unacceptable clock skew.
- Client rejects an unknown key ID and accepts a rotated key during overlap.
- Server returns non-success when signing configuration is absent or malformed.
- Activation/check/pulse agree on suspended, expired, grace, and revoked behavior.
- Offline cache cannot authorize a different license, product, client binding, or domain.
- Repeated online checks and concurrent activation requests remain idempotent.

## 7. Release Gate

Offline licensing is **not production-approved** until the asymmetric protocol, public-key distribution, fail-closed server behavior, revocation window, client verification implementation, and tests above are complete. Until then, clients should require online validation and should not claim that the current HMAC or local timestamp provides tamper-resistant offline enforcement.
