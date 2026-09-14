# CoreVisys Deployment Guide

## Production environment checklist

1. Copy `.env.example` to `.env` and replace placeholders with real values for the target environment.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.example`, `APP_KEY` to a fresh generated key, and disable any local-only debugging flags.
3. Configure production mail credentials in the environment (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`).
4. Configure `LICENSE_SIGNING_PRIVATE_KEY`, `LICENSE_SIGNING_PUBLIC_KEY`, `LICENSE_SIGNING_KEY_ID`, and `LICENSE_SIGNING_ALGORITHM` with a production key pair.
5. Configure Stripe and bKash production secrets through the deployment secret manager. Do not store them in committed files.
6. Configure durable storage (`AWS_*`/`S3-compatible` settings) for receipts and backups.
7. Run `php artisan migrate --force`, then run `php artisan license:migrate-legacy-keys --dry-run` to review legacy plaintext rows, followed by `php artisan license:migrate-legacy-keys` in the target environment.
8. Run `php artisan db:seed` only in the target environment, then verify the admin route and queue workers.

## Secret rotation and scanning

- Rotate `APP_KEY`, signing keys, gateway secrets, and database credentials on a documented schedule.
- Keep old public signing keys available for a short overlap period while clients refresh cached keys.
- Run a repository secret scan on every release with `gitleaks`, `trufflehog`, or an equivalent tool.
- Review logs, screenshots, exports, and test fixtures for any secret remnants before production release.

## Operators

- Start the queue worker process: `php artisan queue:work database --tries=3 --backoff=60`
- Run the scheduler via cron for `license:renew-subscriptions`, `license:notify-expiring`, and `license:cleanup-expired`.
- Monitor `failed_jobs` and application logs for repeated gateway failures or delivery errors.
