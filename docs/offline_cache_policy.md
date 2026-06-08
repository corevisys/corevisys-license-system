# Client-Side Offline License Cache Policy

To ensure high availability and application resilience, the License System supports offline validation logic. This document outlines how client applications should implement caching and offline checks.

## 1. Activation Response
When a client activates a license (`POST /api/v1/license/activate`), the server responds with:

```json
{
    "status": "success",
    "data": {
        "license_status": "active",
        "type": "full",
        "expires_at": "2026-12-31T23:59:59Z",
        "signature": "hmac_sha256_string",
        "offline_valid_until": "2026-01-09T10:00:00Z"
    }
}
```

**Headers:**
`Cache-Control: private, max-age=3600` (Recommend caching for 1 hour).

## 2. Client Implementation Logic

### On Success (Online)
1.  Verify the response status is `success`.
2.  Store the entire `data` object securely (e.g., Encrypted Local Storage, Keychain).
3.  Update the application state to `Active`.

### On Network Failure (Offline)
1.  Retrieve the stored license data.
2.  Check if `data` exists.
3.  **Validate Timestamp**: Check if `Current Time < offline_valid_until`.
    *   **If Valid**: Allow access to the application (Offline Mode).
    *   **If Invalid**: Block access and prompt user to reconnect to internet.

### 3. Security Note on Signature
The `signature` field is generated using a server-side secret (`app.key`). 
*   **Symmetric Logic**: Currently, the client cannot cryptographically verify the signature without exposing the secret key (which is insecure). 
*   **Trust Model**: The client trusts the `offline_valid_until` timestamp *provided that the local storage is secure*.
*   **Future Upgrade**: For stricter security, an Asymmetric Key Pair (RSA/Ed25519) can be implemented, allowing the client to verify the signature using a Public Key embedded in the application.

## 4. Recommended Check Interval
Clients should attempt to validate the license against the server (`POST /api/v1/license/check`) every time the application starts, or at least once every 24 hours. The `offline_valid_until` provides a 24-hour buffer for these checks.
