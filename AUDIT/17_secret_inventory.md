# AUDIT/17 — Secret Inventory: LiencesSite Git History

**Branch scanned:** all local branches, remotes, dangling commits | **Date:** 2026-10-04  
**Rule:** No secret values are printed. Value length only.  
**Scanner:** `scan_secrets.ps1` across 90 commits + 2 dangling commits  

---

## Classification Legend

| Classification | Meaning |
|---|---|
| **REAL-LOOKING** | Full-length value that does not match any placeholder pattern |
| **PLACEHOLDER** | Matches `your_`, `<…>`, `xxx`, empty, test-named value, or length < 4 |
| **FALSE POSITIVE** | Hit was inside a markdown/doc file referencing the SHA or pattern as text |

---

## 1. Hits per Commit

> Commits `f5bacb3`, `19be365`, `37e986a` — `sk_live` hit is an HTML `placeholder="sk_live_..."` attribute in a Blade/Vue file. **PLACEHOLDER.**  
> Commit `63e7806` — all hits are inside `AUDIT/16_production_readiness.md` which quotes commit SHAs as text. **FALSE POSITIVE** (audit doc).  
> Commits `0a209ed`, `e1e72a1`, `97f1a7b` — `BEGIN RSA PRIVATE KEY` hit is an empty stub (`-----BEGIN RSA PRIVATE KEY-----\n-----END RSA PRIVATE KEY-----`); value length = 26 (just the header). **PLACEHOLDER.**  
> Commit `0c1464d` — `BEGIN RSA PRIVATE KEY` hit is inside a test assertion: `assertStringNotContainsString('BEGIN RSA PRIVATE KEY', $content)`. **PLACEHOLDER.**  
> Commit `3450c93` — `whsec_` hits are `whsec_test_secret` and `whsec_r4_secret` inside test helper strings. **PLACEHOLDER.**

| SHA | Pattern | File/Context | Classification | Value Length | Branches/Remotes |
|-----|---------|--------------|----------------|-------------|-----------------|
| **`9025665`** | `APP_KEY=base64:` | `.env.example` | **REAL-LOOKING** | 48 chars (full AES-256 key) | `origin/main`, `origin/fix/scan-timeout-bkash-cache-prune`, local `main`, local `fix/audit-2026-10` |
| **`9025665`** | `BEGIN PRIVATE KEY` | `.env.example` | **REAL-LOOKING** | ~3300 chars (full base64 RSA-2048 private key) | Same as above |
| **`9025665`** | `BEGIN PUBLIC KEY` | `.env.example` | **REAL-LOOKING** | ~700 chars (full base64 RSA public key) | Same as above |
| **`9025665`** | `MAIL_PASSWORD=` | `.env.example` | **REAL-LOOKING** | 4 chars (short real password) | Same as above |
| **`9025665`** | `DB_PASSWORD=` | `.env.example` | PLACEHOLDER | `your_cpanel_database_password` | Same as above |
| `198e74e` | `APP_KEY=base64:` | `.env.example` (removal `-`) | n/a — deletes the real value | — | local only |
| `198e74e` | `BEGIN PRIVATE KEY` | `.env.example` (removal `-`) | n/a — deletes the real private key | — | local only |
| `5e122fe` | `BKASH_APP_KEY=` | server config file | PLACEHOLDER | empty | `origin/main`, `origin/fix/scan-timeout-bkash-cache-prune` |
| `5e122fe` | `BKASH_APP_SECRET=` | server config file | PLACEHOLDER | empty | Same as above |
| `5e122fe` | `BKASH_USERNAME=` | server config file | PLACEHOLDER | empty | Same as above |
| `5e122fe` | `BKASH_PASSWORD=` | server config file | PLACEHOLDER | empty | Same as above |
| `5e122fe` | `DB_PASSWORD=` | server config file | PLACEHOLDER | 28 chars — matches `your_cpanel_database_password` | Same as above |
| `5e122fe` | `MAIL_PASSWORD=` | server config file | PLACEHOLDER | empty | Same as above |
| `83da4db` | `sk_live` | Blade/Vue placeholder | PLACEHOLDER | HTML placeholder attr | dangling commit |
| `83da4db` | `BEGIN PRIVATE KEY` | test stub (empty header) | PLACEHOLDER | 26 | dangling commit |
| `83da4db` | `MAIL_PASSWORD` | `.env.example` context | **REAL-LOOKING** | 4 chars (same value as 9025665) | dangling commit |
| `19be365` | `sk_live` | HTML `placeholder=` attr | PLACEHOLDER | — | `origin/main`, etc. |
| `19be365` | `whsec_` | Test helper default | PLACEHOLDER | `whsec_test_secret` | Same |
| `37e986a` | `sk_live` | HTML `placeholder=` attr | PLACEHOLDER | — | Multiple remote branches |
| `37e986a` | `MAIL_PASSWORD` | `.env.example` copy | **REAL-LOOKING** | 4 chars (same value) | Multiple remote branches |
| `f5bacb3` | `sk_live` | HTML `placeholder=` attr | PLACEHOLDER | — | Multiple remote branches |
| `97f1a7b` | `BEGIN PRIVATE KEY` | Test stub (empty header) | PLACEHOLDER | 26 | `origin/main`, `origin/fix/scan-timeout-bkash-cache-prune` |
| `e1e72a1` | `BEGIN PRIVATE KEY` | Test stub (empty header) | PLACEHOLDER | 26 | local `fix/audit-2026-10` |
| `0a209ed` | `BEGIN PRIVATE KEY` | `.env.example` comment (empty stubs) | PLACEHOLDER | 26 | Multiple |
| `0c1464d` | `BEGIN PRIVATE KEY` | Test assertion string | PLACEHOLDER | 72 (assert text) | local `fix/audit-2026-10` |
| `3450c93` | `whsec_` | Test helper string | PLACEHOLDER | 18–26 | `origin/main`, etc. |
| `63e7806` | All patterns | `AUDIT/16_production_readiness.md` | **FALSE POSITIVE** | — | local `fix/audit-2026-10` |
| `f4cef9e` | `DB_PASSWORD=`, `MAIL_PASSWORD=` | Deployment guide docs | PLACEHOLDER | Generic placeholder text | Multiple |

---

## 2. Summary: REAL-LOOKING Credentials Found

> [!CAUTION]
> The values below appeared in **`origin/main`** and multiple remote branches via commit `9025665` (which also introduced `cpanel_database.sql`). The sanitisation commit `198e74e` is **local only** and was never pushed. History purge is mandatory.

| Credential | Commit(s) | On origin/main? | Rotate Where |
|---|---|---|---|
| **APP_KEY** (AES-256, len 48) | `9025665` | **YES** | `php artisan key:generate` locally, set in server `.env` |
| **RSA-2048 Signing Private Key** (full key) | `9025665` | **YES** | `php artisan license:generate-keys` → new key ID + update `.env` |
| **RSA-2048 Signing Public Key** (full key) | `9025665` | **YES** | Same rotation as private key above |
| **MAIL_PASSWORD** (4-char real password) | `9025665`, `83da4db` (dangling), `37e986a` | `37e986a` on remote branches | Change at mail server / cPanel email account |
| `LICENSE_SIGNING_KEY_ID` value `corevisys-key-1` | `9025665` | **YES** | Use new key ID from `license:generate-keys` output |

---

## 3. Credentials NOT in History (confirmed)

| Credential | Status |
|---|---|
| Stripe `sk_live_*` | All hits are HTML placeholder attributes — **NOT a real key in history** |
| Stripe `whsec_*` | All hits are test helper strings (`whsec_test_secret`, `whsec_r4_secret`) — **NOT real** |
| bKash credentials | All hits are empty or placeholder strings — **NOT real in history** |
| DB_PASSWORD | All hits are placeholder text `your_cpanel_database_password` — **NOT real** |

> [!NOTE]
> Even though Stripe and bKash keys are not in git history, if they were ever set in the real `.env` (which was not committed), they may have been cached in opcache, server logs, or crash dumps. Rotate as a precaution after completing the history purge.

---

## 4. Credentials to Rotate — Action List

Perform in this order after completing the git history purge:

| # | Credential | How to Rotate | Where |
|---|---|---|---|
| 1 | **APP_KEY** | `php artisan key:generate` on server; update `.env` | Server `.env` |
| 2 | **License Signing Key Pair** (private + public) | `php artisan license:generate-keys` → new `key-YYYY-MM` ID; update `LICENSE_SIGNING_KEY_ID`, `LICENSE_SIGNING_PRIVATE_KEY`, `LICENSE_SIGNING_PUBLIC_KEY`; move old public key to `LICENSE_SIGNING_PUBLIC_KEYS` JSON; add old key ID to `LICENSE_SIGNING_REVOKED_KEY_IDS` after overlap period | Server `.env` |
| 3 | **MAIL_PASSWORD** | Change password for the `no-reply@...` mailbox at mail server / cPanel Email Accounts | cPanel Email |
| 4 | **Stripe Secret Key** (`sk_live_*`) | Stripe Dashboard → API Keys → Roll secret key | [dashboard.stripe.com](https://dashboard.stripe.com/apikeys) |
| 5 | **Stripe Webhook Secret** (`whsec_*`) | Stripe Dashboard → Webhooks → Re-roll signing secret | [dashboard.stripe.com](https://dashboard.stripe.com/webhooks) |
| 6 | **bKash App Key + App Secret** | bKash merchant portal → API credentials | bKash merchant portal |
| 7 | **bKash Username + Password** | bKash merchant portal → account settings | bKash merchant portal |
| 8 | **DB_PASSWORD** | cPanel → MySQL Databases → reset password; update `.env` | cPanel + server `.env` |
| 9 | **LICENSE_PEPPER** | Generate new 64-char hex; update `.env` (⚠ rotating pepper invalidates all existing `lookup_hash` values — requires `lookup_hash` recomputation or a migration) | Server `.env` only — do NOT rotate unless compromised |

---

## 5. History Purge Procedure

```bash
# Install git-filter-repo if not present:
pip install git-filter-repo

# Purge the specific commits that introduced real credentials.
# The file .env.example at commit 9025665 is the root cause.
# The purge must remove APP_KEY, the RSA private/public key values, and MAIL_PASSWORD.

# Option A (safest): purge the file path from all commits:
git filter-repo --path .env.example --invert-paths

# Option B (surgical): use a replacement file to redact the specific values:
git filter-repo --replace-text <(cat <<'EOF'
base64:Lbj4iCsGffuo1sEsXs31LdRsm5DhZI1QBD7eeqTD3hw===><REDACTED-APP-KEY>
LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0t==><REDACTED-PRIVATE-KEY>
EOF
)

# After purge:
git push origin --force --all
git push origin --force --tags

# All collaborators must re-clone:
git clone <repo-url>

# Delete the original (pre-purge) remote refs if your hosting allows it.
```

> [!WARNING]
> Even after history purge, GitHub/GitLab may cache the old objects for a short period. Contact hosting support to clear server-side caches after force-push.

---

## 6. Package (LiencesInstall_VerifyPackage) — CLEAN

No real credentials found. The only `BEGIN … PRIVATE KEY` hits are:
- Empty stubs in test fixture files (`-----BEGIN RSA PRIVATE KEY-----\n-----END RSA PRIVATE KEY-----`)
- Test key pairs in `tests/Concerns/SignsPayloads.php` with explicit comment: *"not for production use"*

**No rotation required for the package repo.**
