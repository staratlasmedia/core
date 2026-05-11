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

## Plugin Responsibilities

### 1. SDK Injection

Inject Core SDK on public pages.

The SDK config must include:

```text
site_code
origin
language
section
source_url
source_title optional
WordPress terms/taxonomies optional
```

Example:

```html
<script>
  window.StarAtlasCore = {
    siteCode: "clubalfa_it",
    origin: "https://www.clubalfa.it",
    language: "it",
    section: "automobili",
    sourceUrl: "https://www.clubalfa.it/automobili/example/"
  };
</script>
<script src="https://core.staratlasmedia.com/sdk/core-sdk.<hash>.js" async></script>
```

### 2. Service Worker Local Serving

Maintain and serve Service Worker paths locally:

```text
/smart_sw.js
/automobili/smart_sw.js
/en/smart_sw.js
```

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

The plugin may expose:

```text
/manifest.webmanifest
/core-manifest.webmanifest?app=clubalfa_it
/en/manifest.webmanifest
```

Exact route strategy can be chosen during implementation.

### 4. PWA Start Routes

Create:

```text
/pwa-start/
/en/pwa-start/
```

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

Create:

```text
/core-auth/callback
/core-auth/session
/core-auth/logout
```

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

## Bootstrap Scope

In first Core bootstrap, do not build the full WordPress plugin.

But create:

- documentation;
- plugin skeleton plan;
- expected routes;
- expected config keys;
- maybe a `/wordpress-plugin/` or `/plugins/star-atlas-core-bridge/` skeleton if requested in a later phase.
