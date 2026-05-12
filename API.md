# Core API

Bootstrap API surface for Star Atlas Core.

## Base URL

```text
https://core.staratlasmedia.com/api/v1
```

Bridge setup and private plugin update endpoints live under:

```text
https://core.staratlasmedia.com/api/bridge
```

## Current Bootstrap Endpoints

```text
GET /health
GET /sites/{siteCode}/bootstrap
GET /comments/threads/resolve
GET /comments
```

`/health` returns a minimal service status.

`/sites/{siteCode}/bootstrap` returns non-secret site bootstrap metadata such as origin, language, push group, manifest ID, and Service Worker path.

`/comments/threads/resolve` resolves the Phase 8 comment thread for `site_code + source_url` using normalized `source_url` and returns disabled state when effective settings disable comments.

`/comments` returns approved comments only for the resolved thread. Public reads are exact-origin CORS and rate-limited.

## WordPress Bridge Endpoints

```text
POST /api/bridge/setup/claim
GET  /api/bridge/config
POST /api/bridge/heartbeat
POST /api/bridge/events
GET  /api/bridge/plugin/update-check
GET  /api/bridge/plugin/info
GET  /api/bridge/plugin/download/{token}
POST /api/bridge/comments
POST /api/bridge/comments/{comment}/reactions
DELETE /api/bridge/comments/{comment}/reactions/{reactionType}
POST /api/bridge/comments/{comment}/reports
```

`/setup/claim` consumes a one-time setup token generated in Filament and returns bridge installation credentials only once.

All follow-up bridge endpoints use the HMAC header skeleton:

```text
X-Core-Bridge-Id
X-Core-Timestamp
X-Core-Nonce
X-Core-Signature
```

Plugin download URLs use temporary non-guessable tokens and are not public package listings.

Phase 8 comment writes are server-to-server Bridge calls only. `POST /api/bridge/comments` requires HMAC, validates the bridge installation and effective comment settings, stores only hashes for IP/user agent/email, and creates a moderation event for each created comment.

## PWA Asset Generation

Core currently generates Service Worker and manifest content inside the Filament admin only. Preview and download remain under `/core-admin`; public WordPress origins must serve the generated files through the Star Atlas Core Bridge plugin.

## CORS

API CORS is exact-origin only. Bootstrap allowlist:

```text
https://www.clubalfa.it
https://www.motorisumotori.it
```

CORS responses include `Vary: Origin` and never use wildcard origins for credentialed requests.

## Authentication Direction

Future WordPress-to-Core calls must use signed requests or API client tokens. No secrets belong in frontend SDK config.
