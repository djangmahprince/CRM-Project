# NorthStar — Sales & Service Cloud

Web CRM built with **Laravel**, **Vue 3**, and **Inertia** covering Sales Cloud (Leads, Accounts, Contacts, Opportunities), Service Cloud (Cases), productivity (Tasks, Calendar), analytics (Reports, Dashboards), search, import/export, MFA, GDPR admin tools, and a versioned JSON API.

## Prerequisites

- PHP 8.4+ with SQLite or MySQL
- Composer 2
- Node.js 20+ and npm
- Optional: Docker Desktop for Compose stack

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
# SQLite (default in .env.example):
# touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
# Terminal A
php artisan serve
# Terminal B (optional while developing)
npm run dev
# Optional: queues + schedule
php artisan queue:work
php artisan schedule:work
```

Open `http://localhost:8000` and sign in with seeded demo users (see [docs/DEMO_CREDENTIALS.md](docs/DEMO_CREDENTIALS.md)).

## Demo users

| Role | Email | Password |
| --- | --- | --- |
| System Administrator | admin@northstar.test | Password1! |
| Sales Representative | rep@northstar.test | Password1! |
| Service Representative | service@northstar.test | Password1! |

## Architecture

- **UI transport:** Inertia pages under `resources/js/Pages`
- **Authorization:** Spatie roles/permissions + record ownership/sharing (`visibleTo`)
- **Domain actions:** e.g. `App\Actions\ConvertLeadAction`
- **Background work:** queued notifications, task reminders, report subscriptions (`routes/console.php`)
- **API:** Sanctum token API under `/api/v1/*` (see [docs/openapi.yaml](docs/openapi.yaml))

## Testing

```bash
php artisan test --compact
vendor/bin/pint --dirty
npm run build
```

Coverage requires Xdebug or PCOV (`php artisan test --coverage`).

## Documentation

| Doc | Purpose |
| --- | --- |
| [docs/requirements-status.md](docs/requirements-status.md) | SRS requirement traceability |
| [docs/DEVELOPMENT_PLAN.md](docs/DEVELOPMENT_PLAN.md) | Architecture decisions |
| [docs/BACKUP.md](docs/BACKUP.md) | Backup procedure |
| [docs/DEMO_CREDENTIALS.md](docs/DEMO_CREDENTIALS.md) | Seeded login accounts |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Production / Docker notes |
| [docs/openapi.yaml](docs/openapi.yaml) | REST API outline |

## Environment highlights

See `.env.example` for mail, queue, session idle timeout, CRM sharing defaults, and MFA-related settings. Never commit secrets; `env()` is only used inside `config/*` files.
