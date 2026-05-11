# CHANGELOG

Tutte le modifiche sostanziali al progetto vanno registrate qui.

## Unreleased

### Added
- Phase 2 foundational database schema for sites, origins, identity, auth, push, comments, newsletter, audit, webhooks, and SDK tokens.
- Eloquent foundation models with base relationships and encrypted casts for push, VAPID, API client, SDK token, and newsletter subscriber secrets.
- Filament base resources for Sites, Site Origins, Legacy Push Apps, VAPID Key Sets, and read-only Push Subscriptions.
- Phase 2 schema/security tests for critical tables, relationships, encrypted-at-rest fields, and Filament secret visibility.
- Initial changelog scaffold.
- Laravel 13 application bootstrap under `htdocs/core.staratlasmedia.com`.
- Filament 5 panel configured for `/core-admin`.
- Laravel Horizon configuration under `/core-admin/horizon`.
- Exact-origin CORS middleware and bootstrap API routes.
- TypeScript/Lit Core SDK skeleton.
- Star Atlas Core Bridge WordPress plugin skeleton.
- Root `API.md` and `SECURITY.md` documentation.
- Agent Browser tooling for fast online and visual smoke tests.
- Root `agent-browser.md` command reference linked from `AGENTS.md`.

### Changed
- Users now include UUID, status, metadata, and soft-delete support for Core identity foundations.
- Root `README.md` now documents the bootstrap layout and runtime targets.
- Agent instructions now prefer quick `agent-browser` checks for site availability and simple visual verification.
- Development logging now keeps debug enabled and writes to single, daily, PHP error log, and deprecation logs while Core is under construction.
- Infrastructure troubleshooting now documents the vhost/PHP-FPM checks for Laravel 500 errors.

### Fixed
- Local runtime `.env` permissions now allow PHP-FPM to read the application key.
- Laravel storage and cache permissions now allow PHP-FPM to write logs and compiled views.

### Security
- `.env.example` uses placeholders only; local `.env` is ignored by Git.
- Bootstrap API CORS uses exact origins and emits `Vary: Origin`.
