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

`index.php` reads its configuration from environment variables. `.env` is committed with defaults that work for local development, `.env.example` lists every variable, and in production the deploy workflow writes the real values on the host.

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

Pushing to the `prod` branch (or running the workflow by hand) builds the image, pushes it to `ghcr.io/lekkertech/website`, and deploys it to the server. Images are tagged with the commit SHA, a timestamp and `latest`. A weekly workflow deletes old image versions, keeping the last three.

The deploy step connects over SSH as a dedicated user and, in `~/lekkertech`:

1. writes `compose.yml` (a copy of `compose-production.yml`) and `.env` from the secrets below, over stdin so nothing appears in `ps`;
2. runs `docker compose pull` and `docker compose up -d`, which only recreates the container when the image changed.

The container listens on `127.0.0.1:8801` only. nginx on the host terminates TLS for `lekkertech.org.za` and proxies to that port, so certbot renewals are unchanged.

The image is public, like the repository, and contains only `public/`. If it is ever made private, the deploy user needs `docker login ghcr.io` with a token that has `read:packages`.

### GitHub secrets

| Secret | Contents |
|---|---|
| `PROD_SSH_HOST` | Server hostname |
| `PROD_SSH_USER` | Deploy user on the server |
| `PROD_SSH_KEY` | Private key for that user (ed25519, used for nothing else) |
| `RECAPTCHA_SITE_KEY` | reCAPTCHA v3 site key |
| `RECAPTCHA_SECRET_KEY` | reCAPTCHA v3 secret key |
| `SLACK_INVITE_URL` | Slack invite link |
| `PROD_SSH_HOST_KEY` | The server's public SSH host key, from `ssh-keyscan -t ed25519 <host>` |

The host key is pinned with strict checking so a spoofed host cannot receive the secrets. If the server is rebuilt, refresh that secret.

## Server setup

One-time preparation lives in `deploy/`:

- `deploy/server-setup.sh` creates the unprivileged deploy user in the docker group, installs the deploy public key with SSH restrictions, and copies the nginx vhost into `sites-available` without enabling it. Run it as root on the server.
- `deploy/nginx/lekkertech.org.za.conf` is the hand-written vhost. It proxies to `127.0.0.1:8801`, and redirects `www` to the apex. It is HTTP only: after enabling it, run `certbot --nginx -d lekkertech.org.za -d www.lekkertech.org.za --redirect` and certbot adds TLS and the HTTPS redirect, then renews it like any other site.

Cutover is enabling that vhost, running certbot, and removing the old web root and its previous nginx configuration.
