# Lekker Tech website

The site at https://lekkertech.org.za: a single `public/index.php` that shows the landing page and, on a verified reCAPTCHA POST, redirects to the Slack invite.

## Layout

Only `public/` is served. Everything else in the repository is configuration, tooling or documentation. This is the Laravel layout, so a future Laravel conversion keeps the same web root.

| Path | Purpose |
|---|---|
| `public/index.php` | The whole site |
| `public/*.png`, `favicon.ico`, `site.webmanifest` | Icons and social preview images |
| `public/robots.txt`, `sitemap.xml`, `llms.txt` | Crawler files |
| `public/b5923ca771454a4cb31365e168e43f87.txt` | IndexNow key (see below) |

## Configuration

`index.php` reads its configuration from environment variables. `.env` is committed with defaults that work for local development, `.env.example` lists every variable. In production the values are GitHub secrets that every deploy delivers to `/opt/lekkertech/.env` on the host (see Deployment).

| Variable | Purpose |
|---|---|
| `RECAPTCHA_SITE_KEY` | reCAPTCHA v3 site key |
| `RECAPTCHA_SECRET_KEY` | reCAPTCHA v3 secret key |
| `SLACK_INVITE_URL` | Where a verified visitor is redirected |
| `APP_VERSION` | Git SHA baked into the production image at build time, returned as an `X-App-Version` header on every response. Not set by hand; local builds report `dev`. |

The site runs behind Cloudflare, so the visitor's address passed to the reCAPTCHA check is taken from the `CF-Connecting-IP` header when present, falling back to the socket peer.

## Socials

See [SOCIALS.md](SOCIALS.md).

## Search and analytics

| Service | Property |
|---|---|
| Google Analytics 4 | `G-9K7PZHF635` |
| Google Search Console | `sc-domain:lekkertech.org.za` |
| Bing Webmaster Tools | `https://lekkertech.org.za/` |
| IndexNow | Key `b5923ca771454a4cb31365e168e43f87`, served from the web root |

`robots.txt` allows search and AI crawlers by name, and `llms.txt` summarises the community for AI assistants. The IndexNow key file proves ownership of the site so Bing accepts instant URL submissions.

## Local development

Needs Docker. The committed `.env` has defaults that work out of the box.

```sh
docker compose up --build
open http://localhost:8800
```

The source directory is bind-mounted into the container, so edits to anything under `public/` show up on reload without a rebuild. Development builds only the `base` stage of the `Dockerfile` (the upstream image plus settings, no application code), so dev builds stay fast however large the app gets. CI builds the `production` stage, which adds the application on top of the same base.

The upstream image, `thecodingmachine/php:8.5-v5-slim-apache`, is pinned by digest in the `Dockerfile` so the base only changes when someone bumps it on purpose. The comment at the top of the file says how.

With the default `.env`, a POST to `/` with any token redirects to the placeholder invite URL, because Google's test secret always verifies. The reCAPTCHA widget in the browser needs real keys with `localhost` in their allowed domains.

## Deployment

Pushing to the `prod` branch (or running the workflow by hand) deploys. GitHub never connects to the server; it posts to two hooks the server exposes, and the server does the rest.

1. The workflow assembles the application environment from the secrets below and POSTs it to `https://lekkertech.org.za/_hooks/env`, signed with the env hook secret the same way GitHub signs its own webhooks. The server stores it as pending.
2. The workflow builds the image and pushes it to `ghcr.io/lekkertech/website`, tagged with the commit SHA, a timestamp and `latest`. A weekly workflow deletes old image versions, keeping the last three.
3. GHCR publishes the image, which fires the repository's **Packages** webhook at `https://lekkertech.org.za/_hooks/deploy`.
4. nginx rate-limits the `/_hooks/` prefix and proxies it to the `webhook` daemon, which runs unprivileged on loopback. The daemon checks the HMAC signature on every request and rejects anything else with a 403.
5. On a valid deploy delivery it runs `sudo /usr/local/sbin/lekkertech-deploy`, the one command its sudo rule allows. That root-owned script takes a lock, moves the pending environment into `/opt/lekkertech/.env`, then runs `docker compose pull` and `docker compose up -d`, which recreates the container only when the image or the environment changed. Its output is in `journalctl -t lekkertech-deploy`.
6. The workflow's last step polls the live site until the `X-App-Version` header reports the SHA it just built, and fails after five minutes if it never does.

Both hooks together can do one thing: set the environment and trigger a pull of `latest`. Nothing in GitHub can reach the server beyond that. Changing a secret is `gh secret set` followed by any deploy. The image carries no configuration. The container listens on `127.0.0.1:8801` only; nginx terminates TLS for `lekkertech.org.za` and proxies to that port.

The image is public, like the repository, and contains only `public/`. If it is ever made private, root on the server needs `docker login ghcr.io` with a token that has `read:packages`.

### GitHub configuration

| Secret | Contents |
|---|---|
| `RECAPTCHA_SITE_KEY` | reCAPTCHA v3 site key |
| `RECAPTCHA_SECRET_KEY` | reCAPTCHA v3 secret key |
| `SLACK_INVITE_URL` | Slack invite link |
| `ENV_HOOK_SECRET` | Signs the environment delivery; printed by the server setup script |

One repository webhook: payload URL `https://lekkertech.org.za/_hooks/deploy`, content type JSON, the deploy hook secret printed by the server setup script, subscribed to the Packages event only. Its initial ping shows as a 403 delivery, because that hook accepts nothing but Packages events.

## Server setup

One-time preparation lives in `deploy/`. Copy `deploy/` and `compose-production.yml` to the server and run `sudo bash deploy/server-setup.sh`. It is idempotent.

| File | Installed as | Purpose |
|---|---|---|
| `server-setup.sh` | | Installs everything below, generates the two hook secrets on first run and prints them once |
| `lekkertech-deploy` | `/usr/local/sbin/lekkertech-deploy` | The deploy script, root-owned |
| `lekkertech-env-store` | `/usr/local/bin/lekkertech-env-store` | Stores a delivered environment for the deploy script to apply; runs as `webhook` |
| `sudoers` | `/etc/sudoers.d/lekkertech-deploy` | The `webhook` user may run the deploy script as root, nothing else |
| `webhook.conf.template` | `/etc/webhook.conf` | The two hook definitions, with the secrets filled in from `/etc/lekkertech/hook-secrets` |
| `webhook.service.override.conf` | `/etc/systemd/system/webhook.service.d/override.conf` | Run the package's daemon as `webhook` on `127.0.0.1:9000` |
| `nginx/lekkertech-hook-zone.conf` | `/etc/nginx/conf.d/` | Rate-limit zone; limits nothing until a location references it |
| `nginx/lekkertech-hook.conf` | `/etc/nginx/snippets/` | The `/_hooks/` location |
| `nginx/lekkertech.org.za.conf` | `/etc/nginx/sites-available/` | The vhost, installed on first run only |

The script never overwrites an existing vhost, because certbot edits that file on the live host. On a host where the vhost already exists, add `include snippets/lekkertech-hook.conf;` to the `lekkertech.org.za` server block that terminates TLS and reload nginx; the script's summary says whether that is still needed. On a fresh host, enable the vhost and run certbot for the host as usual to add TLS.

The deploy directory `/opt/lekkertech` holds `compose.yml` (installed from `compose-production.yml`) and `.env`, which the first deploy fills in.
