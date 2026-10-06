# CoreVisys Deployment & Operations Guide

## Production Deployment Order (Strict Runbook)

When deploying updates (especially the performance and license hashing remediation), execute steps in this exact sequential order:

```text
1. set LICENSE_PEPPER (secret store/.env)
   ↓
2. config:cache
   ↓
3. db:check-unique-prerequisites
   ↓
4. migrate --force
   ↓
5. deploy code
   ↓
6. license:migrate-legacy-keys
```

### Critical Note on `LICENSE_PEPPER`
> [!CAUTION]
> **Deploying application code without configuring `LICENSE_PEPPER` makes EVERY license check fail.**
> 
> The application uses an HMAC-SHA256 lookup hash (`lookup_hash`) salted with `LICENSE_PEPPER` for O(1) indexed license lookups. `LicenseService::getLicensePepper()` enforces that `LICENSE_PEPPER` is present and non-empty. If code is deployed before the pepper is provisioned in the secret store and cached in configuration, any incoming license verification, activation, or heartbeat pulse will immediately throw a `RuntimeException` and return a 500 error to clients.

### Step-by-Step Deployment Commands

1. **Set `LICENSE_PEPPER` in secret store / `.env`:**
   Generate a high-entropy string (at least 32 characters / 256 bits):
   ```bash
   # Add to production .env or secret manager:
   LICENSE_PEPPER=your-long-high-entropy-random-secret-key-here
   ```

2. **Warm configuration cache:**
   ```bash
   php artisan config:cache
   ```

3. **Run pre-flight check for unique constraints:**
   ```bash
   php artisan db:check-unique-prerequisites
   ```
   *If duplicates are reported in `payments.receipt_hash` or `processed_webhooks (gateway, event_id)`, resolve them manually before proceeding. Migrations will abort if duplicates exist.*

4. **Run database migrations:**
   ```bash
   php artisan migrate --force
   ```

5. **Deploy application code:**
   Deploy the latest release artifacts to the application web servers and restart PHP-FPM / queue workers:
   ```bash
   php artisan queue:restart
   ```

6. **Migrate legacy keys / backfill lookup hashes:**
   First preview with a dry-run:
   ```bash
   php artisan license:migrate-legacy-keys --dry-run
   ```
   Then apply backfill:
   ```bash
   php artisan license:migrate-legacy-keys
   ```

---

## Pepper Rotation Procedure (Maintenance Window Required)

Pepper rotation changes the HMAC secret used to generate `lookup_hash`. Because this invalidates all indexed lookups, it **must** be conducted during a scheduled maintenance window.

### Which Licenses Can and Cannot Be Recomputed Offline

- **Can be recomputed offline**: Only legacy licenses that have a plaintext key stored in the `license_key` column (`license_key IS NOT NULL`). The `license:migrate-legacy-keys` command will iterate through these rows, compute the new HMAC-SHA256 with the new pepper, and populate `lookup_hash` immediately.
- **Cannot be recomputed offline**: Modern licenses where plaintext keys are not stored (only salted `license_key_hash` + `secret_salt` or `key_encrypted` are stored, with `license_key` being NULL). Because the original key is never stored in plaintext, the new HMAC cannot be precomputed in bulk offline.
- **What happens to the rest**: After running `license:reset-lookup-hashes`, their `lookup_hash` remains `NULL`. They are **not lost**. Instead, they will be lazily recovered: when the client application next performs an activation, pulse check, or verification request, the server will fall back to salted verification against remaining `NULL` lookup rows (protected by per-IP fallback rate limiting). Upon successful match, the new `lookup_hash` is computed from the incoming key and persisted, removing that license from future fallback scans.

### Maintenance Window Steps

1. **Schedule window & activate maintenance mode:**
   ```bash
   php artisan down --message="CoreVisys is undergoing scheduled maintenance. Please check back shortly."
   ```

2. **Set the new `LICENSE_PEPPER` in the secret manager / `.env`:**
   Update the secret store with the newly generated pepper string.

3. **Rebuild config cache:**
   ```bash
   php artisan config:cache
   ```

4. **Reset existing lookup hashes:**
   Run the reset command. The command runs a preflight assessment displaying how many rows have plaintext keys vs. how many will rely on lazy fallback, and requires `--force` plus typing `RESET`:
   ```bash
   php artisan license:reset-lookup-hashes --force
   # Prompt: Type "RESET" to confirm resetting lookup_hash for all licenses:
   # Input: RESET
   ```

5. **Backfill recomputable licenses:**
   Run the backfill command to update all rows that contain plaintext keys:
   ```bash
   php artisan license:migrate-legacy-keys
   ```

6. **Bring application back online:**
   ```bash
   php artisan up
   ```

### Security & Operations Note: `key_encrypted` and `APP_KEY`

> [!WARNING]
> **Key Security & Rotation Constraints:**
> - **Encryption Mechanism:** The `key_encrypted` column on the `licenses` table uses Laravel's `'encrypted'` model cast (AES-256-CBC / AES-128-CBC keyed by `APP_KEY`).
> - **Compromise Exposure:** Because this is symmetric two-way encryption, an `APP_KEY` compromise exposes all stored license keys in `key_encrypted` to any party with database read access.
> - **APP_KEY Rotation Risk:** Rotating `APP_KEY` without preserving old keys in `APP_PREVIOUS_KEYS` or running an explicit re-encryption migration will cause `DecryptException` when accessing `key_encrypted`, permanently breaking both bulk recovery via `license:migrate-legacy-keys` and lazy backfilling.
> - **Design Recommendation:** The dual-storage model (`lookup_hash` for index speed + `key_encrypted` for bulk maintenance recomputation) is standard when administrative key recovery is required. If absolute zero-knowledge security is required in a future architectural phase, `key_encrypted` should be removed in favor of purely irreversible salted hashes, accepting that pepper rotation can then only be handled via lazy client-driven rehash.

---

## Reverse Proxy & Client IP Configuration (TrustProxies)

The application handles client IP resolution behind load balancers and reverse proxies (AWS ALB, Cloudflare, Nginx, cPanel):

- **Middleware Configuration**: Configured in `bootstrap/app.php` via `$middleware->trustProxies()`.
- **Environment Variable**: `TRUSTED_PROXIES` — **required in production** (comma-delimited CIDRs/IPs like `10.0.0.0/8,172.16.0.0/12,192.168.0.0/16`, or `'*'` if the network layer handles spoofing). When unset the application defaults to **trusting no proxies** (`$proxies = []`), which means `$request->ip()` returns the load-balancer's internal socket IP rather than the real client IP. A boot-time `Log::error` is emitted on production and staging environments when `TRUSTED_PROXIES` is missing.
- **Fallback Rate Limit Settings**:
  - `LICENSE_FALLBACK_RATE_LIMIT` (default: 30 attempts)
  - `LICENSE_FALLBACK_RATE_LIMIT_WINDOW` (default: 60 seconds)
- **Shared Cache Store**:
  In multi-server production environments, configure a shared cache driver (`redis` or `memcached`). If `CACHE_STORE` is set to `array` or `file` in production, `LicenseService` logs a warning because per-IP counters cannot be coordinated across server instances.

---

## Production Environment Checklist

1. Copy `.env.example` to `.env` and configure real credentials.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.example`.
3. Set `APP_KEY`, `APP_PREVIOUS_KEYS` (if rotating), and `LICENSE_PEPPER`.
4. Configure database (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
5. Configure shared cache (`CACHE_STORE=redis`) and queue (`QUEUE_CONNECTION=database` or `redis`).
6. Configure signing keys: `LICENSE_SIGNING_PRIVATE_KEY`, `LICENSE_SIGNING_PUBLIC_KEY`, `LICENSE_SIGNING_KEY_ID`.
7. Configure Stripe and bKash webhooks and secrets.
8. Start queue workers: `php artisan queue:work database --tries=3 --backoff=60`
9. Configure crontab for scheduled tasks (`* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`).
10. **⚠ Set `TRUSTED_PROXIES` to your load-balancer CIDRs (or `'*'` if the network layer handles spoofing)**. If unset, the application logs a boot-time `Log::error` on production/staging and all clients behind the load-balancer share a single fallback-scan rate-limit bucket after as few as 30 scans.
11. **Destructive Command Protection:** `AppServiceProvider::boot()` registers `DB::prohibitDestructiveCommands($this->app->isProduction())`. When `APP_ENV=production`, table-destructive commands (`migrate:fresh`, `migrate:refresh`, `migrate:reset`, `migrate:rollback`, and `db:wipe`) are strictly blocked at the framework level to prevent accidental production database loss.
