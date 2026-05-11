# Infrastructure

## Contesto macchina
- Host: `SRV2`
- OS: Linux Debian-based, kernel `6.12.48+deb13-cloud-amd64`
- Utente corrente: `root`
- Workspace: `/home/staratlasmedia-core`
- Proprietario directory di lavoro: gruppo `staratlasmedia-core`

## Vhost attivo
- Dominio: `core.staratlasmedia.com`
- File vhost Nginx: `/etc/nginx/sites-enabled/core.staratlasmedia.com.conf`
- Document root: `/home/staratlasmedia-core/htdocs/core.staratlasmedia.com/public`
- Log Nginx: `/home/staratlasmedia-core/logs/nginx/access.log` e `/home/staratlasmedia-core/logs/nginx/error.log`
- PHP-FPM upstream: `127.0.0.1:19003`

## CloudPanel
- CloudPanel gestisce i vhost tramite il suo editor dedicato e valida la sintassi prima di applicare le modifiche.
- Per i siti PHP, la root segue il pattern `/home/$siteUser/htdocs/$domainName/$rootDirectory`.
- Per questo sito la root effettiva punta a `public`.

## Comandi base
- `clpctl`
  - Mostra i comandi disponibili.
- `clpctl db:backup --databases=all`
  - Backup di tutti i database.
- `clpctl db:backup --databases=all --ignoreDatabases=db1,db2`
  - Backup di tutti i database esclusi quelli elencati.
- `clpctl db:backup --databases=database-name`
  - Backup di un singolo database.
- `clpctl db:import --file=dump.sql.gz --database=database-name`
  - Import o restore di un database.
- `clpctl db:show:credentials`
  - Mostra le credenziali master per il database.
- `clpctl db:show:master-credentials`
  - Mostra host, user, password e porta del database master.
- `clpctl user:disable:mfa --userName=john.doe`
  - Disabilita MFA/2FA per un utente CloudPanel.
- `clpctl user:reset:password --userName=john.doe --password='!newPassword!'`
  - Reset password di un utente CloudPanel.
- `clpctl system:permissions:reset --directories=770 --files=660 --path=.`
  - Ripristina permessi di file e directory.
- `clpctl cloudpanel:enable:basic-auth --userName=john.doe --password='password123'`
  - Attiva Basic Auth davanti a CloudPanel.
- `clpctl cloudpanel:disable:basic-auth`
  - Disattiva Basic Auth davanti a CloudPanel.
- `clpctl cloudflare:update:ips`
  - Aggiorna gli IP Cloudflare usati dal firewall/vhost.
- `clp-update`
  - Aggiorna CloudPanel e le dipendenze.

## Promemoria
- Prima di cambiare il vhost, verificare sempre il file in `/etc/nginx/sites-enabled/`.
- Per backup e restore database, preferire i comandi `clpctl` quando disponibili.
- Evitare di modificare file di sistema senza un motivo chiaro e una verifica del risultato.
