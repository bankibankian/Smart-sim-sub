# Monthly activation-bonus cron job

Covers scheduling `php artisan sim:grant-bulk-activation-bonus` (see
`app/Console/Commands/GrantBulkActivationBonusData.php`) to run automatically
once a month, on both cPanel shared hosting and a VPS.

## What the command actually does

For each network with an active bonus plan configured (admin panel →
SME Data Plans → SIM Activation Data Bonus), it **queues** one
`GrantActivationBonusData` job per already-activated SIM on that network. It
does not send anything itself — the jobs land on the `jobs` table
(`QUEUE_CONNECTION=database` in `.env`) and only run once a **queue worker**
picks them up.

This matters for cron setup: you need **two** scheduled things, not one —
the command itself, and something processing the queue it feeds. If you
already have a queue worker running continuously (see Part 2), you only need
to add Part 1.

---

## Part 1 — the monthly command itself

Recommended schedule: once a month, early morning, low-traffic — e.g. the
1st at 3:00 AM server time:

```
0 3 1 * *
```

### On cPanel

1. **Find your PHP binary path first** — cPanel's default `php` on `PATH` is
   often an old/unrelated version. Go to **MultiPHP Manager**, confirm which
   PHP version your app's domain uses (e.g. `8.2`), then find that version's
   CLI binary — usually under `/opt/cpanel/ea-php82/root/usr/bin/php`
   (substitute your version). If unsure, ask your host or check
   **Setup Node.js/PHP App** → your app → "Run NPM Install" style tooling
   often shows the interpreter path, or SSH in and run:
   ```bash
   ls /opt/cpanel/ | grep ea-php
   ```
2. Go to **cPanel → Advanced → Cron Jobs**.
3. Under **Add New Cron Job**, pick **Once Per Month** (or select "Common
   Settings" → *Once a month* then adjust the minute/hour), or fill the
   fields manually to match `0 3 1 * *`.
4. In the **Command** box, enter (adjust the PHP path and the app path — the
   deploy workflow in this repo, `.github/workflows/deploy-cpanel.yml`,
   already reads these two values from the `CPANEL_PHP_BIN` and
   `CPANEL_DEPLOY_PATH` secrets used at deploy time — reuse the same values
   here):
   ```bash
   /opt/cpanel/ea-php82/root/usr/bin/php /home/yourcpaneluser/public_html/artisan sim:grant-bulk-activation-bonus >> /home/yourcpaneluser/public_html/storage/logs/bulk-bonus.log 2>&1
   ```
5. Save. cPanel emails the account holder on every run by default (stdout);
   since the command's own output is redirected to the log file above, you
   can leave cPanel's notification email blank in your account settings if
   you don't want a monthly email, or leave it on as a second confirmation
   channel — either is fine.

### On a VPS (Ubuntu/Debian-style, systemd-managed)

1. SSH in as the deploy user (whichever user owns the app files — running
   cron as `root` for an app-owned directory just creates permission
   headaches later).
2. Open that user's crontab:
   ```bash
   crontab -e
   ```
3. Add:
   ```
   0 3 1 * * cd /var/www/smartsim && php artisan sim:grant-bulk-activation-bonus >> storage/logs/bulk-bonus.log 2>&1
   ```
   (adjust `/var/www/smartsim` to your actual deploy path). A VPS's `php` on
   `PATH` is normally already the right version if you `ssh` in as the same
   user that runs the app — confirm with `php -v` before saving.
4. Save and exit; `crontab -l` to confirm it's registered.

---

## Part 2 — make sure something is processing the queue

If your queue worker is already running in production (most setups), skip
this — the jobs the command queues will just be picked up automatically,
same as any other queued job in the app (e.g. `GrantActivationBonusData`
already fires this way on every individual SIM activation, not just the
monthly bulk run).

If you're not sure, here's how to check and, if needed, set one up.

### VPS — Supervisor (recommended; long-running worker)

Best fit for a VPS since you have full process control. Create
`/etc/supervisor/conf.d/smartsim-worker.conf`:
```ini
[program:smartsim-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/smartsim/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
directory=/var/www/smartsim
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/smartsim/storage/logs/worker.log
stopwaitsecs=3600
```
Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smartsim-worker:*
```
This worker already respects the app's existing `RateLimited('sme-data-vendor')`
middleware on the job (capped at 30/min in `AppServiceProvider`), so it's
safe to leave running continuously — it naturally paces itself even during
the once-a-month burst this command creates.

### cPanel — cron-driven `queue:work --stop-when-empty`

Most shared cPanel accounts can't run a persistent background process
(no Supervisor access), so the standard workaround is a **second cron job**
that runs the worker for a short burst, frequently:
```
* * * * * /opt/cpanel/ea-php82/root/usr/bin/php /home/yourcpaneluser/public_html/artisan queue:work --stop-when-empty --tries=3 --max-time=50 >> /home/yourcpaneluser/public_html/storage/logs/worker.log 2>&1
```
`--stop-when-empty` makes each invocation exit as soon as the queue is
drained rather than idling, and `--max-time=50` keeps it safely under the
one-minute cron interval so runs never overlap. This already exists on most
Laravel-on-cPanel deployments for the app's other queued jobs — check
**Cron Jobs** first in case one's already configured before adding a
duplicate.

---

## Verifying it worked

After the scheduled time passes:

```bash
# Confirm the command actually ran and queued jobs (per-network summary)
tail -50 storage/logs/bulk-bonus.log

# Confirm jobs were processed (should trend toward 0 if the worker's keeping up)
php artisan tinker --execute="echo DB::table('jobs')->count();"

# Confirm no jobs died
php artisan tinker --execute="echo DB::table('failed_jobs')->count();"
```

A healthy run's log looks like:
```
Queued 42 SIM(s) on mtn for "1GB (SME)".
Skipping airtel: bonus is off or no plan selected.
Queued 18 SIM(s) on glo for "500MB (SME)".
Skipping 9mobile: bonus is off or no plan selected.
Total: 60 activation bonus job(s) queued.
```

If `failed_jobs` grows, check `storage/logs/laravel.log` for the vendor
error — `GrantActivationBonusData::failed()` already logs the final
give-up reason there after all retries are exhausted.
