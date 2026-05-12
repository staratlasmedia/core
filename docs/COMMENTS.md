# Proprietary Comments Skeleton

Phase 8 prepares the centralized Core comments system that will eventually replace Disqus. It is a skeleton only: no Disqus import, no WordPress native comments import, no production antispam, no realtime, no reply notifications, and no guest-comments production flow.

## Data Model

- `source_url` is the canonical business reference for a thread.
- `source_url_hash` is always `sha256(normalized_source_url)`.
- Existing `comments.external_post_url_hash` is retained only for legacy compatibility and is populated with the same hash for new Phase 8 writes.
- `comment_threads` stores one row per `site_id + source_url_hash`.
- `comments` uses adjacency-list nesting with `parent_id`, `root_id`, and `depth`.
- Status values are strings with PHP/model constants, not database enums.

## Settings

`comment_settings.scope_key` is deterministic:

- `global`
- `site:{site_id}`
- `push_group:{push_group_id}`
- `bridge_installation:{bridge_installation_id}`

Resolution order is bridge installation, push group, site, global, then hardcoded safe fallback. If no explicit setting exists, comments are disabled with login and moderation required.

## API Direction

Public read endpoints live under `/api/v1/comments*` and return approved comments only. Write endpoints live under `/api/bridge/comments*` and require Bridge HMAC; Phase 8 intentionally does not expose direct browser write endpoints.

## WordPress And SDK Contract

The WordPress Bridge receives effective comment settings in Core config and exposes local `/core-comments/*` endpoints for the SDK. The SDK must post through the local Bridge endpoint, not directly to Core with browser-held secrets.
