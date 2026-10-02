# CoreVisys Package Distribution Plan

**Date:** 2026-10-02  
**Target Package:** `corevisys/laravel-license-client` (v1.0.0)  
**Infrastructure Context:** Primary hosting on cPanel (Apache / PHP 8.2+ / MySQL) with GitHub repository.

---

## 1. Executive Summary

This document evaluates four distribution architectures for delivering the proprietary `corevisys/laravel-license-client` package to customers while protecting intellectual property, providing smooth installation/upgrades via Composer, and remaining fully viable within a cPanel hosting environment.

---

## 2. Evaluation of Delivery Options

### Option A: Static Composer Repository via Satis (cPanel Subdomain)

- **Architecture:** Satis compiles static Composer metadata (`packages.json` + hashed provider JSONs) and package zip archives from the private Git repository. The resulting static files are published to a cPanel web directory (e.g., `https://packages.corevisys.com`).
- **Access Control:** Protected via `.htaccess` HTTP Basic Authentication or bearer token query parameters. Customer places their assigned token in `auth.json`:
  ```json
  {
    "http-basic": {
      "packages.corevisys.com": {
        "username": "client-token",
        "password": "x"
      }
    }
  }
  ```
- **Pros:**
  - Standard native Composer UX (`composer require corevisys/laravel-license-client`).
  - Pure static HTML/JSON files — virtually zero CPU/memory load on cPanel.
  - Automated deployment via GitHub Actions (build Satis on push to main/tag, deploy via SFTP/FTP/Git to cPanel).
- **Cons:**
  - Token management is decoupled from the main database unless an `.htpasswd` generator script is synced via cron or webhook.
- **cPanel Viability:** **100% compatible.** Can be served from any cPanel subdomain or public folder with standard Apache rewrite rules.

---

### Option B: Dynamic Token-Auth Composer Repository (Integrated into LiencesSite)

- **Architecture:** LiencesSite implements Composer Repository V2 API endpoints (`/packages.json`, `/p2/corevisys/laravel-license-client.json`, `/dist/{version}.zip`).
- **Access Control:** Composer sends HTTP Basic Auth or Bearer token (the customer's license key or portal API token). A Laravel middleware checks the key against the `licenses` table. If active and valid, it serves the metadata and streams the package zip.
- **Pros:**
  - Instant entitlement enforcement: if a license is revoked, cancelled, or expired past grace, `composer update` immediately rejects downloads.
  - No duplicate user or token management — uses existing license keys.
  - Single application to manage and deploy on cPanel.
- **Cons:**
  - Requires maintaining the Composer v2 metadata endpoints and zip streaming controller in LiencesSite.
- **cPanel Viability:** **100% compatible.** Operates inside the existing Laravel application on cPanel.

---

### Option C: Managed Private Packagist (packagist.com)

- **Architecture:** Commercial SaaS service operated by the Composer team. Syncs with GitHub, generates per-customer access tokens, and provides download statistics.
- **Pros:**
  - Zero server maintenance, enterprise reliability, built-in customer sub-repositories.
- **Cons:**
  - Recurring monthly cost ($49–$199/month).
  - External dependency outside CoreVisys infrastructure.
- **cPanel Viability:** Fully compatible (external service, customer connects via token).

---

### Option D: Direct Zip Download via Customer License Portal

- **Architecture:** Authenticated customers download `corevisys-license-client-1.0.0.zip` directly from their dashboard on `LiencesSite`. Customer unzips into their project (e.g. `packages/corevisys-license-client`) and references it in `composer.json`:
  ```json
  "repositories": [
    {
      "type": "path",
      "url": "packages/corevisys-license-client"
    }
  ]
  ```
- **Pros:**
  - Zero ongoing infrastructure setup.
  - Downloads are strictly protected behind customer login and active license verification.
  - Works immediately today.
- **Cons:**
  - Upgrades require manual re-download and file replacement by the customer.
  - Not an automated `composer update` workflow.
- **cPanel Viability:** **100% compatible.** Standard authenticated file download route.

---

## 3. Comparison Matrix

| Option | Composer Workflow | Token Auth / Entitlement | Maintenance on cPanel | Cost | Recommended Staging |
|---|---|---|---|---|---|
| **A. Satis (Static)** | Native `composer require` | Static `.htpasswd` / bearer | Low (CI deploys static files) | Free | **Target Phase 2** |
| **B. Dynamic Laravel** | Native `composer require` | Real-time DB lookup | Medium (Laravel API routes) | Free | **Target Phase 3** |
| **C. Private Packagist** | Native `composer require` | Turnkey portal tokens | Zero | Paid ($49+/mo) | Optional |
| **D. Zip Download** | Manual path repo | Real-time portal auth | Zero | Free | **Immediate Phase 1** |

---

## 4. Staged Rollout Recommendation

1. **Phase 1 (Immediate / MVP):**
   - Provide a download button in the customer portal on LiencesSite for verified license holders.
   - Instruct customers to use a local `path` repository in `composer.json` or extract into `app/Packages`.
2. **Phase 2 (Automated Composer Distribution):**
   - Deploy Satis or lightweight Composer v2 endpoint on `packages.corevisys.com` (or `LiencesSite/api/v1/packages`), secured with license key bearer tokens.
