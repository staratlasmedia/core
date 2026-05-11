# CHANGELOG

Tutte le modifiche sostanziali al progetto vanno registrate qui.

## Unreleased

### Added
- Phase 6 Star Atlas Core Bridge WordPress plugin skeleton with namespaced bootstrap, setup token admin flow, config storage, path-aware SDK/Service Worker/manifest/PWA/auth/push-click route skeletons, HMAC Core client, and private updater client integration.
- Phase 4B WordPress Bridge Core support with setup token, installation, config version, plugin package, plugin release, and plugin update download models/tables.
- Filament WordPress Bridge resources for setup tokens, bridge installations, config versions, plugin packages, plugin releases, and read-only download audit.
- Bridge API skeleton for setup claim, config sync, heartbeat, future events, private plugin update checks, plugin info, and temporary ZIP downloads.
- Bridge configuration preview builder for ClubAlfa root, `/automobili/`, and `/en/` installation contexts.
- Phase 5 Core SDK frontend skeleton with Vite/Lit Web Components for login, comments, and push widgets.
- Embeddable SDK bundles in `public/sdk` with config resolution from `window.StarAtlasCore` and element attributes.
- Push subscription scaffold that registers origin-local Service Workers, posts modern subscription/context payloads to Core, and flags existing subscriptions as legacy reconfirmation candidates.
- SSO login scaffold with click-synchronous popup flow, redirect fallback, and silent iframe helper placeholder.
- Phase 4 Push Groups as persistent PWA/Web Push configuration entities with seeded defaults for ClubAlfa, MotoriSuMotori, Mbenz, NotizieAuto, AlfaVirtualClub, and Robotica.
- Filament Push Groups resource with Service Worker and manifest preview/download actions.
- Filament Push Migration dashboard widgets for legacy status totals, reconfirmation rate, daily reconfirmations, pending vs reconfirmed, and legacy app mapping summaries.
- Custom `minishlink/web-push` service layer and Redis/Horizon jobs for campaign and batch dispatch scaffolding.
- Generator tests for clean Service Worker output and stable ClubAlfa IT/EN manifest fields.

### Changed
- Sites, legacy push apps, and push subscriptions now include nullable `push_group_id` while retaining legacy string fields for compatibility/backfill.
- Legacy import now links imported app mappings and subscriptions to canonical Push Groups.
- VAPID key sets are read-only in Filament and public keys are masked in table views.

### Fixed
- Push delivery logs now disable Eloquent timestamps for tables that use explicit attempted/delivered timestamps.

### Security
- Star Atlas Core Bridge stores the bridge secret for HMAC use but only shows the fingerprint in admin; update downloads use Core-issued temporary URLs instead of exposing secrets in query strings.
- Bridge setup tokens are stored only as SHA-256 hashes, bridge secrets are encrypted at rest, and Filament displays fingerprints instead of raw secrets.
- Bridge config, heartbeat, event, and plugin update endpoints require the HMAC header skeleton; plugin downloads use temporary hashed tokens.
- Generated Service Workers use the new push-only payload shape and do not include `eval`, `fetch` handlers, or `data.target`.
- VAPID private keys remain absent from Filament forms, tables, previews, generated downloads, and tests.

## 2026-05-11

### Added
- Phase 3 legacy Web Push import tooling with `core:legacy-push:inspect` and `core:legacy-push:import`, safe dry-run reporting, encrypted subscription/VAPID storage, and idempotent pending imports.
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
- Agent memory workflow now defaults ByteRover curation to `--timeout 700` and recommends `--detach` for curation batches with more than two attached files when the result is not needed immediately.
- Users now include UUID, status, metadata, and soft-delete support for Core identity foundations.
- Root `README.md` now documents the bootstrap layout and runtime targets.
- Agent instructions now prefer quick `agent-browser` checks for site availability and simple visual verification.
- Development logging now keeps debug enabled and writes to single, daily, PHP error log, and deprecation logs while Core is under construction.
- Infrastructure troubleshooting now documents the vhost/PHP-FPM checks for Laravel 500 errors.

### Fixed
- Local runtime `.env` permissions now allow PHP-FPM to read the application key.
- Laravel storage and cache permissions now allow PHP-FPM to write logs and compiled views.

### Security
- Legacy Web Push import output reports endpoint hashes and VAPID sources only, never raw endpoints, subscription keys, or VAPID private keys.
- `.env.example` uses placeholders only; local `.env` is ignored by Git.
- Bootstrap API CORS uses exact origins and emits `Vary: Origin`.
