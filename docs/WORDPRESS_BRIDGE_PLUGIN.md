# Star Atlas Core Bridge — WordPress Plugin Plan

## Plugin Name

**Star Atlas Core Bridge**

## Why the Plugin Is Required

Core lives on:

```text
https://core.staratlasmedia.com
```

The editorial sites live on their own origins:

```text
https://www.clubalfa.it
https://www.motorisumotori.it
https://mbenz.it
...
```

Service Workers must be served from the same origin as the pages they control. Therefore Core cannot directly serve one Service Worker from `core.staratlasmedia.com` and control WordPress sites.

The plugin is the local bridge between WordPress and Core.

Core ships and manages one generic plugin only. Do not create custom plugin forks per site. Each WordPress installation is configured by consuming a Core-generated setup token.

## Phase 6 Plugin Skeleton Status

The repository now contains the generic plugin skeleton at:

```text
wordpress/star-atlas-core-bridge/
```

The plugin uses a namespaced `src/` structure and remains one installable package for every WordPress site. It does not fork by domain, language, section, push group, or Service Worker path.

Phase 6 implements:

- admin page “Star Atlas Core”;
- setup token claim form;
- config storage in `wp_options`;
- HMAC client skeleton for private Core calls;
- SDK injection on public pages;
- path-aware route interception driven by Core config;
- same-origin Service Worker and manifest skeleton responses;
- PWA start, auth callback, and push-click skeleton routes;
- standard WordPress update UI integration backed by Core update endpoints.

Core remains the source of site-specific configuration. The plugin detects `home_url('/')`, origin, and base path, then stores the claimed Core config and uses those values for local route handling.

## Core Setup Flow

1. Core admin creates a setup token in Filament for a site, origin, optional push group, language, section, and base path.
2. The admin installs the generic **Star Atlas Core Bridge** plugin in WordPress.
3. The admin pastes the setup token in the plugin.
4. The plugin calls `POST /api/bridge/setup/claim`.
5. Core validates token, origin, and base path, creates a bridge installation, and returns config plus credentials once.
6. Future plugin calls use the bridge installation ID and HMAC headers.

The setup token is shown once, stored only as a SHA-256 hash in Core, expires, and cannot be reused after it is consumed.

## Plugin Responsibilities

### 1. SDK Injection

Inject Core SDK on public pages.

The WordPress-side data must include:

```text
site_code
origin
language
section
source_url
api_base_url
source_title optional
vapid_public_key optional, required for push subscribe
WordPress terms/taxonomies optional
```

The JavaScript SDK receives the saved Core config plus page context on `window.StarAtlasCoreConfig`.
The SDK may map those values to camelCase keys or Web Component attributes internally.

Example:

```html
<script>
  window.StarAtlasCore = {
    siteCode: "clubalfa_it",
    origin: "https://www.clubalfa.it",
    language: "it",
    section: "automobili",
    sourceUrl: "https://www.clubalfa.it/automobili/example/",
    apiBaseUrl: "https://core.staratlasmedia.com/api/v1",
    serviceWorkerUrl: "/smart_sw.js",
    serviceWorkerScope: "/",
    vapidPublicKey: "PUBLIC_KEY_FROM_CORE"
  };
</script>
<script src="https://core.staratlasmedia.com/sdk/core-sdk.iife.js" defer></script>

<core-login-widget></core-login-widget>
<core-comments-widget></core-comments-widget>
<core-push-widget></core-push-widget>
```

### 2. Service Worker Local Serving

Maintain and serve Service Worker paths locally from the same WordPress origin.
The plugin must use paths returned by Core config, not a universal hardcoded path.

These paths must return JavaScript directly.

Do not redirect Service Worker URLs.

The returned worker must be the modern clean Core worker:

- no `eval`;
- no `data.target` standard;
- no fetch/cache/offline handler;
- push-only;
- click opens `notification.data.url`.

### 3. Manifest Serving

Serve site-specific manifests.

Core generates canonical manifest JSON from persistent Push Groups. The bridge plugin remains responsible for serving that content from the WordPress origin.

ClubAlfa IT:

```text
id: /pwa/clubalfa-it
scope: /
start_url: /pwa-start/?app=clubalfa_it
```

ClubAlfa EN:

```text
id: /pwa/clubalfa-en
scope: /en/
start_url: /en/pwa-start/?app=clubalfa_en
```

`/automobili/` must use the same Italian manifest as root.

The Phase 6 plugin skeleton intercepts the manifest path supplied by Core config and returns `application/manifest+json`.

### 4. PWA Start Routes

Create path-aware PWA start routes from Core config.

Purpose:

- when user opens PWA from installed icon;
- redirect to correct entry page.

ClubAlfa IT:

```text
/pwa-start/?app=clubalfa_it
```

Logic:

```text
if core_pwa_entry = /automobili/ → redirect /automobili/
else → redirect /
```

ClubAlfa EN:

```text
/en/pwa-start/?app=clubalfa_en
```

Logic:

```text
redirect /en/
```

### 5. SSO Callback

Create a path-aware `/core-auth/callback` skeleton relative to the installation base path.

Responsibilities:

- accept one-time code from Core;
- exchange it server-to-server with Core;
- validate state/nonce;
- create first-party WordPress session;
- expose local session state to SDK;
- support logout.

### 6. Push Subscription Context

When user subscribes to push, plugin/SDK must pass:

```text
source_url
source_title nullable
language
section
wp_terms_json nullable
referrer nullable
utm_json nullable
```

Use only `source_url` as the URL field.

Do not send:

```text
canonical_url
external_post_url
external_post_id
post_id
```

### 7. WordPress Events

Eventually send events to Core:

- post published;
- post updated;
- taxonomy/category data;
- comment widget context;
- article metadata.

Do not implement all events in the first bootstrap.

### 8. No-Cache Headers

Service Worker paths must be no-cache or max-age=0.

Recommended plugin behavior:

```text
Cache-Control: no-cache, no-store, must-revalidate
Pragma: no-cache
Expires: 0
```

For manifests, use moderate/no-cache during rollout.

### 10. Private Plugin Updates

The plugin includes an update client skeleton and integrates with:

```text
pre_set_site_transient_update_plugins
plugins_api
upgrader_process_complete
```

Update checks call Core with bridge credentials and HMAC headers. Core returns metadata and, when an update is available, a temporary signed package URL. The plugin never places the raw bridge secret in URLs and does not rely on WordPress.org.

### 9. Core Dashboard Integration

Core/Filament must provide for each site/push group:

- Service Worker preview;
- Service Worker download;
- Manifest preview;
- Manifest download;
- plugin configuration;
- site API key / token;
- integration instructions;
- current version;
- changelog.

The plugin should be able to fetch current config from Core and serve it locally.

Phase 4B adds Filament management for:

- bridge setup tokens;
- bridge installations;
- bridge config versions and JSON previews;
- plugin packages;
- plugin releases;
- plugin update downloads.

## Bridge API Skeleton

```text
POST /api/bridge/setup/claim
GET  /api/bridge/config
POST /api/bridge/heartbeat
POST /api/bridge/events
GET  /api/bridge/plugin/update-check
GET  /api/bridge/plugin/info
GET  /api/bridge/plugin/download/{token}
```

`/setup/claim` is rate-limited and uses the one-time setup token. The other private bridge endpoints use this HMAC header skeleton:

```text
X-Core-Bridge-Id
X-Core-Timestamp
X-Core-Nonce
X-Core-Signature
```

The signature covers method, path, timestamp, nonce, and body hash.

## Private Plugin Update Server

Core is the private update server for **Star Atlas Core Bridge**. WordPress must be able to update the plugin through the standard Dashboard updates UI without WordPress.org hosting.

Core tracks:

- plugin package metadata;
- stable, beta, and internal releases;
- published/revoked release status;
- ZIP path, SHA-256 checksum, and size metadata;
- temporary non-guessable download tokens;
- download attempts linked to bridge installations when available.

Default update channel is `stable`. Download URLs must be temporary and package files must not be exposed publicly.

## Phase Boundaries

Phase 6 is a functional skeleton only. It does not implement full SSO, comments, push preferences, push sending, ZIP packaging, site branding UI, or full auto-update policy logic.
