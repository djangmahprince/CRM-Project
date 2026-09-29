# Daily backup procedure

This project stores CRM data in the configured database (`DB_*` in `.env`) and private attachments on the `local` disk under `storage/app`.

## Daily checklist

1. **Database dump**
   - MySQL/MariaDB: `mysqldump -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backups/crm-$(date +%F).sql`
   - PostgreSQL: `pg_dump $DATABASE_URL > backups/crm-$(date +%F).sql`
   - SQLite (local only): copy `database/database.sqlite` to `backups/crm-YYYY-MM-DD.sqlite`
2. **Files**: archive `storage/app` (attachments live under `storage/app/private` or `storage/app` depending on disk config).
3. **Verify**: restore a dump into a scratch database weekly and confirm login + one lead show page.
4. **Retention**: keep at least 7 daily backups and 4 weekly backups off the app server.
5. **Secrets**: never commit `.env`, dumps, or `credentials.txt` to git.

## Automation note

Queue/scheduler jobs (`php artisan schedule:run`) are separate from backups. Wire OS cron or Laravel Cloud scheduled tasks for dumps in staging/production; local Mailpit and SQLite are fine for development only.
