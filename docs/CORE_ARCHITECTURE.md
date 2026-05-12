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
- private update server for the WordPress bridge plugin.

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

## WordPress Bridge Core Support

Core supports exactly one generic WordPress plugin:

```text
Star Atlas Core Bridge
```

Core does not generate site-specific plugin forks. Instead, Core/Filament generates one-time setup tokens for specific WordPress installations. The plugin consumes the setup token through:

```text
POST /api/bridge/setup/claim
```

After claim, Core stores the bridge installation and returns installation credentials once. Follow-up plugin calls use bridge installation ID plus the HMAC header skeleton:

```text
X-Core-Bridge-Id
X-Core-Timestamp
X-Core-Nonce
X-Core-Signature
```

Core also acts as the private update server for the plugin, supporting update checks, plugin info metadata, and temporary signed download tokens without WordPress.org hosting.

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

### WordPress Bridge / Plugin Updates

```text
bridge_setup_tokens
- id
- uuid
- token_hash
- site_id nullable
- push_group_id nullable
- site_origin_id nullable
- intended_* fields
- status active/consumed/expired/revoked
- expires_at
- consumed_at nullable
- consumed_by_installation_id nullable
- created_by nullable
- revoked_at nullable
- metadata_json nullable
- timestamps

bridge_installations
- id
- uuid
- site_id
- push_group_id nullable
- site_origin_id nullable
- setup_token_id nullable
- site_code / push_group_code
- language / section
- origin / wp URLs / detected_base_path
- plugin_version / wordpress_version / php_version
- status active/disabled/error/revoked
- bridge_secret_encrypted
- bridge_secret_fingerprint
- last_seen_at nullable
- last_config_sync_at nullable
- metadata_json nullable
- soft deletes
- timestamps

bridge_config_versions
- id
- bridge_installation_id nullable
- site_id
- push_group_id nullable
- version
- config_json
- checksum
- active
- published_at nullable
- timestamps

plugin_packages / plugin_releases / plugin_update_downloads
- define the private update server package, release ZIP metadata, release channels, and temporary download audit trail.
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
- provider_user_id nullable
- email nullable
- name nullable
- avatar_url nullable
- access_token_encrypted nullable
- refresh_token_encrypted nullable
- token_expires_at nullable
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
- bridge_installation_id nullable
- origin
- redirect_url
- redirect_uri nullable
- state_hash
- nonce_hash
- status
- expires_at
- consumed_at nullable
- metadata JSON nullable
- timestamps

auth_sessions
- id
- user_id
- site_id nullable
- origin nullable
- session_uuid nullable
- session_hash
- status
- user_agent_hash nullable
- ip_hash nullable
- last_seen_at nullable
- expires_at
- revoked_at nullable
- metadata_json nullable
- timestamps

login_events
- id
- user_id nullable
- site_id nullable
- bridge_installation_id nullable
- origin nullable
- event_type
- provider nullable
- result nullable
- success boolean
- ip_hash nullable
- user_agent_hash nullable
- metadata JSON nullable
- metadata_json nullable
- created_at
```

### Phase 7 Auth Provider Skeleton

```text
auth_providers
- code unique
- name
- type passkey/oauth/magic_link/password
- status disabled/enabled/hidden
- sort_order
- is_default
- is_public
- config_json
- encrypted_config_json
- metadata_json

auth_provider_site_settings
- auth_provider_id
- site_id nullable
- push_group_id nullable
- bridge_installation_id nullable
- status inherited/enabled/disabled/hidden
- config_json
- encrypted_config_json
```

Seeded providers are `passkey`, `google`, `apple`, `magic_link`, `password`, and `facebook`, all disabled and non-public by default.

### Phase 7 WebAuthn / Magic Link Skeleton

```text
webauthn_credentials
- user_id
- credential_id_hash
- credential_id_encrypted
- public_key
- sign_count
- transports_json
- attestation_type
- aaguid
- name
- last_used_at

webauthn_challenges
- user_id nullable
- challenge_hash
- type registration/authentication
- rp_id
- origin
- expires_at
- consumed_at

magic_link_tokens
- email
- token_hash
- user_id nullable
- site_id nullable
- bridge_installation_id nullable
- status
- expires_at
- consumed_at
- ip_hash
- user_agent_hash
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

### Comments Phase 8 Skeleton

Phase 8 extends the existing comments foundation additively.

```text
comment_threads
- id / uuid
- site_id
- push_group_id nullable
- bridge_installation_id nullable
- source_url
- source_url_hash
- source_title nullable
- language / section nullable
- status string: open, closed, archived, disabled
- cached counters
- wp_terms_json / metadata_json nullable

comment_settings
- scope: global, site, push_group, bridge_installation
- scope_key deterministic: global, site:{id}, push_group:{id}, bridge_installation:{id}
- comments_enabled default false
- require_login default true
- allow_guest default false
- require_moderation default true
- max_depth / max_length / min_length
```

`source_url` is the canonical thread reference. `source_url_hash` is always generated from the normalized URL. Existing `external_post_url_hash` remains only as legacy compatibility.

Comment settings are resolved by `CommentSettingsResolver` in this order: bridge installation, push group, site, global, then safe fallback disabled.

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
