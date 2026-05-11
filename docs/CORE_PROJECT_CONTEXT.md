# Core Project Context — Star Atlas Media System

## Project Name

**Core** — Star Atlas Media System.

## Hosting

Core will be hosted on:

```text
https://core.staratlasmedia.com
```

The project root is currently empty or almost empty. Laravel, Filament, SDK tooling, documentation, and database schema must be created from zero.

## Business Context

Star Atlas Media runs a network of editorial websites, mostly WordPress-based, with high traffic and several existing systems that will be progressively centralized into Core.

Core is not initially a client-facing SaaS product. It is an internal centralized platform for Star Atlas Media-owned sites.

## Core Modules Planned

Core will eventually manage:

1. **Identity / SSO**
   - Central user identity.
   - Login across Star Atlas Media sites.
   - Google/Apple login later.
   - WordPress first-party sessions via bridge plugin.

2. **Web Push**
   - Import legacy Web Push subscriptions.
   - New Web Push subscriptions via modern SDK.
   - Campaign management.
   - Delivery logs.
   - Reconfirmation analytics.
   - PWA and manifest generation.

3. **Newsletter**
   - Amazon SES for sending.
   - Amazon SNS webhooks for bounce/complaint handling.
   - Lists and subscriber events.

4. **Comments**
   - Proprietary comment system replacing Disqus.
   - Centralized identity.
   - Moderation in Filament.
   - SDK/Web Components embedded on WordPress articles.

5. **Social Automation**
   - Future module.
   - Do not implement in bootstrap.

6. **Frontend SDK**
   - TypeScript + Lit.
   - Built with Vite.
   - Embeddable on WordPress sites.
   - Shadow DOM Web Components.

7. **WordPress Bridge Plugin**
   - Name: **Star Atlas Core Bridge**.
   - Required for Service Workers, SSO callback, SDK config, metadata, and WordPress integration.

## Target Stack

```text
Backend: Laravel 13
Admin: Filament 5
Database: MariaDB 11.8
Queue/Cache: Redis
Queue UI: Laravel Horizon
Realtime: Laravel Reverb only if needed later
SDK: Vite + TypeScript + Lit
CDN/WAF: Cloudflare for Core
Admin Protection: Cloudflare Zero Trust Access on /core-admin*
WordPress CDN: mostly Fastly
```

## Infrastructure Notes

Server resources currently planned:

```text
8 Core CPU
16 GB RAM
MariaDB 11.8
Redis
```

Storage:

```text
Local storage on core.staratlasmedia.com
Public assets served through controlled endpoints
Cloudflare CDN/WAF in front
```

## Domains / Sites

Known editorial sites:

```text
https://www.clubalfa.it
https://www.motorisumotori.it
https://mbenz.it
https://bmwisti.it
https://robotica.news
https://alfavirtualclub.it
```

Important:

- `www.clubalfa.it` has always used `www`.
- `www.motorisumotori.it` has always used `www`.
- Do not automatically add non-www origins for these two unless explicitly required later.

## ClubAlfa Current Situation

ClubAlfa currently has multiple WordPress installations/sections under the same origin:

```text
https://www.clubalfa.it/              Italian main site
https://www.clubalfa.it/automobili/   Italian Automobili section
https://www.clubalfa.it/en/           English / Global section
```

Project decision:

```text
www.clubalfa.it + /automobili/ → unified in Core as clubalfa_it
/en/                           → separate in Core as clubalfa_en
```

## Web Push Legacy Decision

Proceed with clean modern migration:

```text
Option B — 100% clean immediately
```

Meaning:

- Keep old Service Worker paths.
- Replace old contents with clean modern Service Worker code.
- Do not keep `eval`.
- Do not use `data.target` in the new payload standard.
- Do not implement LegacyPayloadAdapter.
- Import legacy subscriptions but keep them initially as `legacy_import_pending`.
- Send modern campaigns only to `core_sdk` and `core_reconfirmed` subscriptions.

## PWA Decision

Use new stable manifest IDs now.

ClubAlfa IT:

```text
manifest_id: /pwa/clubalfa-it
scope: /
start_url: /pwa-start/?app=clubalfa_it
```

ClubAlfa EN:

```text
manifest_id: /pwa/clubalfa-en
scope: /en/
start_url: /en/pwa-start/?app=clubalfa_en
```

`/automobili/` will point to the same Italian manifest as root.

## Push Preferences Decision

Do not implement advanced push preferences now.

Advanced preferences for brands, macro-categories, and taxonomy-based interests will be studied later based on the actual WordPress categories/taxonomies of the sites.

However, subscription context tracking must be implemented immediately.

Use one URL field:

```text
source_url
```

No:

```text
canonical_url
external_post_url
external_post_id
post_id
```

## Documentation Package

The repository should contain documentation files that preserve this project context:

- `AGENTS.md`
- `CORE_PROJECT_CONTEXT.md`
- `CORE_ARCHITECTURE.md`
- `WEB_PUSH_PWA_MIGRATION.md`
- `SSO_AND_IDENTITY.md`
- `WORDPRESS_BRIDGE_PLUGIN.md`
- `CODEX_PHASED_PROMPTS.md`
