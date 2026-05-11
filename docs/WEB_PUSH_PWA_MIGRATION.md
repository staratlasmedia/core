# Web Push, PWA, Service Worker and Legacy Migration Plan

## Final Direction

Use **Option B — 100% clean immediately**.

This means:

- keep legacy Service Worker paths;
- replace their contents with a clean modern worker;
- do not keep `eval`;
- do not use `data.target` in the new payload;
- do not implement a LegacyPayloadAdapter;
- import legacy subscriptions as historical/pending records;
- send modern campaigns only to subscriptions confirmed by the new SDK or reconfirmed by the new worker flow.

## Current Legacy Service Worker Observation

The current workers for:

```text
https://www.clubalfa.it/smart_sw.js
https://www.clubalfa.it/automobili/smart_sw.js
```

are effectively identical.

Legacy worker behavior:

- `install` calls `skipWaiting()`;
- `push` parses `event.data.text()` as JSON;
- `push` calls `showNotification(payload.title, payload)`;
- `notificationclick` opens `event.notification.data.target`;
- legacy worker contains `eval` calls for `payload.command`, `data.click`, and action handlers.

New Core system must not use these legacy code paths.

## New Payload Standard

Modern Core Web Push payload:

```json
{
  "version": 1,
  "campaign_id": "cmp_123",
  "site_code": "clubalfa_it",
  "notification": {
    "title": "Nuova Alfa Romeo Junior",
    "body": "Scopri tutte le novità.",
    "url": "https://www.clubalfa.it/nuova-alfa-romeo-junior/",
    "icon": "https://www.clubalfa.it/android-chrome-192x192.png",
    "badge": "https://www.clubalfa.it/android-chrome-96x96.png",
    "image": null,
    "tag": "clubalfa_it_cmp_123"
  }
}
```

Forbidden in new payloads:

```text
data.target
payload.command
notification.data.click
notification.data.actions
eval
```

## New Service Worker

The new worker must be:

- push-only;
- no `eval`;
- no `fetch` handler;
- no cache/offline behavior in bootstrap;
- no legacy `data.target` standard;
- based on `notification.url`;
- click handler opens `notification.data.url`;
- `install` uses `skipWaiting()`;
- `activate` uses `clients.claim()`.

Reference worker template:

```js
"use strict";

const CORE_SW_VERSION = "core-sw-v1";

self.addEventListener("install", event => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener("activate", event => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener("push", event => {
  event.waitUntil((async () => {
    let payload = {};

    try {
      payload = event.data ? event.data.json() : {};
    } catch (_) {
      payload = {};
    }

    const notification = payload.notification || {};
    const title = notification.title || "Nuova notifica";
    const url = notification.url || "/";

    await self.registration.showNotification(title, {
      body: notification.body || "",
      icon: notification.icon || "/android-chrome-192x192.png",
      badge: notification.badge || "/android-chrome-96x96.png",
      image: notification.image || undefined,
      tag: notification.tag || payload.campaign_id || undefined,
      data: {
        url,
        campaign_id: payload.campaign_id || null,
        site_code: payload.site_code || null,
        sw_version: CORE_SW_VERSION
      }
    });
  })());
});

self.addEventListener("notificationclick", event => {
  event.notification.close();

  const url = event.notification.data?.url || "/";

  event.waitUntil(
    clients.matchAll({
      type: "window",
      includeUncontrolled: true
    }).then(clientList => {
      for (const client of clientList) {
        if (client.url === url && "focus" in client) {
          return client.focus();
        }
      }

      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
```
## Web Push Sending Library

Core uses `minishlink/web-push` as the delivery engine.

The package is used directly through custom Laravel services and queue jobs, not through Laravel's generic Notification channel abstraction.

This is required because Core must support:

- imported legacy subscriptions;
- multiple VAPID key sets;
- per-site/per-origin delivery;
- push groups;
- subscription reconfirmation states;
- delivery logs;
- invalid endpoint cleanup;
- campaign-level batching;
- Redis/Horizon queue execution.


## Service Worker Paths

Keep these legacy paths alive:

```text
/smart_sw.js
/automobili/smart_sw.js
/en/smart_sw.js
```

The path stays. The content changes.

Do not redirect these paths. Serve valid JavaScript directly from each path.

For new Italian ClubAlfa subscriptions from root and `/automobili/`, use:

```text
service_worker_url: /smart_sw.js
service_worker_scope: /
```

For English `/en/`, use:

```text
service_worker_url: /en/smart_sw.js
service_worker_scope: /en/
```

`/automobili/smart_sw.js` exists for legacy path preservation and update/migration, not for new Italian subscription registration.

## Legacy Import Statuses

Legacy subscriptions must be imported, but initial state is not immediately dispatchable.

Initial status:

```text
legacy_import_pending
```

Send modern campaigns only to:

```text
core_sdk
core_reconfirmed
```

Do not send to:

```text
legacy_import_pending
```

Rationale:

- We are not keeping legacy payload compatibility.
- We are replacing workers with clean modern code.
- Users must revisit the site and be reconfirmed by the new SDK/worker before receiving modern campaigns.

## Subscription Sources

```text
legacy_import       Imported from old DB; initially pending.
core_sdk            Created by the new SDK/plugin.
core_reconfirmed    Legacy subscription or user reconfirmed by the new SDK/plugin.
```

Statuses:

```text
legacy_import_pending
active
core_reconfirmed
superseded
invalid
unsubscribed
```

## Legacy Data Source

Legacy data is local.

SQL dump:

```text
/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/database/pushservice.sql
```

Old application files:

```text
/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/push.staratlasmedia.com
```

Recommended import process:

1. Use the already-loaded legacy DB through Laravel connection `legacy_push`.
2. Configure the legacy DB through `LEGACY_PUSH_DB_*` env values; do not hardcode credentials.
3. Do not mix legacy tables into the new Core DB.
4. Use the Phase 3 Artisan commands:
   - `php artisan core:legacy-push:inspect --appids=1,11,12,10`
   - `php artisan core:legacy-push:import --dry-run --appids=1,11,12,10`
   - `php artisan core:legacy-push:import --appids=1,11,12,10`
5. Parse `devices.token` as JSON-first.
6. Extract endpoint, `p256dh`, `auth`.
7. Create `vapid_key_sets` using the effective per-app VAPID rule.
8. Store secrets encrypted.
9. Store `endpoint_hash` for lookup/deduplication.
10. Exclude Safari.

## Legacy Tables

Legacy table:

```text
devices
```

Important columns:

```text
id
userid
appid
platid
token
firebase
created_date
last_active_time
status
```

`devices.token` is JSON-first and contains:

```json
{
  "endpoint": "...",
  "auth": "...",
  "p256dh": "..."
}
```

or equivalent nested `keys` shape.

Import only:

```text
5  Chrome
7  Firefox
9  Opera
10 Samsung Browser
11 Edge
```

Exclude entirely:

```text
6 Safari
```

## Legacy VAPID Rule

Effective VAPID keys are app-specific.

Rule:

```text
Read apps_platfom.settings for appid + platid 5.
If shared == 1 → use global setting.vapid_public/private.
Else → use settings.vapid_public/private for that app.
```

Do not use global VAPID keys unless `shared == 1`.

Never print or commit VAPID private keys.

Dry-run and inspect output must show only counts, VAPID source labels, safe key fingerprints, and `endpoint_hash` samples. It must never print raw endpoints, `auth`, `p256dh`, or VAPID private key values.

## ClubAlfa Mapping

Unify Italian ClubAlfa root and Automobili:

```text
appid 1  → clubalfa_it, section main
appid 11 → clubalfa_it, section automobili
```

Keep English separate:

```text
appid 12 → clubalfa_en, section en
```

### appid 1

```text
site_code: clubalfa_it
origin: https://www.clubalfa.it
path_prefix: /
language: it
section: main
merge_group: clubalfa_it
legacy service worker: /smart_sw.js
legacy service worker scope: /
legacy source: legacy_import
```

### appid 11

```text
site_code: clubalfa_it
origin: https://www.clubalfa.it
path_prefix: /automobili/
language: it
section: automobili
merge_group: clubalfa_it
legacy service worker: /automobili/smart_sw.js
legacy service worker scope: /automobili/
legacy source: legacy_import
```

### appid 12

```text
site_code: clubalfa_en
origin: https://www.clubalfa.it
path_prefix: /en/
language: en
section: en
merge_group: clubalfa_en
legacy service worker: /en/smart_sw.js
legacy service worker scope: /en/
legacy source: legacy_import
```

Do not merge `clubalfa_en` with `clubalfa_it`.

## New ClubAlfa Subscription Rules

### Root ClubAlfa

```text
site_code: clubalfa_it
language: it
section: main
push_group: clubalfa_it
manifest_id: /pwa/clubalfa-it
service_worker_url: /smart_sw.js
service_worker_scope: /
source: core_sdk
```

### ClubAlfa Automobili

```text
site_code: clubalfa_it
language: it
section: automobili
push_group: clubalfa_it
manifest_id: /pwa/clubalfa-it
service_worker_url: /smart_sw.js
service_worker_scope: /
source: core_sdk
```

### ClubAlfa EN

```text
site_code: clubalfa_en
language: en
section: en
push_group: clubalfa_en
manifest_id: /pwa/clubalfa-en
service_worker_url: /en/smart_sw.js
service_worker_scope: /en/
source: core_sdk
```

## PWA Manifests

Use new stable manifest IDs now.

### ClubAlfa IT Manifest

Root and `/automobili/` share this PWA.

```json
{
  "id": "/pwa/clubalfa-it",
  "name": "ClubAlfa.it",
  "short_name": "ClubAlfa",
  "description": "Notizie auto, Alfa Romeo, Stellantis e motori.",
  "categories": ["news", "automotive"],
  "background_color": "#eceff1",
  "theme_color": "#d12711",
  "display": "standalone",
  "scope": "/",
  "start_url": "/pwa-start/?app=clubalfa_it",
  "lang": "it-IT",
  "dir": "ltr",
  "icons": [
    {
      "src": "/android-chrome-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/clubalfa-pwa-logo-192-msk.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable"
    },
    {
      "src": "/android-chrome-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/clubalfa-pwa-logo-512-msk.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
```

### ClubAlfa EN Manifest

```json
{
  "id": "/pwa/clubalfa-en",
  "name": "ClubAlfa - Global",
  "short_name": "ClubAlfa",
  "description": "Automotive news in English.",
  "categories": ["news", "automotive"],
  "background_color": "#eceff1",
  "theme_color": "#d12711",
  "display": "standalone",
  "orientation": "portrait",
  "scope": "/en/",
  "start_url": "/en/pwa-start/?app=clubalfa_en",
  "lang": "en",
  "dir": "ltr",
  "icons": [
    {
      "src": "/android-chrome-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/clubalfa-pwa-logo-192-msk.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable"
    },
    {
      "src": "/android-chrome-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/clubalfa-pwa-logo-512-msk.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
```

The manifest `name` can change later.
The manifest `id` should not change.

## PWA Start Routes

The WordPress plugin must create:

```text
/pwa-start/
/en/pwa-start/
```

These routes are for launching the installed PWA from its icon.

They are not used for push notification click navigation.

### ClubAlfa IT

```text
/pwa-start/?app=clubalfa_it
```

Behavior:

```text
if core_pwa_entry = /automobili/ → redirect to /automobili/
else → redirect to /
```

### ClubAlfa EN

```text
/en/pwa-start/?app=clubalfa_en
```

Behavior:

```text
redirect to /en/
```

## Subscription Context Tracking

Do not implement advanced preferences yet.

Immediately track where a user subscribed.

Use only one URL field:

```text
source_url
```

Do not use:

```text
canonical_url
external_post_url
external_post_id
post_id
```

Store:

```text
source_url
source_url_hash
source_title nullable
language
section
wp_terms_json nullable
referrer nullable
utm_json nullable
user_agent_hash nullable
```

## Push Migration Analytics

Filament should include a “Push Migration” area.

Metrics:

```text
total legacy imported
legacy pending
core_reconfirmed
superseded
core_sdk
invalid
unsubscribed
reconfirmation rate
daily reconfirmation chart
pending vs reconfirmed chart
table by legacy_appid / site / section
```

Formula:

```text
reconfirmation_rate = (core_reconfirmed + superseded) / legacy_imported_total
```

Create table:

```text
push_reconfirmation_events
```

Fields:

```text
id
site_id
push_subscription_id nullable
legacy_push_app_id nullable
legacy_appid nullable
legacy_device_id nullable
old_status
new_status
match_method
source_url
source_url_hash
language
section
origin
service_worker_url
service_worker_scope
manifest_id
sw_version
user_agent_hash
created_at
```

## Future Question: ClubAlfa EN on Subdomain

If `/en/` is later migrated to `https://en.clubalfa.it`, it becomes a new origin.

Consequences:

- push permission does not transfer automatically;
- PWA install does not transfer automatically;
- Service Worker does not transfer automatically;
- push subscription does not transfer automatically.

Core must be ready by using stable `site_code = clubalfa_en` and `site_origins` records.

Today:

```text
site_code: clubalfa_en
origin: https://www.clubalfa.it
path_prefix: /en/
scope: /en/
start_url: /en/pwa-start/?app=clubalfa_en
service_worker_url: /en/smart_sw.js
```

Possible future:

```text
site_code: clubalfa_en
origin: https://en.clubalfa.it
path_prefix: /
scope: /
start_url: /pwa-start/?app=clubalfa_en
service_worker_url: /smart_sw.js
```

This is only a future possibility, not part of the current implementation.
