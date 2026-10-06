# Manual Production Key & Secret Rotation Guide

**Operating Environment:** Windows / XAMPP & Production Linux CLI  
**Scope:** `LiencesSite` (Server-side key generation, rotation, revocation, and re-encryption)

This runbook outlines the exact environment variables, commands, and operational procedures required to rotate license signing keys, application encryption keys (`APP_KEY`), and database HMAC peppers in production.

---

## 1. Environment Variable Summary

| Variable Name | Purpose | Rotation Action |
|---|---|---|
| `LICENSE_SIGNING_KEY_ID` | Identifier of the active signing key | Set to the new key ID (e.g. `corevisys-key-20261002`) |
| `LICENSE_SIGNING_PRIVATE_KEY` | Base64-encoded PEM of the active RSA private key | Replace with newly generated private key |
| `LICENSE_SIGNING_PUBLIC_KEY` | Base64-encoded PEM of the active RSA public key | Replace with newly generated public key |
| `LICENSE_SIGNING_PUBLIC_KEYS` | JSON map of `{ "key_id": "base64-pem", ... }` of **non-revoked** accepted keys | Do NOT include compromised/retired keys |
| `LICENSE_SIGNING_REVOKED_KEY_IDS` | Comma-separated string of revoked key IDs | Add compromised/retired key IDs here immediately |
| `APP_KEY` | Laravel primary encryption key (`base64:...`) | Set to newly generated 32-byte base64 key |
| `APP_PREVIOUS_KEYS` | Comma-separated list of previous encryption keys | **Temporary only** — remove once all columns are re-encrypted under the new `APP_KEY` |
| `LICENSE_PEPPER` | HMAC-SHA256 secret salt for `licenses.lookup_hash` | Rotate safely before launch, or via `license:reset-lookup-hashes` + `license:migrate-legacy-keys` on populated DB |

---

## 2. Security Policy: Immediate Key Revocation — NO Overlap Period

> [!IMPORTANT]
> When rotating a compromised or retiring signing key, **do NOT grant any overlap period**.
> The old key ID is revoked in the **exact same deployment** that activates the new key.
> The old public key is **not** kept in `LICENSE_SIGNING_PUBLIC_KEYS`.
> The package client rejects any signature produced by a key ID listed in `LICENSE_SIGNING_REVOKED_KEY_IDS`
> across all verification paths (online check, cached fast path, and offline grace).

In the same `.env` update:
1. Set the new active key ID and RSA keypair.
2. Add the old key ID to `LICENSE_SIGNING_REVOKED_KEY_IDS`.
3. Do **not** retain the old public key in `LICENSE_SIGNING_PUBLIC_KEYS`.

---

## 3. Step-by-Step Production Rotation Procedure

### Phase A: Generate New RSA Key Pair
Run the artisan generator from the Windows/XAMPP PowerShell or production shell:
```bash
php artisan license:generate-keys --key-id=corevisys-key-20261002 --bits=2048 --force
```
The `--key-id` value is a literal string you choose. **Do not use shell date interpolation** (`$(date ...)`) — this project runs on Windows/XAMPP where bash date substitution is unavailable. Pick a meaningful static identifier (e.g. the rotation date in `YYYYMMDD` format).

Copy the generated Base64 private key, public key, and key ID from the output.

### Phase B: Deploy Key Update & Immediate Revocation
Update `.env` in the server environment:
```env
LICENSE_SIGNING_KEY_ID=corevisys-key-20261002
LICENSE_SIGNING_PRIVATE_KEY="<new-base64-private-key>"
LICENSE_SIGNING_PUBLIC_KEY="<new-base64-public-key>"
LICENSE_SIGNING_REVOKED_KEY_IDS=corevisys-key-1
```
The old key is revoked immediately. Do **not** add it to `LICENSE_SIGNING_PUBLIC_KEYS`.

### Phase C: Clear Caches After `.env` Update
After editing `.env`, always flush cached config and application state before verifying:
```bash
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```
Skipping `cache:clear` can leave stale public-key cache entries that cause verification failures even after the correct key is deployed.

### Phase D: Rotate Laravel `APP_KEY`
Laravel supports key rotation without breaking existing encrypted data via `APP_PREVIOUS_KEYS`.

> [!IMPORTANT]
> `APP_PREVIOUS_KEYS` is a **temporary** migration aid only. Once all encrypted columns have been
> re-encrypted under the new `APP_KEY`, remove `APP_PREVIOUS_KEYS` from `.env` entirely.
> Leaving it indefinitely means the old compromised key remains permanently accepted.

1. **If the database contains NO real production licenses yet (Fresh Launch):**
   Simply generate a brand-new `APP_KEY`:
   ```bash
   php artisan key:generate
   ```
   No `APP_PREVIOUS_KEYS` is needed.

2. **If the database contains EXISTING encrypted licenses (`licenses.key_encrypted`, etc.):**
   - Copy your current `APP_KEY` value.
   - Generate a new key string (Windows PowerShell):
     ```powershell
     php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
     ```
   - Temporarily configure `.env`:
     ```env
     APP_KEY=<new-base64-key>
     APP_PREVIOUS_KEYS=<old-base64-key>
     ```
   - Flush config cache, then re-encrypt existing encrypted columns:
     ```bash
     php artisan config:clear
     php artisan config:cache
     php artisan license:migrate-legacy-keys
     ```
   - Verify all rows are updated, then **remove `APP_PREVIOUS_KEYS`** from `.env` and flush cache again:
     ```bash
     php artisan config:clear
     php artisan config:cache
     ```

### Phase E: `LICENSE_PEPPER` Analysis and Rotation

#### Audit Finding — Pepper History in Git
A repository-wide `git log -S "LICENSE_PEPPER"` audit confirms:
- `LICENSE_PEPPER` appears in exactly **one commit** (`3450c93`) where it was added to `.env.example` as a blank placeholder (`LICENSE_PEPPER=`).
- **No production pepper value was ever committed** to git history, `.env.production.example`, or any tracked file.
- Risk: **None** from git history exposure.

#### Rotation Rules
- **Fresh Launch / No Real Licenses:** If no real production licenses exist yet, regenerating `LICENSE_PEPPER` is completely safe and strongly recommended before launch (Windows PowerShell):
  ```powershell
  php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
  ```
  Set this 64-character hex value in `.env` as `LICENSE_PEPPER=<value>`.

- **Populated Database with Real Licenses:** If licenses already exist, changing `LICENSE_PEPPER` directly will invalidate existing `lookup_hash` values and cause lookups to return 404. Conduct rotation during a maintenance window:
  1. Run the reset command (command exists in `app/Console/Commands/ResetLookupHashes.php`):
     ```bash
     php artisan license:reset-lookup-hashes --force
     # Prompt: Type "RESET" to confirm resetting lookup_hash for all licenses:
     # Input: RESET
     ```
     The command shows a preflight assessment of how many rows have plaintext keys (recomputable offline) vs. how many will rely on lazy client-driven rehash.
  2. Update `LICENSE_PEPPER` in `.env` with the newly generated 64-character hex secret.
  3. Flush config cache:
     ```bash
     php artisan config:clear
     php artisan config:cache
     ```
  4. Recompute lookup hashes for all licenses that have a stored plaintext key:
     ```bash
     php artisan license:migrate-legacy-keys
     ```
     Licenses without a stored plaintext key are recovered lazily on the next client request.

### Phase F: Final Cache Clear and Verification
Execute cache clearing to ensure the server flushes cached public keys, configuration, and settings:
```bash
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

Verify that the server serves the new public key and lists the revoked key ID:
```bash
curl -s https://license.corevisys.com/api/v1/license/public-key
```
Verify JSON response contains:
- `"key_id": "corevisys-key-20261002"`
- `"revoked_key_ids": ["corevisys-key-1"]`
