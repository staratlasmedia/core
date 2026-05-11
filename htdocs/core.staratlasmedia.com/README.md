# Star Atlas Core Laravel App

Laravel 13 backend for `https://core.staratlasmedia.com`.

## Bootstrap Components

- Filament 5 panel at `/core-admin`.
- Horizon mounted under `/core-admin/horizon`.
- API routes under `/api/v1`.
- Exact-origin CORS middleware for public API calls.
- Redis configured for queue, cache, and sessions.

## Local Environment

Copy `.env.example` to `.env` and fill local secrets there only. Do not commit `.env`.

The bootstrap database defaults are:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=core
DB_USERNAME=core
DB_PASSWORD=change-me
```

## Verification

```bash
php artisan route:list
php artisan test
composer test
npm run browser:check -- https://core.staratlasmedia.com
```

## Scheduler Cron

Install one cron entry for the Laravel scheduler:

```cron
* * * * * cd /home/staratlasmedia-core/htdocs/core.staratlasmedia.com && /usr/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
```
