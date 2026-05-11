# Star Atlas Core Bridge

Generic WordPress bridge skeleton for Star Atlas Core.

## Phase 6 Scope

- One installable plugin for all WordPress sites in the network.
- Configuration is claimed with a Core-generated setup token.
- Site-specific origin, base path, service worker paths, manifest path, push group, section, language, SDK URL, and update channel come from Core config.
- No per-site forks and no universal hardcoded domain or worker path rule.

## Implemented Skeleton

- Namespaced `src/` bootstrap with lightweight autoloading.
- Admin page “Star Atlas Core” with setup token, config status, refresh, reset, test connection, update channel, and update-check controls.
- `wp_options` storage for bridge installation ID, bridge secret, secret fingerprint, Core config, release channel, and update-check metadata.
- Setup token claim against `POST /api/bridge/setup/claim`.
- HMAC request skeleton for future private Core calls.
- Public SDK injection through `window.StarAtlasCoreConfig`.
- Config-driven route interception for service worker, manifest, PWA start, auth callback, and push-click skeletons.
- Push-only service worker response served from the WordPress origin with no-cache headers.
- Private update checker skeleton using the standard WordPress plugin update UI hooks.

## Not Implemented Yet

- Full SSO session exchange.
- Full comments integration.
- Push preferences.
- Push sending.
- Brand/category admin UI.
- ZIP packaging.
- Full auto-update policy logic.
