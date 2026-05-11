# Core Architecture

## Architectural Intent

Core is a centralized backend for Star Atlas Media-owned editorial websites.

The system must be modular. Bootstrap only the foundations first. Avoid implementing every business module in one pass.

Core must support:

- multi-site context;
- exact origin handling;
- centralized users;
- site-scoped and network-scoped PPID;
- Web Push legacy migration and new subscriptions;
- PWA/Service Worker configuration;
- SSO foundations;
- comments and newsletter foundations;
- Filament administration;
- SDK integration;
- WordPress bridge plugin.

## Public Surface

Core should expose only controlled public surfaces:

```text
/                         Minimal landing page
/core-admin/*             Filament admin, protected by Cloudflare Zero Trust Access
/api/*                    Public/private API endpoints with CORS/rate limits
/sdk/*                    Versioned SDK assets
/auth/*                   Auth/SSO endpoints
/webhooks/*               SES/SNS and external service webhooks
/img-proxy/*              Controlled image proxy if implemented later
```

Do not expose a broad public backend UI.

## Admin Panel

Filament path:

```text
/core-admin
```

Never leave Filament at:

```text
/admin
```

`/core-admin*` should be compatible with Cloudflare Zero Trust Access.

## Database Guidelines

Database:

```text
MariaDB 11.8
```

Guidelines:

- Use relational tables for primary queryable data.
- JSON columns are allowed for flexible metadata only.
- Do not store core segmentation logic only in JSON.
- Every frontend/site-related entity must have indexed `site_id`.
- Use `site_origin_id` where origin-specific distinction is required.
- Store Web Push secrets encrypted at rest.
- Use SHA-256 hashes for lookup/deduplication where secrets are encrypted.
- Use SoftDeletes on users and comments.
- Use cached counters for comments and moderation statistics.

## Core Tables — Initial Plan

### Sites / Origins

```text
sites
- id
- code
- name
- canonical_origin
- language
- push_group
- status
- metadata JSON nullable
- timestamps

site_origins
- id
- site_id
- origin
- path_prefix nullable
- is_primary
- status
- timestamps

allowed_origins
- id
- site_id nullable
- origin
- purpose
- status
- timestamps

api_clients
- id
- site_id nullable
- name
- public_key/token identifier
- secret_hash or encrypted secret
- allowed_origins JSON nullable
- permissions JSON nullable
- status
- timestamps
```

### Identity

```text
users
- id
- uuid
- email nullable
- name nullable
- email_verified_at nullable
- password nullable
- status
- metadata JSON nullable
- remember_token
- soft deletes
- timestamps

social_identities
- id
- user_id
- provider
- provider_id
- email nullable
- avatar_url nullable
- metadata JSON nullable
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

### SSO / Auth Foundation

```text
auth_authorization_codes
- id
- code_hash
- user_id
- site_id
- origin
- redirect_url
- state_hash
- nonce_hash
- expires_at
- consumed_at nullable
- metadata JSON nullable
- timestamps

auth_sessions
- id
- user_id
- site_id nullable
- origin nullable
- session_hash
- user_agent_hash nullable
- ip_hash nullable
- expires_at
- revoked_at nullable
- timestamps

login_events
- id
- user_id nullable
- site_id nullable
- origin nullable
- event_type
- provider nullable
- success boolean
- ip_hash nullable
- user_agent_hash nullable
- metadata JSON nullable
- created_at
```

### Comments Foundation

```text
comments
- id
- site_id
- user_id nullable
- external_post_url_hash
- source_url nullable
- parent_id nullable
- body
- status
- replies_count default 0
- likes_count default 0
- reports_count default 0
- metadata JSON nullable
- soft deletes
- timestamps

comment_reactions
- id
- comment_id
- user_id nullable
- anonymous_id nullable
- reaction_type
- timestamps

comment_reports
- id
- comment_id
- user_id nullable
- reason
- status
- metadata JSON nullable
- timestamps

comment_moderation_events
- id
- comment_id
- moderator_user_id nullable
- event_type
- old_status nullable
- new_status nullable
- metadata JSON nullable
- timestamps
```

### Web Push Foundation

```text
legacy_push_apps
- id
- legacy_appid
- site_id
- origin
- language
- section
- merge_group
- service_worker_url
- service_worker_scope
- vapid_key_set_id nullable
- legacy_title
- metadata JSON nullable
- timestamps

vapid_key_sets
- id
- site_id nullable
- legacy_push_app_id nullable
- name
- public_key
- private_key_encrypted
- source
- active
- metadata JSON nullable
- timestamps

push_subscribers
- id
- site_id
- user_id nullable
- anonymous_id nullable
- language nullable
- first_seen_at nullable
- last_seen_at nullable
- metadata JSON nullable
- timestamps

push_subscriptions
- id
- site_id
- push_subscriber_id nullable
- source ENUM('legacy_import','core_sdk','core_reconfirmed')
- status ENUM('legacy_import_pending','active','core_reconfirmed','superseded','invalid','unsubscribed')
- superseded_by_subscription_id nullable
- legacy_push_app_id nullable
- legacy_appid nullable
- legacy_device_id nullable
- legacy_userid nullable
- platform_id nullable
- platform_name nullable
- origin
- service_worker_url
- service_worker_scope
- endpoint_hash
- endpoint_encrypted
- p256dh_encrypted
- auth_encrypted
- vapid_key_set_id
- language nullable
- section nullable
- merge_group nullable
- source_url nullable
- source_url_hash nullable
- created_at_legacy nullable
- last_active_at_legacy nullable
- timestamps

push_subscription_contexts
- id
- push_subscription_id
- site_id
- source_url
- source_url_hash
- source_title nullable
- language nullable
- section nullable
- wp_terms_json nullable
- referrer nullable
- utm_json nullable
- user_agent_hash nullable
- created_at

push_reconfirmation_events
- id
- site_id
- push_subscription_id nullable
- legacy_push_app_id nullable
- legacy_appid nullable
- legacy_device_id nullable
- old_status
- new_status
- match_method nullable
- source_url nullable
- source_url_hash nullable
- language nullable
- section nullable
- origin nullable
- service_worker_url nullable
- service_worker_scope nullable
- manifest_id nullable
- sw_version nullable
- user_agent_hash nullable
- created_at

push_topics
- id
- site_id
- type
- slug
- label
- status
- timestamps

push_subscription_topics
- id
- push_subscription_id
- push_topic_id
- timestamps

push_campaigns
- id
- site_id nullable
- name
- status
- payload JSON
- scheduled_at nullable
- sent_at nullable
- metadata JSON nullable
- timestamps

push_campaign_targets
- id
- push_campaign_id
- target_type
- target_value
- metadata JSON nullable
- timestamps

push_delivery_logs
- id
- push_campaign_id nullable
- push_subscription_id
- status
- response_code nullable
- error nullable
- attempted_at
- delivered_at nullable
- metadata JSON nullable
```

### Newsletter Foundation

```text
newsletter_subscribers
newsletter_lists
newsletter_events
ses_webhook_events
```

Implement only skeleton in bootstrap, not real SES/SNS sending.

### Audit / Security

```text
audit_logs
webhook_events
sdk_tokens
```

## CORS

Use exact origin matching.

For credentialed requests:

- never use `Access-Control-Allow-Origin: *`;
- echo only allowed origin;
- add `Vary: Origin`;
- reject unknown origins.

## CDN / Cache Rules

Core behind Cloudflare:

```text
/core-admin*   Cloudflare Access + no-cache
/api/*         no-cache + rate limit
/auth/*        no-cache + rate limit
/webhooks/*    no-cache + signature validation where applicable
/sdk/*.js      long cache if versioned
/img-proxy/*   cache aggressively only after validation
```

WordPress behind Fastly:

```text
/smart_sw.js                  no-cache or max-age=0
/push-sw.js                   no-cache or max-age=0
/automobili/smart_sw.js       no-cache or max-age=0
/en/smart_sw.js               no-cache or max-age=0
/pwa-start/*                  no-cache
/en/pwa-start/*               no-cache
/core-auth/*                  no-cache
```

## Bootstrap Scope

Implement in bootstrap:

- Laravel installation;
- Filament installation and `/core-admin` path;
- landing page `/`;
- database migrations foundation;
- Filament resources for basic tables if feasible;
- documentation;
- SDK skeleton;
- WordPress bridge plugin skeleton planning;
- migration command skeleton.

Do not implement in bootstrap:

- real SES sending;
- real SNS webhook processing;
- complete Google/Apple login;
- massive push sending;
- social automation;
- full comments product;
- Reverb realtime;
- VAPID rotation;
- Safari legacy import.
