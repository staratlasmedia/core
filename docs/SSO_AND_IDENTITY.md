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

## Endpoints to Plan

Core:

```text
GET  /auth/start
GET  /auth/callback/provider/{provider}
POST /auth/code/exchange
POST /auth/logout
GET  /auth/bridge
```

WordPress plugin:

```text
GET /core-auth/callback
GET /core-auth/session
POST /core-auth/logout
```

## Token Strategy

Bootstrap phase:

- keep simple;
- implement short-lived one-time authorization codes;
- store code hashes, not raw codes;
- expire quickly;
- mark consumed after successful exchange.

Later:

- add refresh/session tokens if needed;
- add Google/Apple providers;
- add login event analytics.

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
