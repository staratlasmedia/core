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
- Persistent Push Groups manage generated Service Worker and PWA manifest configuration.

## Frontend SDK

- `packages/core-sdk/` builds the TypeScript/Lit SDK with Vite.
- Build command: `npm run build` from `packages/core-sdk/`.
- Embeddable browser bundles are emitted to `htdocs/core.staratlasmedia.com/public/sdk/`.
- Registered Web Components: `core-login-widget`, `core-comments-widget`, and `core-push-widget`.
- Runtime config is read from `window.StarAtlasCore` first and can be overridden by component attributes.

## Admin Surface

- `/core-admin/push-groups` manages Push Groups and provides Service Worker / manifest preview and download.
- `/core-admin/push-migration-dashboard` shows legacy push migration counters, charts, and app mapping summaries.
- VAPID private keys and subscription secrets remain encrypted and are not displayed in Filament.

## Security Baseline

- Do not commit `.env`, credentials, VAPID private keys, SQL dumps, backups, `vendor/`, or `node_modules/`.
- Keep CORS allowlists exact-origin only.
- Keep Service Workers on the WordPress origins through the bridge plugin.
- Store Web Push endpoints and keys encrypted at rest in later schema phases.
