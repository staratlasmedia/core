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
POST /newsletter/subscribe
POST /newsletter/confirm
POST /newsletter/unsubscribe
GET|POST /newsletter/preferences
```

`/health` returns a minimal service status.

`/sites/{siteCode}/bootstrap` returns non-secret site bootstrap metadata such as origin, language, push group, manifest ID, and Service Worker path.

`/comments/threads/resolve` resolves the Phase 8 comment thread for `site_code + source_url` using normalized `source_url` and returns disabled state when effective settings disable comments.

`/comments` returns approved comments only for the resolved thread. Public reads are exact-origin CORS and rate-limited.

`/newsletter/subscribe` creates a pending or subscribed Core newsletter subscriber only when effective newsletter settings are enabled. It accepts `site_code`, `email`, optional `list_code`, `language`, `source_url`, `consent_version`, and selected `topic_ids`. When effective settings require consent, `consent_version` is mandatory. Core stores IP and user-agent hashes for consent evidence.

`/newsletter/confirm` consumes a hashed confirmation token for double opt-in.

`/newsletter/unsubscribe` unsubscribes by a valid active unsubscribe token only. Subscriber UUID alone is not accepted by the public endpoint.

`/newsletter/preferences` returns active, visible newsletter audience topics for a `site_code`. POST can save topic preferences for a subscriber UUID, but only for topics allowed in the newsletter channel.

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
POST /api/bridge/newsletter/subscribe
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

Phase 9 newsletter bridge calls reuse HMAC. WordPress must pass Core site/list/topic context to Core and must not maintain independent newsletter lists.

## Webhooks And Tracking

```text
POST /api/webhooks/aws/sns/ses
GET  /newsletter/o/{token}.gif
GET  /newsletter/c/{token}
```

The SES/SNS webhook is public but must verify Amazon SNS signatures before processing. It stores payload/signature hashes, logs subscription confirmations without auto-confirming by default, and must not be protected by Cloudflare Zero Trust Access.

Open/click endpoints use raw tokens only in URLs; Core stores only token hashes. Click redirects use the target URL stored in token metadata rather than trusting arbitrary request parameters. Open rate is an image-pixel metric and does not guarantee reading.

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
