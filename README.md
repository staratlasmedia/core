# Star Atlas Core

Centralized headless backend for Star Atlas Media, hosted at:

```text
https://core.staratlasmedia.com
```

## Bootstrap Layout

- `htdocs/core.staratlasmedia.com/` — Laravel 13 application served by the active vhost.
- `packages/core-sdk/` — TypeScript/Lit SDK skeleton.
- `wordpress/star-atlas-core-bridge/` — WordPress bridge plugin skeleton.
- `docs/` — architecture, migration, SSO, and operational documentation.

## Runtime Targets

- Laravel 13 on PHP 8.4.
- Filament 5 panel at `/core-admin`.
- MariaDB 11.8.
- Redis queues/cache with Laravel Horizon at `/core-admin/horizon`.
- Web Push delivery via `minishlink/web-push`.

## Security Baseline

- Do not commit `.env`, credentials, VAPID private keys, SQL dumps, backups, `vendor/`, or `node_modules/`.
- Keep CORS allowlists exact-origin only.
- Keep Service Workers on the WordPress origins through the bridge plugin.
- Store Web Push endpoints and keys encrypted at rest in later schema phases.
