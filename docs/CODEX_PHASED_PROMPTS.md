# Codex Phased Prompts — Core Project

This file contains phased prompts to use with Codex in VS Code.

Important strategy:

- Do not ask Codex to build the entire project in one step.
- Start with `/plan`.
- Execute one phase at a time.
- Review output after each phase.
- Commit after each clean phase.
- Never expose secrets.

## Phase 0 — Planning Only

Use this first. It must not modify files.

```text
/plan

Agisci come Senior Laravel Architect, Enterprise Identity Architect e Lead Full-Stack Developer.

Stiamo costruendo “Core”, backend headless centralizzato per Star Atlas Media, ospitato su:

https://core.staratlasmedia.com

La root progetto è vuota o quasi vuota. Prima di installare qualsiasi cosa devi SOLO pianificare.

Leggi e tieni come contesto permanente i file Markdown presenti nella cartella /docs se esistono se non li trovi mi dai conferma negativa:

- AGENTS.md
- CORE_PROJECT_CONTEXT.md
- CORE_ARCHITECTURE.md
- WEB_PUSH_PWA_MIGRATION.md
- SSO_AND_IDENTITY.md
- WORDPRESS_BRIDGE_PLUGIN.md
- CODEX_PHASED_PROMPTS.md

In questa fase:

- non installare pacchetti;
- non modificare file;
- non eseguire migrazioni;
- non importare database;
- non generare chiavi;
- non stampare segreti.

Devi produrre un piano operativo per il bootstrap del progetto.

STACK TARGET:

- Laravel 13, solo se l’ambiente ha PHP compatibile.
- Filament 5.
- MariaDB 11.8.
- Redis.
- Laravel Horizon per code Redis.
- Laravel Reverb solo per realtime/WebSocket, non nel bootstrap se non necessario.
- Vite + TypeScript + Lit per SDK.
- Cloudflare WAF/proxy su Core.
- /core-admin compatibile con Cloudflare Zero Trust Access.

CONTESTO CHIAVE:

- Core è backend centrale.
- I Service Worker devono essere serviti dagli origin WordPress tramite plugin Star Atlas Core Bridge.
- ClubAlfa IT = www.clubalfa.it root + /automobili/ uniti.
- ClubAlfa EN = /en/ separato.
- Web Push migration usa Option B: 100% pulita subito.
- Vecchie subscription importate con status legacy_import_pending.
- Campagne moderne solo a core_sdk e core_reconfirmed.
- Niente data.target, niente eval, niente LegacyPayloadAdapter.
- PWA IDs nuovi e stabili: /pwa/clubalfa-it e /pwa/clubalfa-en.
- source_url unico campo URL per contesto iscrizione.

CONTROLLI AMBIENTE DA PREVEDERE:

- php -v
- composer -V
- node -v
- npm -v
- mysql/mariadb client
- redis availability
- git status
- contenuto root
- permessi filesystem
- estensioni PHP richieste
- PHP compatibile con Laravel target

OUTPUT RICHIESTO:

1. Piano bootstrap.
2. Sequenza installazione sicura.
3. Controlli ambiente.
4. Struttura directory proposta.
5. File da creare subito.
6. Schema DB iniziale da implementare nella fase successiva.
7. Rischi principali.
8. Cosa NON implementare nel bootstrap.
9. Criteri done-when per Phase 1.

Non eseguire comandi.
Non modificare file.
```

## Phase 1 — Laravel + Filament Bootstrap

Run only after Phase 0 plan is accepted.

```text
Esegui Phase 1: bootstrap iniziale Laravel + Filament.

Prima leggi:

- AGENTS.md
- CORE_PROJECT_CONTEXT.md
- CORE_ARCHITECTURE.md
- WEB_PUSH_PWA_MIGRATION.md
- SSO_AND_IDENTITY.md
- WORDPRESS_BRIDGE_PLUGIN.md

OBIETTIVO:

Installare e configurare lo scheletro iniziale del progetto Core.

REGOLE:

- Esegui direttamente nell’ambiente.
- Fermati se mancano permessi o credenziali indispensabili.
- Non hardcodare segreti.
- Non stampare VAPID private key.
- Non importare ancora il dump legacy.
- Non implementare ancora SES, SSO completo, invio push reale, commenti completi o social automation.

OPERAZIONI:

1. Verifica ambiente:
   - php -v
   - composer -V
   - node -v
   - npm -v
   - mysql/mariadb client se disponibile
   - redis availability se disponibile
   - git status
   - contenuto root

2. Se PHP non è compatibile con Laravel target, fermati e segnala il blocco.

3. Installa Laravel nella root.
   Se la root non è completamente vuota per documenti come AGENTS.md o .git, usa una strategia sicura che non distrugga file esistenti.

4. Installa Filament.

5. Configura Filament su:
   - /core-admin

6. Crea landing page pubblica `/` minimale:
   - pagina bianca;
   - logo SVG/CSS;
   - testo: “CORE - Star Atlas Media System”.

7. Configura `.env.example` con placeholder per:
   - MariaDB;
   - Redis;
   - queue;
   - app URL;
   - Core domain;
   - no credenziali reali.

8. Crea/aggiorna documentazione:
   - README.md
   - API.md
   - SECURITY.md se utile

9. Prepara struttura directory SDK:
   - resources/sdk/
   - TypeScript
   - Lit
   - Vite build dedicata, se ragionevole nella fase.

10. Non creare ancora logica business complessa.

VERIFICHE FINALI:

- php artisan route:list
- php artisan test se disponibile
- npm run build se configurato

DONE WHEN:

- Laravel installato.
- Filament installato e configurato su /core-admin.
- Landing page / presente.
- File config/documentazione iniziali presenti.
- Nessun segreto hardcoded.
- Progetto in stato coerente e versionabile.
```

## Phase 2 — Core Database Schema Foundation

```text
Esegui Phase 2: schema database fondazionale.

Prima leggi AGENTS.md e CORE_ARCHITECTURE.md.

OBIETTIVO:

Creare migrazioni, modelli Eloquent essenziali e relazioni base per i moduli fondazionali.

NON implementare ancora logica di invio massivo, SES reale, login provider, commenti completi o plugin WordPress completo.

TABELLE DA CREARE O PIANIFICARE IN MIGRAZIONI:

Sites / origins:
- sites
- site_origins
- allowed_origins
- api_clients

Identity:
- users con uuid
- social_identities
- publisher_provided_ids
- auth_authorization_codes
- auth_sessions
- login_events

Push:
- legacy_push_apps
- vapid_key_sets
- push_subscribers
- push_subscriptions
- push_subscription_contexts
- push_reconfirmation_events
- push_topics
- push_subscription_topics
- push_campaigns
- push_campaign_targets
- push_delivery_logs

Comments skeleton:
- comments
- comment_reactions
- comment_reports
- comment_moderation_events

Newsletter skeleton:
- newsletter_subscribers
- newsletter_lists
- newsletter_events
- ses_webhook_events

Security/audit:
- audit_logs
- webhook_events
- sdk_tokens se utile

REGOLE DB:

- Ogni entità frontend-related deve avere site_id indicizzato.
- Usare site_origin_id dove necessario.
- Endpoint push, p256dh, auth e VAPID private key devono essere encrypted-at-rest.
- endpoint_hash deve essere sha256 endpoint.
- JSON solo per metadata flessibili.
- Non usare JSON come unico mezzo per segmenti principali.
- SoftDeletes su users e comments.
- Commenti con parent_id nullable e contatori cache.
- PPID in publisher_provided_ids, non users.ppid.

FILAMENT:

Se ragionevole, crea risorse Filament base per:
- Sites
- SiteOrigins
- LegacyPushApps
- VapidKeySets senza mostrare private key
- PushSubscriptions read-only iniziale

VERIFICHE:

- php artisan migrate --pretend se DB non disponibile.
- php artisan migrate se DB disponibile e configurato.
- php artisan test se disponibile.

DONE WHEN:

- Migrazioni create.
- Modelli essenziali creati.
- Relazioni base definite.
- Nessun segreto esposto.
- Filament non mostra private keys.
```

## Phase 3 — Legacy Push Import Tooling

```text
Esegui Phase 3: tooling di import legacy Web Push.

Prima leggi WEB_PUSH_PWA_MIGRATION.md.

OBIETTIVO:

Creare comandi Artisan e servizi per importare le vecchie subscription dal dump locale nel nuovo DB Core.

FONTI LOCALI:

SQL dump:
/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/database/pushservice.sql

Vecchi file applicativi:
/home/staratlasmedia-core/backups/push.staratlasmedia.com_20260510T173134Z_extracted/push.staratlasmedia.com

REGOLE:

- Non stampare VAPID private key.
- Non committare dump SQL.
- Non mischiare tabelle legacy nel DB Core.
- Creare o usare DB temporaneo legacy_pushservice.
- Importare solo platid 5,7,9,10,11.
- Escludere sempre Safari platid 6.
- Usare VAPID app-specifiche salvo shared == 1.
- Import status iniziale: legacy_import_pending.
- Non implementare LegacyPayloadAdapter.
- Non inviare campagne ai legacy_import_pending.

COMANDI DA CREARE:

- php artisan core:legacy-push:inspect
- php artisan core:legacy-push:import --dry-run
- php artisan core:legacy-push:import --appids=1,11,12,10

MAPPING CLUBALFA:

appid 1:
- site_code clubalfa_it
- language it
- section main
- merge_group clubalfa_it

appid 11:
- site_code clubalfa_it
- language it
- section automobili
- merge_group clubalfa_it

appid 12:
- site_code clubalfa_en
- language en
- section en
- merge_group clubalfa_en

OUTPUT DRY RUN:

- totals by appid;
- migrable rows;
- malformed rows;
- VAPID source app-specific/shared, but never private key values;
- platform counts;
- sample endpoint_hash only, not raw endpoint;
- planned inserts.

DONE WHEN:

- Commands exist.
- Dry-run works without secrets in output.
- Import creates encrypted records.
- Legacy import analytics can be populated.
```

## Phase 4 — Push/PWA Dashboard and Worker/Manifest Generation

```text
Esegui Phase 4: dashboard Filament e generatori PWA/Web Push.

OBIETTIVO:

Predisporre in Core la gestione dei Service Worker e manifest per sito/push group.

Install and use minishlink/web-push as the Web Push sending library.

Do not implement the main Core push delivery engine with laravel-notification-channels/webpush.

Create a custom Laravel service layer and Redis/Horizon jobs around minishlink/web-push.

FILAMENT:

Creare sezione/pagine per:

- Sites
- Push Groups
- Service Worker preview/download
- Manifest preview/download
- Push Migration dashboard
- VAPID key sets read-only masked
- Legacy app mapping

PUSH MIGRATION WIDGETS:

- totale legacy importati
- legacy pending
- core_reconfirmed
- superseded
- core_sdk
- invalidi
- tasso riconferma
- grafico riconferme giornaliere
- pending vs riconfermati
- tabella per legacy_appid/site/section

SERVICE WORKER:

Generare worker moderno pulito:

- no eval;
- no data.target;
- no fetch handler;
- push-only;
- notification.url → notification.data.url;
- install skipWaiting;
- activate clients.claim.

MANIFEST:

ClubAlfa IT:
- id /pwa/clubalfa-it
- scope /
- start_url /pwa-start/?app=clubalfa_it

ClubAlfa EN:
- id /pwa/clubalfa-en
- scope /en/
- start_url /en/pwa-start/?app=clubalfa_en

Core dashboard must allow preview and download.

DONE WHEN:

- Admin can preview/download worker/manifest.
- Migration stats widgets exist or are scaffolded.
- No secrets displayed.
```

## Phase 5 — SDK Skeleton

```text
Esegui Phase 5: SDK frontend skeleton.

OBIETTIVO:

Creare SDK TypeScript/Lit con Vite per embeddare funzioni Core sui siti WordPress.

COMPONENTI SKELETON:

- core-login-widget
- core-comments-widget
- core-push-widget

REGOLE:

- Web Components con Shadow DOM.
- Vanilla embeddable JS output.
- Configurazione da window.StarAtlasCore o attributi.
- Nessuna implementazione business completa.
- Push preferences avanzate rimandate.

SDK CONFIG MINIMA:

- siteCode
- origin
- language
- section
- sourceUrl
- apiBaseUrl

PUSH:

- registra service worker corretto;
- usa payload moderno;
- invia subscription a Core;
- invia source_url e contesto;
- gestisce reconfirm legacy quando possibile.

SSO:

- implementa skeleton popup da click;
- fallback redirect;
- iframe silent check placeholder.

DONE WHEN:

- npm build produce file SDK embeddabile.
- Componenti skeleton renderizzano.
- Nessuna logica complessa non richiesta.
```

## Phase 6 — Star Atlas Core Bridge Plugin Skeleton

```text
Esegui Phase 6: skeleton plugin WordPress Star Atlas Core Bridge.

OBIETTIVO:

Creare struttura plugin, non full implementation.

FUNZIONI SKELETON:

- SDK injection;
- config admin minimale;
- API endpoint per configurazione;
- service worker path handling;
- manifest path handling;
- /pwa-start/ route;
- /core-auth/callback route;
- no-cache headers per service worker;
- passaggio source_url e contesto pagina.

PATH DA SUPPORTARE:

- /smart_sw.js
- /automobili/smart_sw.js
- /en/smart_sw.js
- /pwa-start/
- /en/pwa-start/
- /core-auth/callback

NON implementare ancora:

- login completo;
- comments completi;
- taxonomy preferences;
- full admin UI complessa.

DONE WHEN:

- Plugin skeleton installabile.
- Route principali definite.
- Può servire worker/manifest placeholder moderni.
- Documentazione plugin aggiornata.
```

## Phase 7 — SSO Skeleton

```text
Esegui Phase 7: SSO skeleton.

OBIETTIVO:

Implementare fondazione SSO senza provider Google/Apple completi.

CORE:

- /auth/start
- one-time authorization code model/service
- /auth/code/exchange
- /auth/bridge silent check placeholder
- login events

SDK:

- popup opened synchronously from click;
- fallback redirect;
- postMessage handler skeleton;
- no long-lived tokens in parent.

PLUGIN:

- /core-auth/callback receives code;
- server-to-server exchange placeholder;
- local session placeholder.

REGOLE:

- Exact origin validation.
- state/nonce mandatory.
- code hashes, not raw persisted codes.
- short expiry.
- consumed_at tracking.

DONE WHEN:

- Auth routes exist.
- One-time code flow skeleton exists.
- Tests for code creation/expiry/consume if feasible.
```

## Phase 8 — Comments Skeleton

```text
Esegui Phase 8: comment system skeleton.

OBIETTIVO:

Preparare commenti proprietari senza sostituire subito Disqus in produzione.

CORE:

- comments API skeleton;
- moderation status;
- adjacency list parent_id;
- cached counters;
- reports/reactions skeleton;
- Filament moderation resource.

SDK:

- core-comments-widget skeleton;
- renders placeholder/comment list mock;
- uses source_url.

NON implementare ancora:

- full anti-spam;
- full moderation workflow;
- migration from Disqus unless requested;
- realtime updates.

DONE WHEN:

- Comment models/API/resource scaffolded.
- SDK widget placeholder works.
```

## Phase 9 — Newsletter Skeleton

```text
Esegui Phase 9: newsletter skeleton.

OBIETTIVO:

Preparare modulo newsletter senza invio reale massivo.

CORE:

- newsletter_subscribers
- newsletter_lists
- newsletter_events
- ses_webhook_events
- config placeholders for SES/SNS
- webhook route skeleton with signature validation placeholder

NON implementare ancora:

- real SES send;
- massive campaigns;
- production webhook handling without credentials;
- image proxy unless requested.

DONE WHEN:

- Tables/resources skeleton ready.
- Webhook skeleton exists.
- No credentials hardcoded.
```
