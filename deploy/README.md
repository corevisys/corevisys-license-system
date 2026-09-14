# Deployment Notes

## Workers and scheduler

Use `supervisord` to run the queue worker and scheduler in production:

- `supervisord -c deploy/supervisord.conf`
- Ensure `php artisan queue:work database --tries=3 --backoff=60` is always running.
- Ensure `php artisan schedule:run` is invoked on a recurring interval by the scheduler program.

## Cron

Add the Laravel scheduler entry to your system cron if you are not using a supervisor-managed loop:

- `* * * * * cd /var/www/corevisys && php artisan schedule:run >> /dev/null 2>&1`

## Monitoring

- Monitor `failed_jobs` in the application database.
- Forward Laravel logs to centralized log aggregation.
- Track queue depth, exceptions, and failed webhooks.
