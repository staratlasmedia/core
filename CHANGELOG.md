# CHANGELOG

Tutte le modifiche sostanziali al progetto vanno registrate qui.

## Unreleased

### Added
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
- Root `README.md` now documents the bootstrap layout and runtime targets.
- Agent instructions now prefer quick `agent-browser` checks for site availability and simple visual verification.

### Fixed
- Local runtime `.env` permissions now allow PHP-FPM to read the application key.

### Security
- `.env.example` uses placeholders only; local `.env` is ignored by Git.
- Bootstrap API CORS uses exact origins and emits `Vary: Origin`.
