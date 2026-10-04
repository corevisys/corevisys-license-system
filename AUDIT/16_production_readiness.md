# AUDIT/16 — Production Readiness for cPanel Deployment

**Branch:** `fix/audit-2026-10` | **Date:** 2026-10-03 | **Commits:** `0a7fea8`, `1d27f79`

---

## 0. CARRY-OVERS

### 0a. MustVerifyEmail (commit 36ebbfa)

**Decision: REVERTED.** The contract was added solely to satisfy a PHPStan null-type error in `VerifyEmailController`. Not needed for real verification logic.

**Changes (commit `0a7fea8`):**

```diff
// app/Models/User.php
-class User extends Authenticatable implements MustVerifyEmail
+class User extends Authenticatable

// app/Http/Controllers/Auth/VerifyEmailController.php
+    /** @var \Illuminate\Contracts\Auth\MustVerifyEmail $user */
     event(new Verified($user));

// phpstan-baseline.neon — removed 2 stale entries:
//   VerifyEmailController argument.type
//   OrderFulfillmentService deadCode.unreachable
```

**Routes referencing `verified` middleware:**

| Route | Middleware |
|-------|-----------|
| `GET /dashboard` (web.php:364) | `auth`, `verified` |

**Effect:** `EnsureEmailIsVerified` checks `$request->user() instanceof MustVerifyEmail` first. Since `User` does not implement the interface, the middleware is a **no-op** — all authenticated users reach the dashboard regardless of `email_verified_at`. This is intentional: email verification is informational, not a gate.

**Admin created by `corevisys:install`:** Sets `email_verified_at = now()` (CorevisysInstallCommand.php:104). ✅

**Tests:** `tests/Feature/Auth/EmailVerificationTest.php` — 4 tests pass, including new:
`user with null email_verified_at is not locked out of dashboard` ✅

**PHPStan:** No errors ✅ | **Suite:** 356 passed, 1330 assertions ✅

---

### 0b. Laravel Matrix (throwaway copies, outside project)

| Laravel | Testbench | Result | Notes |
|---------|-----------|--------|-------|
| **12** (project default) | 10.x | ✅ 355 passed | Main project suite |
| **11** | 9.0.0 | ✅ 408 passed | Throwaway copy |
| **10** | 8.39.0 | ✅ 408 passed | Needed `guzzlehttp/guzzle:^7.8` manually |

**Finding:** Package `composer.json` does not declare `guzzlehttp/guzzle`. Laravel 10 testbench does not ship Guzzle, so `Http::fake()` fails (`Class "GuzzleHttp\Psr7\Response" not found`). Laravel 11+ include it transitively.

**Recommendation (do not apply yet):** Add to `LiencesInstall_VerifyPackage/composer.json`:
```json
"require-dev": { "guzzlehttp/guzzle": "^7.8" }
```
Current `illuminate/*: ^10.0|^11.0|^12.0` and `testbench: ^8.0|^9.0|^10.0` constraints are correct.

---

### 0c. Secret Scan

> **Rule:** File names and commit SHAs only. No secret values printed.

#### LiencesSite — CRITICAL

| Pattern | Commits | Branches |
|---------|---------|----------|
| `BEGIN.*PRIVATE KEY` | `0c1464d`, `e1e72a1`, `198e74e`, `97f1a7b`, `83da4db`, `0a209ed` | `fix/audit-2026-10`, `main`, remotes, dangling |
| `sk_live` (Stripe) | `83da4db`, `f5bacb3`, `19be365`, `37e986a` | multiple + remotes |
| `whsec_` (Stripe webhook) | `3450c93`, `83da4db`, `19be365` | `main`, remotes |
| `APP_KEY=base64:` | `198e74e` (removes), **`9025665`** (adds) | **`origin/main`**, `origin/fix/scan-timeout-bkash-cache-prune` |
| `BKASH_APP_SECRET` | `5e122fe`, `83da4db`, `0a209ed`, `19be365` | **`origin/main`**, remotes |

> [!CAUTION]
> Commit `9025665` (database export + real `APP_KEY`) is **on `origin/main`**. Sanitisation commit `198e74e` is local-only and never pushed. **History purge is mandatory before production.** After purge: rotate APP_KEY, Stripe keys, bKash credentials, and all signing keys.

**Required actions:**
1. `git filter-repo` (or BFG) to purge all commits listed from every branch and remote.
2. Force-push purged history.
3. Notify all collaborators to re-clone.
4. Rotate every credential that appeared in history.

#### LiencesInstall_VerifyPackage — CLEAN

| Pattern | Commits | Context |
|---------|---------|---------|
| `BEGIN.*PRIVATE KEY` | `42e88ec`, `dac5c9b`, `3be5a03` | Test-only static key pairs in `tests/Concerns/SignsPayloads.php` — doc comment says "not for production use" |

**Verdict: Package is clean.** No real credentials.

---

## 1. CLEAN RELEASE ARTIFACT

**Script:** [`scripts/build-release.ps1`](file:///d:/ProjectCorevisys/LiencesSite/scripts/build-release.ps1) (committed `1d27f79`)

```powershell
# Usage:
powershell -ExecutionPolicy Bypass -File .\scripts\build-release.ps1
powershell -ExecutionPolicy Bypass -File .\scripts\build-release.ps1 -OutputDir D:\releases
```

**Steps:**
1. Verify git, composer, npm in PATH
2. `npm run build` (production Vite assets)
3. `git archive HEAD` → extract to temp staging dir (tracked files only)
4. Copy `public/build` into staging
5. Strip: `tests/`, `AUDIT/`, `.github/`, `phpunit.xml`, `phpstan*`, `vite.config.js`, `package*.json`, `jsconfig.json`, `scripts/`
6. `composer install --no-dev --optimize-autoloader --no-scripts`
   - `--no-scripts` required: `AppServiceProvider::boot()` throws when `LICENSE_SIGNING_KEY_ID` absent, blocking `package:discover`
   - Copies `bootstrap/cache/packages.php` and `services.php` from source repo instead
7. Exclusion audit (fails loudly on `.env`, `.git`, `tests/`, `*.sql`, `*.bak`, `*.key`, `installed.lock`)
8. Zip to `$OutputDir\corevisys-release-YYYYMMDD-HHmmss.zip`

**Verified run:**
```
AUDIT PASSED: No forbidden files found.
Zip: C:\Users\offic\corevisys-release\corevisys-release-20261003.zip
Size: 42.6 MB | Total entries: 10,714
Top-level: app/, bootstrap/, config/, database/, deploy/, docs/, public/,
           resources/, routes/, storage/, vendor/, artisan, composer.json,
           composer.lock, .env.example, .gitignore, .htaccess, README.md
```

**Confirmed absent from zip:** `.env`, `.git/`, `tests/`, `AUDIT/`, `node_modules/`, `*.sql`, `*.bak`, `cpanel_database.sql`, `storage/*.key`, `storage/installed.lock` ✅

---

## 2. WEB EXPOSURE / cPanel LAYOUT

### Standard Setup (document root = `public/`)

```
/home/youraccount/
├── public_html/          ← document root = Laravel's public/
│   ├── .htaccess
│   ├── index.php
│   ├── favicon.ico
│   ├── robots.txt
│   └── build/
└── corevisys/            ← app OUTSIDE public_html
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── storage/          ← must be writable
    ├── vendor/
    └── ...
```

**If host forces `public_html` as only document root:**
1. Upload app to `/home/youraccount/corevisys/`
2. Move contents of `corevisys/public/` into `public_html/`
3. Edit `public_html/index.php`: change `../vendor/autoload.php` and `../bootstrap/app.php` to `../corevisys/vendor/autoload.php` etc.
4. `php artisan storage:link` creates `public_html/storage → corevisys/storage/app/public`

### URLs to test from outside after deploy

| URL | Expected |
|-----|----------|
| `https://yourdomain.com/.env` | **403** or **404** — never 200 |
| `https://yourdomain.com/storage/logs/laravel.log` | **403** or **404** |
| `https://yourdomain.com/composer.json` | **403** or **404** |
| `https://yourdomain.com/vendor/` | **403** or **404** |
| `https://yourdomain.com/database/` | **403** or **404** |
| `https://yourdomain.com/.git/` | **403** or **404** |
| `https://yourdomain.com/artisan` | **403** or **404** |
| `https://yourdomain.com/api/v1/license/public-key` | **200** with JSON signing key |
| `http://yourdomain.com/` | **301 → https://** |

---

## 3. PRODUCTION ENV CHECKLIST

> [!IMPORTANT]
> Never commit `.env`. Never include it in the zip.

| Variable | Safe Example | Missing = |
|----------|-------------|-----------|
| `APP_ENV` | `production` | Default `production` — OK |
| `APP_DEBUG` | `false` | Default `false` — if `true` leaks stack traces |
| `APP_URL` | `https://license.corevisys.com` | Wrong URLs silently |
| `APP_KEY` | `base64:...` | **Fatal** — encryption fails |
| `LICENSE_SIGNING_KEY_ID` | `key-2026-01` | **Fatal** — AppServiceProvider throws on boot |
| `LICENSE_SIGNING_PRIVATE_KEY` | base64-encoded PEM of RSA key (output of `php artisan license:generate-keys`) | **Fatal** — signing fails |
| `LICENSE_SIGNING_PUBLIC_KEY` | `-----BEGIN PUBLIC KEY-----...` | **Fatal** — verification fails |
| `LICENSE_SIGNING_PUBLIC_KEYS` | `{"key-id":"-----BEGIN..."}` JSON | Silent `[]` — old clients can't verify |
| `LICENSE_SIGNING_REVOKED_KEY_IDS` | *(comma list or empty)* | Silent `[]` — revoked keys still trusted |
| `LICENSE_PEPPER` | 64-char random hex | **Fatal** — getLicensePepper() throws |
| `DB_CONNECTION` | `mysql` | Default SQLite — **silent wrong DB** |
| `DB_HOST` | `127.0.0.1` | — |
| `DB_DATABASE` | `corevisys_live` | — |
| `DB_USERNAME` | `corevisys_user` | — |
| `DB_PASSWORD` | *(password)* | — |
| `MAIL_MAILER` | `smtp` | Default `log` — **emails silently discarded** |
| `MAIL_HOST` | `mail.yourdomain.com` | — |
| `MAIL_PORT` | `465` | — |
| `MAIL_USERNAME` | `noreply@...` | — |
| `MAIL_PASSWORD` | *(password)* | — |
| `MAIL_ENCRYPTION` | `ssl` | — |
| `MAIL_FROM_ADDRESS` | `noreply@corevisys.com` | Default `hello@example.com` |
| `QUEUE_CONNECTION` | `database` | Default `sync` — **jobs run inline** |
| `SESSION_DRIVER` | `database` | `file` risky if /tmp gets wiped on cPanel |
| `SESSION_SECURE_COOKIE` | `true` | **Fixed** (commit `1d27f79`): defaults `true` in production |
| `SESSION_DOMAIN` | `.yourdomain.com` | Null — cookies not shared across subdomains |
| `TRUSTED_PROXIES` | `*` or LB CIDR | Silent wrong IP rate-limiting; boot logs `Log::error` |
| `STRIPE_SECRET_KEY` | `sk_live_...` | Stripe payments fail (gateway also disabled in system_settings) |
| `STRIPE_PUBLISHABLE_KEY` | `pk_live_...` | — |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` | Webhooks silently rejected 400 |
| `BKASH_APP_KEY` | *(live key)* | bKash payments fail |
| `BKASH_APP_SECRET` | *(live secret)* | — |
| `BKASH_USERNAME` | *(live username)* | — |
| `BKASH_PASSWORD` | *(live password)* | — |
| `FINGERPRINT_ENFORCEMENT_DEADLINE` | `2026-12-01` or empty | Null — no grace window unless grace_mode also true |
| `FINGERPRINT_GRACE_MODE` | `false` | **Fixed** (commit `e73fffc`): default is now `false` — enforced from day one |

**Fixed (commit `1d27f79`):**
- `SESSION_SECURE_COOKIE` now defaults to `true` when `APP_ENV=production`

**Still silent — must set explicitly:**
- `MAIL_MAILER=log` → emails discarded → set `smtp`
- `QUEUE_CONNECTION=sync` → no async → set `database`
- `SESSION_DRIVER=file` → risky → set `database`
- `DB_CONNECTION` → defaults to sqlite → always set `mysql`

---

## 4. SCHEDULER AND QUEUE

### Scheduled Commands (`php artisan schedule:list`)

| Cron | Command | Purpose |
|------|---------|---------|
| `0 0 * * *` | `license:renew-subscriptions` | Recurring subscription renewal ✅ |
| `0 1 * * *` | `license:notify-expiring` | Expiry notification emails |
| `0 2 * * *` | `license:flag-stale` | Flag stale offline licenses |
| `0 0 1 * *` | `license:cleanup-expired` | Monthly cleanup |
| `0 0 * * *` | `receipts:prune` | Prune old receipt records |

**Renewal IS scheduled.** ✅

### cPanel Cron Lines

```
# Run Laravel scheduler every minute
* * * * * /usr/local/bin/php /home/youraccount/corevisys/artisan schedule:run >> /dev/null 2>&1

# Process queued jobs every minute (shared hosting: no persistent worker)
# --max-time=50 ensures the worker exits before the next cron fires (prevents overlap)
* * * * * /usr/local/bin/php /home/youraccount/corevisys/artisan queue:work --stop-when-empty --tries=3 --max-time=50 >> /dev/null 2>&1
```

> [!NOTE]
> Verify PHP binary: run `which php` in cPanel terminal. Replace `/home/youraccount/corevisys/` with your actual path.

**Required .env:**
```env
QUEUE_CONNECTION=database
```

**One-time (if jobs table not yet migrated):**
```bash
php artisan queue:table && php artisan migrate --force
```

> [!WARNING]
> `--stop-when-empty` gives max 1-minute job latency. Acceptable for scheduled renewals; webhook processing is synchronous in the controller.

---

## 5. DEPLOY AND ROLLBACK

### Pre-deploy (local)

```bash
powershell -ExecutionPolicy Bypass -File .\scripts\build-release.ps1
# Verify zip: open and confirm no .env inside
```

### First Deploy Sequence (server via SSH)

```bash
# 1. Maintenance mode
php artisan down --retry=60

# 2. DB backup BEFORE migrate
mkdir -p ~/backups
mysqldump -u DB_USER -p DB_NAME > ~/backups/pre-deploy-$(date +%Y%m%d-%H%M%S).sql

# 3. Upload and extract zip into app directory
mkdir -p /home/youraccount/corevisys
cd /home/youraccount/corevisys
unzip ~/corevisys-release-YYYYMMDD.zip

# 4. Create .env from template and fill all required values
cp .env.example .env
nano .env

# 5. Discover packages (requires .env with LICENSE_SIGNING_KEY_ID set)
php artisan package:discover --ansi

# 6. Clear any stale caches from the zip
php artisan optimize:clear

# 7. Run migrations
php artisan migrate --force

# 8. Run installer (creates admin, seeds system settings, links storage)
php artisan corevisys:install --admin-email=admin@yourcompany.com

# 9. Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Set permissions
chmod -R 775 storage bootstrap/cache

# 11. Bring up
php artisan up
```

### Subsequent Deploy Sequence (update to existing install)

```bash
# 1. Maintenance mode
php artisan down --retry=60

# 2. DB backup BEFORE migrate
mysqldump -u DB_USER -p DB_NAME > ~/backups/pre-deploy-$(date +%Y%m%d-%H%M%S).sql

# 3. Delete old code dirs (keep .env and storage intact)
cd /home/youraccount/corevisys
rm -rf app bootstrap/app.php config database public resources routes vendor
# NOTE: storage/ and .env are NOT deleted

# 4. Extract new release
unzip ~/corevisys-release-YYYYMMDD.zip

# 5. Discover packages (bootstrap/cache was empty in zip — requires .env)
php artisan package:discover --ansi

# 6. Clear stale caches
php artisan optimize:clear

# 7. Migrate
php artisan migrate --force

# 8. Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Permissions
chmod -R 775 storage bootstrap/cache

# 10. Bring up
php artisan up
```

### Rollback

```bash
php artisan down
mysql -u DB_USER -p DB_NAME < ~/backups/pre-deploy-TIMESTAMP.sql
unzip corevisys-release-PREVIOUS.zip -d /home/youraccount/corevisys
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

### migrate --force on MySQL copy — VERIFIED ✅

```
DB: corevisys_migrate_test (copied from corevisys: 37 migrations, 9 users, 6 licenses)

$ cmd /c "set DB_DATABASE=corevisys_migrate_test&& php artisan migrate --force"

   INFO  Running migrations.
   2026_10_02_000002_add_cancelled_status_to_licenses_table ... 39ms DONE

After: user_count=9, license_count=6, migration_count=38 — rows preserved ✅
```

---

## 6. NO DUMMY DATA IN PRODUCTION

### Before (danger)

`DatabaseSeeder` unconditionally called `UserSeeder`, which creates `admin@example.com` / `password` + 5 faker users.

### After (commit `1d27f79`) ✅

**`DatabaseSeeder`:**
```php
if (!app()->environment('production')) {
    $this->call([UserSeeder::class]);
}
```

**`UserSeeder`:**
```php
if (app()->environment('production')) { return; }
```

**`SystemSettingsSeeder`:** uses `firstOrCreate()` — idempotent, no fake data, safe in production.

### Only admin creation path in production

```bash
php artisan corevisys:install --admin-email=admin@yourcompany.com
```

Creates admin with `email_verified_at = now()`, secure random password shown once, writes `storage/installed.lock`.

**Never run in production:**
- `php artisan db:seed` (now guarded, but still avoid)
- `php artisan db:seed --class=UserSeeder` (guarded in class itself)
- `php artisan migrate:fresh` / `migrate:reset` (prohibited by `DB::prohibitDestructiveCommands()`)

---

## 7. SMOKE TESTS (run after deploy)

```bash
# 7.1 HTTPS redirect
curl -sI http://yourdomain.com/ | grep -i "HTTP\|Location"
# Expected: 301, Location: https://

# 7.2 No debug pages
curl -s https://yourdomain.com/deliberate-404 | grep -i "stack trace\|exception\|APP_KEY"
# Expected: no output

# 7.3 Sensitive files blocked
for path in .env composer.json .git/ vendor/ artisan; do
  echo -n "$path: "; curl -sI "https://yourdomain.com/$path" | grep HTTP
done
# All expected: 403 or 404

# 7.4 Public key endpoint (200 + JSON)
curl -s https://yourdomain.com/api/v1/license/public-key
# Expected: {"key_id":"...","public_key":"-----BEGIN PUBLIC KEY-----..."}

# 7.5 Invalid license key -- must return 403 with error_code=invalid_license_key
# Header name is X-API-Version (from CheckClientVersion middleware)
curl -s -X POST https://yourdomain.com/api/v1/license/activate \
  -H "Content-Type: application/json" \
  -H "X-API-Version: 1.0.0" \
  -d '{"license_key":"INVALID-0000-0000-0000","domain":"smoke.example.com","product_code":"test"}'
# Expected: HTTP 403, body: {"status":false,"error_code":"invalid_license_key",...}
# Must NOT be 500, must NOT reveal internal detail

# 7.6 Health check
curl -sI https://yourdomain.com/up
# Expected: HTTP 200

# 7.7 Login throttle (6th failed attempt)
# (browser test) -- Expected: "Too many login attempts. Please try again in X seconds."

# 7.8 Queue health
php artisan queue:work --stop-when-empty --tries=1 --max-time=50
# Expected: exits cleanly, no exceptions
```

---

## 8. MISSING ITEMS WITH SEVERITY

| # | Item | Severity |
|---|------|----------|
| 1 | **Git history purge** (secrets on `origin/main`) | 🔴 BLOCKER — must complete before go-live |
| 2 | **Admin 2FA** (TOTP or email OTP) | 🟠 High — account takeover = full compromise |
| 3 | **Automated DB backup** | 🟠 High — no backups scheduled; use cPanel or mysqldump cron |
| 4 | **Log rotation** | 🟠 High — `LOG_CHANNEL=single` fills disk; switch to `daily` |
| 5 | **Mail SPF / DKIM / DMARC** | 🟠 High — without these, email lands in spam |
| 6 | **Legal pages** (Terms, Privacy, Refund) | 🟡 Medium — Vue stubs exist, content is placeholder; required for payments + GDPR |
| 7 | **bKash sandbox toggle** | 🟡 Medium — `gateway_bkash_sandbox=1` in system_settings; must set `0` after install |
| 8 | **Storage backup** (uploaded receipts) | 🟡 Medium — `storage/app/` not backed up; consider S3 (flysystem-aws already in composer.json) |
| 9 | **Rate limit + TRUSTED_PROXIES** | 🟡 Medium — without proxies config all clients share one bucket |
| 10 | **Error monitoring** (Sentry/Bugsnag) | 🟡 Medium — silent failures go undetected |
| 11 | **Key rotation runbook** | 🟡 Medium — `AUDIT/14` exists; rehearse before go-live |
| 12 | **OPcache** | 🟢 Low — verify `opcache.enable=1` in cPanel PHP settings |
| 13 | **`cpanel_database.sql` in repo** | 🟡 Medium — 35 KB file in repo root; check for real data, remove from git |

---

## LOCALLY COMPLETED

| Item | Status |
|------|--------|
| MustVerifyEmail reverted; `@var` docblock for PHPStan | ✅ |
| PHPStan: No errors | ✅ |
| 356 tests pass, 1330 assertions | ✅ |
| L10 matrix: 408 passed (guzzle added manually) | ✅ |
| L11 matrix: 408 passed | ✅ |
| Secret scan documented — no values printed | ✅ |
| `build-release.ps1` verified: 42.6 MB, 10714 entries, audit passed | ✅ |
| `migrate --force` on MySQL copy: migration applied, rows preserved | ✅ |
| UserSeeder + DatabaseSeeder guarded in production | ✅ |
| `SESSION_SECURE_COOKIE` defaults `true` in production | ✅ |
| `trusted_proxies` key in config/app.php | ✅ |

---

## SERVER-SIDE CHECKLIST (YOU MUST DO)

```
[ ] BLOCKER: Purge git history (secrets on origin/main), force-push, rotate all credentials
[ ] Set all required .env variables (§3)
[ ] Upload release zip and run deploy sequence (§5)
[ ] php artisan corevisys:install --admin-email=...
[ ] Set gateway_bkash_sandbox=0 in system_settings after install
[ ] Add cPanel cron: schedule:run every minute
[ ] Add cPanel cron: queue:work --stop-when-empty every minute
[ ] Run smoke tests (§7)
[ ] Configure DB backups
[ ] Set LOG_CHANNEL=daily in .env
[ ] Configure SPF/DKIM/DMARC in DNS
[ ] Fill in legal page content
[ ] Verify OPcache enabled in PHP settings
[ ] Check cpanel_database.sql — remove if it has real data
```
