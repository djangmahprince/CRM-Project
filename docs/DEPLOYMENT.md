# Deployment

## Docker Compose

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Services typically include `app`, `mysql`, `redis` (optional), and `mailpit` for local mail. Confirm ports in `docker-compose.yml`.

## Production checklist

1. Set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`
2. Use MySQL/PostgreSQL (not SQLite) and Redis for cache/queue when possible
3. Configure SMTP (or Mailgun/SES) for password reset and CRM notifications
4. Run `php artisan migrate --force`, `npm ci && npm run build`, `php artisan optimize`
5. Run a queue worker and scheduler:
   - `php artisan queue:work --sleep=3 --tries=3`
   - cron: `* * * * * php /path/to/artisan schedule:run`
6. Point the web server document root to `public/`
7. Ensure `storage/` and `bootstrap/cache/` are writable
8. Attachments use the private disk — never expose storage outside authorized download routes

## Scheduled jobs

Registered in `routes/console.php`:

- `SendTaskReminders` every five minutes
- `SendReportSubscriptions` hourly

## Health

`GET /up` — Laravel health endpoint.
