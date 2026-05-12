# Newsletter Editorial Platform

Phase 9 adds the Core foundation for a multi-site editorial newsletter platform. It is functional where safe and intentionally conservative where production risk is high.

## Safety Defaults

- `newsletter_enabled=false` unless an explicit `newsletter_settings` row enables a scope.
- `send_enabled=false` unless an admin enables a sender/settings scope.
- `double_opt_in=true` and `require_consent=true` by default.
- `allow_import=false`, `ai_generation_enabled=false`, `rss_import_enabled=false`, `wordpress_api_import_enabled=false`, and `automatic_digest_enabled=false` by default.
- No mass-send pipeline is enabled in Phase 9.
- Digest recipes create drafts for editorial review; they do not auto-schedule or auto-send.
- CSV imports never send email and never reactivate suppressed subscribers automatically.

## Core Pieces

- `newsletter_settings` resolves config by bridge installation, push group, site, then global fallback.
- `audience_topics` is the canonical topic catalog shared by newsletter and push.
- Existing `push_topics` and `push_subscription_topics` remain intact and can be mapped through `audience_topic_mappings`.
- Newsletter subscriber email is encrypted at rest and deduplicated by normalized SHA-256 hash.
- Suppressions are checked before controlled or future production sends.

## Imports

CSV import supports upload to private local storage, column mapping, dry-run, validation report, duplicate detection, existing-subscriber detection, suppression detection, optional topic slug mapping, and explicit commit. Commit is blocked unless the effective newsletter settings allow imports, a dry-run has completed, and the batch has not already been committed. Commit only creates or updates subscribers, topic preferences, and list pivots. It never sends email.

## Editorial Sources

RSS/Atom and WordPress REST sources support manual bounded preview fetches that do not persist content by default. Persistence is a separate confirmed admin action and is blocked unless the effective newsletter settings allow that source type.

Digest generation performs a bounded just-in-time refresh of the active sources explicitly attached to the recipe before selecting articles for the draft. It creates the campaign draft and keeps it in editorial review. Auto-send and auto-schedule remain false in Phase 9B.

This is not aggressive crawling. Phase 9 does not run continuous background polling, does not follow arbitrary links, does not crawl whole sites, and limits fetches to configured feed/API endpoints, enabled recipe sources, the first WordPress REST page, and capped item counts.

## AI Drafting

AI providers are global Core configuration, not newsletter-only. Providers are disabled by default, API keys are encrypted, and tests are manual admin actions. If a provider is disabled or unconfigured, Core returns a clearly marked mock result without an external call. Newsletter AI draft actions are gated by effective `ai_generation_enabled` settings and currently create review-required placeholders.

## Tracking

Open and click tracking use hashed tokens. Raw tokens are never stored. Click redirects use the URL stored in token metadata instead of trusting arbitrary query-string targets. Open rate means image-pixel opens and is not guaranteed reading; privacy proxies and image blocking can distort it. Clicks are generally more reliable engagement signals.

## Operational Dashboard

The Newsletter dashboard shows subscriber states, imports, campaign draft counts, delivery/open/click/bounce/complaint rates, recent SNS events, webhook health, content source health, AI job health, and a top-clicked-links skeleton. “Aperture/open rate” is a pixel metric, not proof that the email was read.

## SES/SNS

Controlled SES test email is manual only and requires the global `CORE_NEWSLETTER_SEND_ENABLED` kill switch plus enabled sender identity flags. SNS webhook payloads are stored with hashes, verified before processing, and subscription confirmations are logged without automatic confirmation by default.
