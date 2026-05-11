# Agent Browser Commands

Riferimento rapido per `agent-browser` nel progetto Core.

- Tool: `vercel-labs/agent-browser`
- Repo: https://github.com/vercel-labs/agent-browser
- README letto: https://github.com/vercel-labs/agent-browser/blob/main/README.md
- Versione locale verificata: `agent-browser 0.27.0`
- Path progetto: `htdocs/core.staratlasmedia.com`

## Regola Operativa Core

Usare `agent-browser` per test browser veloci su disponibilita' sito e modifiche grafiche semplici.

Comando rapido preferito:

```bash
cd /home/staratlasmedia-core/htdocs/core.staratlasmedia.com
npm run browser:check -- https://core.staratlasmedia.com
```

Output atteso:

```json
{"ok":true,"status":200,"title":"Star Atlas Core","h1":"Star Atlas Core"}
```

Passare a screenshot, snapshot o diagnostica estesa solo se il controllo rapido fallisce o non basta.

## Setup

```bash
npm install agent-browser
npx agent-browser install
npx agent-browser install --with-deps
npx agent-browser upgrade
npx agent-browser doctor
npx agent-browser doctor --fix
npx agent-browser doctor --offline --quick
```

## Skills

Le skill sono versionate con la CLI e sono il modo migliore per recuperare istruzioni aggiornate.

```bash
npx agent-browser skills
npx agent-browser skills list
npx agent-browser skills get core
npx agent-browser skills get core --full
npx agent-browser skills get <name>
npx agent-browser skills get <name> --full
npx agent-browser skills get --all
npx agent-browser skills path
npx agent-browser skills path <name>
```

## Comandi Core

```bash
npx agent-browser open
npx agent-browser open <url>
npx agent-browser goto <url>
npx agent-browser navigate <url>
npx agent-browser click <selector-or-ref>
npx agent-browser dblclick <selector-or-ref>
npx agent-browser focus <selector-or-ref>
npx agent-browser type <selector-or-ref> <text>
npx agent-browser fill <selector-or-ref> <text>
npx agent-browser press <key>
npx agent-browser key <key>
npx agent-browser keyboard type <text>
npx agent-browser keyboard inserttext <text>
npx agent-browser keydown <key>
npx agent-browser keyup <key>
npx agent-browser hover <selector-or-ref>
npx agent-browser select <selector-or-ref> <value>
npx agent-browser check <selector-or-ref>
npx agent-browser uncheck <selector-or-ref>
npx agent-browser scroll <up|down|left|right> [px]
npx agent-browser scrollintoview <selector-or-ref>
npx agent-browser scrollinto <selector-or-ref>
npx agent-browser drag <source> <target>
npx agent-browser upload <selector-or-ref> <files>
npx agent-browser download <selector-or-ref> <path>
npx agent-browser screenshot [path]
npx agent-browser screenshot --full
npx agent-browser screenshot --annotate
npx agent-browser screenshot --screenshot-dir ./shots
npx agent-browser screenshot --screenshot-format jpeg --screenshot-quality 80
npx agent-browser pdf <path>
npx agent-browser snapshot
npx agent-browser eval <javascript>
npx agent-browser connect <port-or-url>
npx agent-browser close
npx agent-browser close --all
```

## Navigazione

```bash
npx agent-browser back
npx agent-browser forward
npx agent-browser reload
npx agent-browser pushstate <url>
```

## Lettura Dati

```bash
npx agent-browser get text <selector-or-ref>
npx agent-browser get html <selector-or-ref>
npx agent-browser get value <selector-or-ref>
npx agent-browser get attr <selector-or-ref> <attribute>
npx agent-browser get title
npx agent-browser get url
npx agent-browser get cdp-url
npx agent-browser get count <selector>
npx agent-browser get box <selector-or-ref>
npx agent-browser get styles <selector-or-ref>
```

## Stato Elementi

```bash
npx agent-browser is visible <selector-or-ref>
npx agent-browser is enabled <selector-or-ref>
npx agent-browser is checked <selector-or-ref>
```

## Locator Semantici

Azioni supportate: `click`, `fill`, `type`, `hover`, `focus`, `check`, `uncheck`, `text`.

```bash
npx agent-browser find role <role> <action> [value]
npx agent-browser find role button click --name "Submit"
npx agent-browser find text <text> <action>
npx agent-browser find text "Sign In" click
npx agent-browser find label <label> <action> [value]
npx agent-browser find placeholder <text> <action> [value]
npx agent-browser find alt <text> <action>
npx agent-browser find title <text> <action>
npx agent-browser find testid <id> <action> [value]
npx agent-browser find first <selector> <action> [value]
npx agent-browser find last <selector> <action> [value]
npx agent-browser find nth <n> <selector> <action> [value]
```

Opzioni utili:

```bash
--name <name>
--exact
```

## Wait

```bash
npx agent-browser wait <selector>
npx agent-browser wait <milliseconds>
npx agent-browser wait --text "Welcome"
npx agent-browser wait --url "**/dashboard"
npx agent-browser wait --load domcontentloaded
npx agent-browser wait --load load
npx agent-browser wait --load networkidle
npx agent-browser wait --fn "window.ready === true"
npx agent-browser wait "#spinner" --state hidden
```

## Batch

Usare `batch` per ridurre overhead quando servono piu' azioni.

```bash
npx agent-browser batch "open https://example.com" "snapshot -i" "screenshot"
npx agent-browser batch --bail "open https://example.com" "click @e1" "screenshot"
echo '[["open","https://example.com"],["snapshot","-i"]]' | npx agent-browser batch --json
```

## Clipboard E Mouse

```bash
npx agent-browser clipboard read
npx agent-browser clipboard write "Hello"
npx agent-browser clipboard copy
npx agent-browser clipboard paste
npx agent-browser mouse move <x> <y>
npx agent-browser mouse down [button]
npx agent-browser mouse up [button]
npx agent-browser mouse wheel <dy> [dx]
```

## Impostazioni Browser

```bash
npx agent-browser set viewport <width> <height> [scale]
npx agent-browser set device <name>
npx agent-browser set geo <lat> <lng>
npx agent-browser set offline [on|off]
npx agent-browser set headers <json>
npx agent-browser set credentials <username> <password>
npx agent-browser set media [dark|light] [reduced-motion]
```

## Cookies, Storage E Stato

```bash
npx agent-browser cookies
npx agent-browser cookies get
npx agent-browser cookies set <name> <value>
npx agent-browser cookies set --curl <file>
npx agent-browser cookies clear
npx agent-browser storage local
npx agent-browser storage local <key>
npx agent-browser storage local set <key> <value>
npx agent-browser storage local clear
npx agent-browser storage session
npx agent-browser state save <path>
npx agent-browser state load <path>
npx agent-browser state list
npx agent-browser state show <name>
npx agent-browser state rename <old> <new>
npx agent-browser state clear [name]
npx agent-browser state clear --all
npx agent-browser state clean --older-than <days>
```

Nota sicurezza: i file di stato possono contenere token di sessione. Non committarli.

## Network E HAR

```bash
npx agent-browser network route <url>
npx agent-browser network route <url> --abort
npx agent-browser network route <url> --body <json>
npx agent-browser network route "*" --abort --resource-type script
npx agent-browser network unroute [url]
npx agent-browser network requests
npx agent-browser network requests --clear
npx agent-browser network requests --filter api
npx agent-browser network requests --type xhr,fetch
npx agent-browser network requests --method POST
npx agent-browser network requests --status 2xx
npx agent-browser network request <requestId>
npx agent-browser network har start
npx agent-browser network har stop [output.har]
```

## Tab, Finestre, Frame E Dialog

```bash
npx agent-browser tab
npx agent-browser tab list
npx agent-browser tab new [url]
npx agent-browser tab new --label docs [url]
npx agent-browser tab <tabId-or-label>
npx agent-browser tab close [tabId-or-label]
npx agent-browser window new
npx agent-browser frame <selector-or-ref>
npx agent-browser frame main
npx agent-browser dialog accept [text]
npx agent-browser dialog dismiss
npx agent-browser dialog status
```

## Diff E Debug

```bash
npx agent-browser diff snapshot
npx agent-browser diff snapshot --baseline before.txt
npx agent-browser diff snapshot --selector "#main" --compact
npx agent-browser diff screenshot --baseline before.png
npx agent-browser diff screenshot --baseline before.png -o diff.png
npx agent-browser diff screenshot --baseline before.png -t 0.2
npx agent-browser diff url https://v1.example.com https://v2.example.com
npx agent-browser diff url https://v1.example.com https://v2.example.com --screenshot
npx agent-browser trace start [path]
npx agent-browser trace stop [path]
npx agent-browser profiler start
npx agent-browser profiler stop [path]
npx agent-browser console
npx agent-browser console --json
npx agent-browser console --clear
npx agent-browser errors
npx agent-browser errors --clear
npx agent-browser highlight <selector-or-ref>
npx agent-browser inspect
```

## Streaming, Dashboard E Chat

```bash
npx agent-browser stream enable [--port <port>]
npx agent-browser stream status
npx agent-browser stream disable
npx agent-browser dashboard
npx agent-browser dashboard start
npx agent-browser dashboard start --port <port>
npx agent-browser dashboard stop
npx agent-browser chat "<instruction>"
npx agent-browser chat
```

Il comando `chat` richiede configurazione AI esterna. Per Core, preferire comandi deterministici (`open`, `snapshot`, `get`, `screenshot`, `batch`) salvo richiesta esplicita.

## React E Performance

```bash
npx agent-browser open --enable react-devtools <url>
npx agent-browser react tree
npx agent-browser react inspect <id>
npx agent-browser react renders start
npx agent-browser react renders stop [--json]
npx agent-browser react suspense [--only-dynamic] [--json]
npx agent-browser vitals [url] [--json]
```

## Auth

```bash
npx agent-browser auth save <name> [options]
npx agent-browser auth login <name>
npx agent-browser auth list
npx agent-browser auth show <name>
npx agent-browser auth delete <name>
```

Opzioni di sessione/autenticazione:

```bash
--profile <name-or-path>
--session-name <name>
--state <path>
--auto-connect
--headers <json>
```

Nota sicurezza: profili, state file e auth vault possono dare accesso a sessioni reali. Non salvarli in repository.

## Conferme E Sessioni

```bash
npx agent-browser confirm <id>
npx agent-browser deny <id>
npx agent-browser session
npx agent-browser session list
```

## Opzioni Globali Utili

```bash
--session <name>
--session-name <name>
--profile <name-or-path>
--state <path>
--headers <json>
--executable-path <path>
--extension <path>
--init-script <path>
--enable react-devtools
--args <args>
--user-agent <ua>
--proxy <url>
--ignore-https-errors
--allow-file-access
--json
--annotate
--screenshot-dir <dir>
--screenshot-quality <0-100>
--screenshot-format <png|jpeg>
--headed
--cdp <port-or-url>
--auto-connect
--color-scheme <dark|light|no-preference>
--download-path <dir>
--content-boundaries
--max-output <chars>
--allowed-domains <domains>
--action-policy <path>
--confirm-actions <categories>
--confirm-interactive
--engine <chrome|lightpanda>
--no-auto-dialog
--model <model>
--config <path>
--debug
```

## Variabili Ambiente Utili

```bash
AGENT_BROWSER_PROFILE
AGENT_BROWSER_SESSION
AGENT_BROWSER_SESSION_NAME
AGENT_BROWSER_STATE
AGENT_BROWSER_ENCRYPTION_KEY
AGENT_BROWSER_STATE_EXPIRE_DAYS
AGENT_BROWSER_ALLOWED_DOMAINS
AGENT_BROWSER_CONTENT_BOUNDARIES
AGENT_BROWSER_MAX_OUTPUT
AGENT_BROWSER_ACTION_POLICY
AGENT_BROWSER_CONFIRM_ACTIONS
AGENT_BROWSER_CONFIRM_INTERACTIVE
AGENT_BROWSER_SCREENSHOT_DIR
AGENT_BROWSER_SCREENSHOT_FORMAT
AGENT_BROWSER_SCREENSHOT_QUALITY
AGENT_BROWSER_HEADED
AGENT_BROWSER_CONFIG
```

## Config File

`agent-browser` puo' leggere un file `agent-browser.json`:

1. `~/.agent-browser/config.json`
2. `./agent-browser.json`
3. variabili `AGENT_BROWSER_*`
4. flag CLI

Se contiene path, proxy o dati locali, aggiungerlo a `.gitignore`.

## Esempi Core

Check veloce:

```bash
npm run browser:check -- https://core.staratlasmedia.com
```

Snapshot compatta:

```bash
npx agent-browser batch --bail "open https://core.staratlasmedia.com" "snapshot -c -d 3" "close"
```

Screenshot:

```bash
npx agent-browser batch --bail "open https://core.staratlasmedia.com" "screenshot /tmp/core-home.png" "close"
```

Controllo title e `h1`:

```bash
npx agent-browser open https://core.staratlasmedia.com
npx agent-browser get title
npx agent-browser get text h1
npx agent-browser close
```
