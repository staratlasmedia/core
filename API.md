# Core API

Bootstrap API surface for Star Atlas Core.

## Base URL

```text
https://core.staratlasmedia.com/api/v1
```

## Current Bootstrap Endpoints

```text
GET /health
GET /sites/{siteCode}/bootstrap
```

`/health` returns a minimal service status.

`/sites/{siteCode}/bootstrap` returns non-secret site bootstrap metadata such as origin, language, push group, manifest ID, and Service Worker path.

## CORS

API CORS is exact-origin only. Bootstrap allowlist:

```text
https://www.clubalfa.it
https://www.motorisumotori.it
```

CORS responses include `Vary: Origin` and never use wildcard origins for credentialed requests.

## Authentication Direction

Future WordPress-to-Core calls must use signed requests or API client tokens. No secrets belong in frontend SDK config.
