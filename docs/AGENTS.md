# AGENTS.md — Core / Star Atlas Media System

## Purpose

This repository contains **Core**, the centralized headless backend for Star Atlas Media.

Core is hosted on:

- `https://core.staratlasmedia.com`

Core will progressively manage:

- centralized Identity / SSO;
- Web Push subscription management;
- migration of legacy Web Push subscriptions from the old Smart Push Notification System;
- newsletter infrastructure via Amazon SES and SNS webhooks;
- proprietary comments system replacing Disqus;
- social automation foundation;
- centralized analytics;
- admin dashboard via Filament;
- frontend SDK in TypeScript/Lit embeddable on WordPress sites;
- WordPress bridge plugin: **Star Atlas Core Bridge**.

## Target Stack

- Laravel 13, only if the server has compatible PHP.
- Filament 5.
- MariaDB 11.8.
- Redis.
- Laravel Horizon for Redis queues.
- Laravel Reverb only for realtime/WebSocket features, not for queue processing.
- Vite + TypeScript + Lit for SDK widgets.
- Cloudflare proxy/WAF in front of `core.staratlasmedia.com`.
- Cloudflare Zero Trust Access for `/core-admin*`.
- WordPress sites are mostly behind Fastly.

## Immediate Rule

Start with planning and bootstrap. Do not attempt to implement all modules at once.

Preferred order:

1. Environment validation.
2. Laravel/Filament bootstrap.
3. Documentation and skeleton structure.
4. Core database schema.
5. Web Push migration import tooling.
6. WordPress bridge plugin skeleton.
7. SDK skeleton.
8. SSO skeleton.
9. Comments/newsletter/social modules later.

## Non-Negotiable Security Rules

- Never hardcode secrets.
- Never commit `.env`.
- Never print, log, or write VAPID private keys to README/API/docs/prompt output.
- Never write private keys into generated Markdown.
- Store VAPID private keys encrypted at rest.
- Store Web Push endpoint, `p256dh`, and `auth` encrypted at rest.
- Use `endpoint_hash = sha256(endpoint)` for lookup/deduplication.
- Do not expose global user IDs to frontend sites.
- Use exact-origin CORS allowlists.
- Do not use wildcard CORS for credentialed requests.
- Add `Vary: Origin` where CORS responses depend on request origin.
- Public APIs must be rate-limited.
- API calls from WordPress plugin to Core must use signed requests or API client tokens.
- Filament must not be available at `/admin`.
- Filament admin path must be `/core-admin`.
- `/core-admin*` must be compatible with Cloudflare Zero Trust Access.

## Critical Web Push Architecture Rule

Core is the backend. The Service Workers for Web Push must be served from the actual WordPress site origins.

Examples:

- `https://www.clubalfa.it/smart_sw.js`
- `https://www.clubalfa.it/automobili/smart_sw.js`
- `https://www.clubalfa.it/en/smart_sw.js`
- `https://www.motorisumotori.it/smart_sw.js`

A Service Worker served only from `core.staratlasmedia.com` cannot control pages on the editorial domains.

Core may generate worker/manifest content and expose it through the dashboard, but the WordPress bridge plugin must serve those assets locally from the site origin.

## WordPress Bridge Plugin

The WordPress plugin is required and will be named:

- **Star Atlas Core Bridge**

It must eventually provide:

- SDK injection;
- local serving of Service Worker files from WordPress origin;
- legacy Service Worker path preservation;
- manifest/PWA handling;
- `/pwa-start/` route handling;
- `/core-auth/callback` route for SSO;
- first-party WordPress session creation after SSO;
- post/page metadata export to Core;
- comment widget injection;
- push widget configuration;
- no-cache headers for Service Worker paths;
- WordPress event dispatching to Core.

## Domains / Origins

Use exact origins. For these two sites, use only `www` unless explicitly changed later:

- `https://www.clubalfa.it`
- `https://www.motorisumotori.it`

Other domains must be configured based on actual legacy data / production reality:

- `https://mbenz.it`
- `https://bmwisti.it`
- `https://robotica.news`
- `https://alfavirtualclub.it`

Do not automatically add apex/non-www variants.

## Cloudflare / Fastly

Core:

- Cloudflare proxy/WAF for all `core.staratlasmedia.com`.
- Cloudflare Zero Trust Access only for `/core-admin*`.
- Do not protect the entire Core domain with Access, because SDK, API, auth, webhooks, and image proxy must be publicly reachable.

WordPress/Fastly:

- Service Worker paths must be no-cache or max-age=0.
- `/pwa-start/`, `/en/pwa-start/`, `/core-auth/*` must be no-cache.

## SSO Rules

Do not build SSO only around an invisible iframe.

Priority:

1. Full-page redirect authorization-code-style flow as robust fallback.
2. Popup opened synchronously from user click/tap as preferred UX when available.
3. Hidden iframe/postMessage only for best-effort silent check.

Popup rule:

- Open `window.open('about:blank')` immediately in the click handler.
- Do not run `await`, `fetch`, or async operations before opening the popup.
- If popup returns `null`, fallback to full-page redirect.

Iframe/postMessage rule:

- Silent check only.
- Exact origin allowlist.
- Mandatory nonce/state.
- Return at most a one-time code.
- Never send long-lived tokens to the parent window.

Each WordPress site must maintain a local first-party session.

## PPID Rules

All sites are owned by Star Atlas Media, so both PPID types are useful:

- site-scoped PPID;
- network-scoped PPID.

However:

- `users.uuid` is the internal Core identity;
- `publisher_provided_ids` stores PPIDs;
- site-scoped PPID is the default external identifier;
- network-scoped PPID is used only where needed for network analytics, frequency capping, advertising, or internal matching.

Do not use a single global `users.ppid` field.

## Web Push Payload Rules

New Core Web Push payload standard:

```json
{
  "version": 1,
  "campaign_id": "cmp_123",
  "site_code": "clubalfa_it",
  "notification": {
    "title": "...",
    "body": "...",
    "url": "https://www.clubalfa.it/articolo/",
    "icon": "...",
    "badge": "...",
    "image": null,
    "tag": "..."
  }
}
```

Do not use in the new system:

- `data.target`;
- `payload.command`;
- `notification.data.click`;
- `notification.data.actions`;
- `eval`.

## Service Worker Rules

Proceed with Option B: clean immediately.

- Keep legacy paths.
- Replace contents with modern clean worker.
- Do not redirect Service Worker paths.
- Serve valid JavaScript directly on each path.
- No `eval`.
- No `fetch` handler.
- No offline/cache handling in bootstrap.
- Push-only worker.
- Click opens `notification.data.url`.

Legacy paths to preserve:

- `/smart_sw.js`
- `/automobili/smart_sw.js`
- `/en/smart_sw.js`

## Legacy Push Import Rules

Legacy import data is local:

- SQL dump: `/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/database/pushservice.sql`
- old files: `/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/push.staratlasmedia.com`

Import only modern Web Push platform IDs:

- `5` Chrome;
- `7` Firefox;
- `9` Opera;
- `10` Samsung Browser;
- `11` Edge.

Never import Safari legacy platform ID `6`.

Legacy VAPID rule:

- effective VAPID keys are app-specific;
- use global VAPID keys only if Chrome platform settings have `shared == 1`;
- otherwise use `apps_platfom.settings.vapid_public/private` for the app.

Legacy subscriptions are imported with initial status:

- `legacy_import_pending`

Modern campaigns must send only to:

- `core_sdk`;
- `core_reconfirmed`.

Do not implement LegacyPayloadAdapter.
Do not send legacy `data.target` payloads.

## ClubAlfa Push Group Rules

Unify Italian ClubAlfa root and Automobili:

- `appid 1` → `clubalfa_it`, section `main`;
- `appid 11` → `clubalfa_it`, section `automobili`.

Keep English separate:

- `appid 12` → `clubalfa_en`, section `en`.

New `/automobili/` subscriptions must use:

- `site_code = clubalfa_it`;
- `push_group = clubalfa_it`;
- `service_worker_url = /smart_sw.js`;
- `service_worker_scope = /`;
- same Italian manifest as root.

English `/en/` subscriptions must use:

- `site_code = clubalfa_en`;
- `push_group = clubalfa_en`;
- `service_worker_url = /en/smart_sw.js`;
- `service_worker_scope = /en/`.

## PWA / Manifest Rules

Use new stable manifest IDs now.
Do not preserve query-based legacy IDs.
Do not change these IDs later unless intentionally creating a new app identity.

ClubAlfa IT:

- `manifest_id = /pwa/clubalfa-it`
- `scope = /`
- `start_url = /pwa-start/?app=clubalfa_it`

ClubAlfa EN:

- `manifest_id = /pwa/clubalfa-en`
- `scope = /en/`
- `start_url = /en/pwa-start/?app=clubalfa_en`

The manifest `name` can change in the future. The manifest `id` should not.

## Push Subscription Context Tracking

Do not implement advanced push preferences yet.
Do implement subscription context tracking immediately.

Use one URL field only:

- `source_url`

Do not use:

- `canonical_url`;
- `external_post_url`;
- `external_post_id`;
- `post_id`.

Also store:

- `source_url_hash`;
- `source_title` nullable;
- `language`;
- `section`;
- `wp_terms_json` nullable;
- `referrer` nullable;
- `utm_json` nullable;
- `user_agent_hash` nullable.

## Push Migration Analytics

Prepare dashboard/widgets for legacy reconfirmation statistics:

- total legacy imported;
- pending;
- core_reconfirmed;
- superseded;
- core_sdk;
- invalid;
- reconfirmation rate;
- daily reconfirmation chart;
- pending vs reconfirmed chart;
- table by legacy app/site/section.

## Documentation

Keep updated:

- `README.md`
- `API.md`
- `AGENTS.md`
- `SECURITY.md`
- `WEB_PUSH_PWA_MIGRATION.md`
- `WORDPRESS_BRIDGE_PLUGIN.md`
- `SSO_AND_IDENTITY.md`
- `CODEX_PHASED_PROMPTS.md`

## Verification Before Completing Tasks

Run relevant checks when available:

- `php artisan test`
- `php artisan route:list`
- `composer test`
- `npm run build`
- `npm run typecheck` if configured
- `php artisan migrate --pretend` if DB is unavailable

Never force migrations if DB credentials are unavailable.
