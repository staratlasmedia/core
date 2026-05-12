# SSO and Identity Plan

## Goal

Core acts as the central Identity Provider for Star Atlas Media-owned sites.

Each WordPress site keeps a first-party local session. Core does not rely exclusively on third-party cookies or invisible iframes.

## SSO Priority

Use three layers:

1. **Full-page redirect**
   - Most reliable fallback.
   - Authorization-code-style flow.

2. **Popup from user click/tap**
   - Preferred UX when available.
   - Must be opened synchronously from a user action.

3. **Iframe/postMessage silent check**
   - Best-effort only.
   - Never the only login mechanism.

## Flow: Full-Page Redirect

1. User clicks login on a WordPress site.
2. SDK/plugin sends user to:

```text
https://core.staratlasmedia.com/auth/start?site_id=...&return_url=...&state=...
```

3. User logs in on Core.
4. Core creates a short-lived one-time code.
5. Core redirects back to WordPress:

```text
https://www.clubalfa.it/core-auth/callback?code=...&state=...
```

6. WordPress plugin exchanges code server-to-server with Core.
7. Core returns session/user payload to the server-side plugin.
8. WordPress sets a first-party session cookie.
9. SDK detects local authenticated state.

## Flow: Popup UX

The popup must be opened immediately during the click handler.

Correct pattern:

```js
loginButton.addEventListener("click", () => {
  const popup = window.open(
    "about:blank",
    "core_login",
    "popup,width=520,height=720"
  );

  if (!popup) {
    window.location.href = buildCoreLoginUrl();
    return;
  }

  popup.location.href = buildCoreLoginUrl();
});
```

Rules:

- Do not do `await`, `fetch`, or async work before `window.open()`.
- If popup is blocked, fallback to full-page redirect.
- Popup must return at most a one-time code.
- Do not pass long-lived tokens to the parent.

## Flow: Iframe / postMessage Silent Check

Iframe may be used for best-effort silent identity checks only.

Rules:

- iframe origin: `https://core.staratlasmedia.com`.
- parent origins must be exact allowlisted.
- use nonce/state per request.
- validate `event.origin` strictly.
- return only a short-lived one-time code or anonymous status.
- do not send long-lived access tokens.
- if third-party cookies/storage are blocked, fallback to popup/redirect.

## WordPress First-Party Session

Every WordPress site needs its own first-party session after SSO.

Reason:

- Core cookie is first-party only on `core.staratlasmedia.com`.
- It cannot reliably act as a session cookie inside all editorial sites.
- Browser privacy restrictions make invisible cross-site sessions unreliable.

The WordPress plugin creates local session state after server-to-server code exchange.

## Comments Identity Contract

Phase 8 comments use the Phase 7 SSO skeleton through the WordPress Bridge. The preferred write path is: Core SSO login, WordPress first-party local session, SDK post to local Bridge comments endpoint, Bridge HMAC write to Core, and Core storage without exposing global `users.id` to the browser.

Guest comments remain disabled by default and are not a production flow in Phase 8.

## Endpoints to Plan

Core:

```text
GET  /auth/start
GET  /auth/popup
GET  /auth/silent-check
POST /auth/exchange-code
POST /auth/logout
POST /auth/passkey/register/options
POST /auth/passkey/register/verify
POST /auth/passkey/login/options
POST /auth/passkey/login/verify
POST /auth/magic-link/request
POST /auth/magic-link/verify
POST /auth/password/login
POST /auth/password/register
POST /auth/password/forgot
POST /auth/password/reset
GET  /auth/oauth/{provider}/redirect
GET  /auth/oauth/{provider}/callback
```

WordPress plugin:

```text
GET /core-auth/callback
GET /core-auth/session
POST /core-auth/logout
```

## Token Strategy

Phase 7 skeleton:

- provider records exist but are seeded `status=disabled` and `is_public=false`;
- disabled provider routes return a safe not-available response instead of starting a real flow;
- one-time authorization codes are short-lived;
- code values are stored only as SHA-256 hashes;
- `redirect_uri` is written while `redirect_url` remains for compatibility;
- server-to-server code exchange requires Star Atlas Core Bridge HMAC.

Later:

- add refresh/session tokens if needed;
- add Google/Apple providers;
- add login event analytics.

## Configurable Auth Providers

Core stores auth provider configuration in `auth_providers` and optional scoped overrides in `auth_provider_site_settings`.

Initial providers:

- `passkey`
- `google`
- `apple`
- `magic_link`
- `password`
- `facebook`

All are configurable from Filament but disabled and non-public until production implementations and secrets are intentionally enabled.

Secrets and private provider configuration are stored in encrypted fields and are write-only in Filament. Existing values must never be displayed in full after save.

## Passkey / WebAuthn

Phase 7 does not install `web-auth/webauthn-lib` and does not implement full WebAuthn verification. It creates only schema, configuration, services, endpoint skeletons, and documentation for a later production pass.

Current relying party configuration:

```text
rp_id  = core.staratlasmedia.com
origin = https://core.staratlasmedia.com
```

No special DNS record is required for this WebAuthn model beyond normal DNS and valid HTTPS for `core.staratlasmedia.com`. Cloudflare proxy is compatible when the public host, HTTPS, and origin observed by the browser stay coherent.

Operational checks:

- `/auth/*` must not be protected by Cloudflare Zero Trust Access;
- Zero Trust Access remains scoped to `/core-admin*`;
- avoid aggressive WAF/challenge behavior on `/auth/passkey/*`, `/auth/oauth/*`, and `/auth/exchange-code`, because challenges can break WebAuthn ceremonies, OAuth callbacks, popup/redirect flows, and server-to-server POSTs.

## Bridge Callback Resolution

Core derives the WordPress callback from the bridge installation instead of hardcoded section paths.

Priority:

1. `wp_base_path`, if a future schema adds it;
2. `detected_base_path`, currently stored by Phase 6;
3. `/` fallback.

The resulting callback is:

```text
{bridge_installation.origin}{base_path}/core-auth/callback
```

## PPID Strategy

All supported sites are owned by Star Atlas Media.

Therefore both PPID types are useful:

- **site-scoped PPID**;
- **network-scoped PPID**.

### Site-Scoped PPID

A user receives a different PPID per site.

Example:

```text
user U123 + www.clubalfa.it       → ppid A
user U123 + www.motorisumotori.it → ppid B
user U123 + mbenz.it              → ppid C
```

Use as default external identifier.

Benefits:

- less cross-site exposure;
- cleaner privacy posture;
- safer to expose to frontend/advertising contexts;
- easier per-site governance.

### Network-Scoped PPID

A user receives one PPID for the whole Star Atlas Media network.

Example:

```text
user U123 + network → ppid N
```

Use for:

- internal analytics;
- cross-site frequency capping;
- centralized advertising use cases;
- identity matching where allowed.

Do not expose indiscriminately to frontend sites.

## Recommended Schema

```text
users
- id
- uuid
- email nullable
- name nullable
- status
- metadata JSON nullable
- soft deletes
- timestamps

publisher_provided_ids
- id
- user_id
- site_id nullable
- ppid
- scope ENUM('site','network')
- version
- created_at
- rotated_at nullable
```

Generation concept:

```text
site_ppid    = HMAC(secret_version, user_uuid + ':' + site_id)
network_ppid = HMAC(secret_version, user_uuid + ':network')
```

## Important Rule

Do not create a single global `users.ppid` field.
