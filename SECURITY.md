# Security

## Secrets

- Never commit `.env`.
- Never commit database passwords, API keys, VAPID private keys, SQL dumps, backups, `vendor/`, or `node_modules/`.
- `.env.example` must contain placeholders only.
- Rotate any credential shared outside the final secrets store before production.

## Web Push

- VAPID private keys must be encrypted at rest.
- Web Push endpoint, `p256dh`, and `auth` values must be encrypted at rest.
- Use `endpoint_hash = sha256(endpoint)` for lookup and deduplication.
- Do not write VAPID private keys into generated documentation.

## CORS And Public APIs

- Use exact-origin allowlists.
- Do not use wildcard CORS for credentialed requests.
- Add `Vary: Origin` when the CORS response depends on request origin.
- Rate-limit public API routes.

## Admin

- Filament must be available at `/core-admin`, never `/admin`.
- `/core-admin*` must remain compatible with Cloudflare Zero Trust Access.
- Horizon is placed below `/core-admin/horizon` during bootstrap.
