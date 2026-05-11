# Star Atlas Core Bridge

WordPress bridge skeleton for Star Atlas Core.

## Bootstrap Scope

- Inject the future Core SDK asset from `https://core.staratlasmedia.com`.
- Reserve the plugin namespace, constants, and bootstrap class.
- Keep Service Worker, manifest, PWA start, and SSO callback routes for a later implementation phase.

## Required Future Routes

- `/smart_sw.js`
- `/automobili/smart_sw.js`
- `/en/smart_sw.js`
- `/pwa-start/`
- `/en/pwa-start/`
- `/core-auth/callback`
- `/core-auth/session`
- `/core-auth/logout`

## Security Notes

- Service Worker routes must be served directly from the WordPress origin.
- Service Worker responses must use no-cache headers.
- API calls to Core must use signed requests or client tokens.
- Do not store Core secrets in versioned plugin files.
