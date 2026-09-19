# Notes for agents

Lekker Tech landing page. Plain PHP today, may become Laravel later; keep
changes compatible with that.

## Structure

- `public/` is the web root and the only directory served. `public/index.php` is the whole site.
- Config is read from environment variables (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `SLACK_INVITE_URL`). `APP_VERSION` is baked into the production image, not configured.
- `.env` is committed with local development defaults. `.env.example` lists every variable. In production the values are GitHub secrets; every deploy delivers them to `/opt/lekkertech/.env` on the host.
- `public/b5923ca771454a4cb31365e168e43f87.txt` is the IndexNow key; leave it in place.

## Docker

- `Dockerfile` has two stages: `base` (upstream `thecodingmachine/php:8.5-v5-slim-apache` pinned by digest, plus settings) and `production` (base plus `public/`). Dev builds `base` and bind-mounts source; CI builds `production`. Put extensions and php.ini settings in `base`, application copying in `production`. `.dockerignore` is an allowlist; add to it when the app grows.
- Do not bump the upstream digest casually; the base is meant to stay stable until deliberately changed.
- `compose.yml` is local development: `docker compose up --build`, then http://localhost:8800. Source is bind-mounted.
- `compose-production.yml` is what runs on the server, published on `127.0.0.1:8801` behind nginx.

## Deployment

- Push to `prod` triggers `.github/workflows/build-production-docker-image.yml`: post the environment to the server's env hook, build, push to `ghcr.io/lekkertech/website`, then poll the live site until its `X-App-Version` header shows the new SHA. GitHub never connects to the server; its secrets can only set the environment and trigger a pull.
- The server pulls: GHCR's Packages webhook hits `/_hooks/deploy`, nginx proxies it to the `webhook` daemon, which verifies the signature and runs the root-owned deploy script via a single sudo rule. The script applies the delivered environment, then pulls and restarts. See `deploy/` and the README.
- Never put hostnames or credentials in the repo. The two hook secrets exist only in `/etc/lekkertech/hook-secrets` on the server, the GitHub webhook settings, and the `ENV_HOOK_SECRET` repository secret.
- `deploy/` holds the one-time server setup script and everything it installs: deploy script, sudoers rule, webhook config, systemd drop-in, nginx snippets and the hand-written vhost. The setup script is idempotent and never overwrites a live vhost (certbot edits it).
- New env vars: add to `.env`, `.env.example`, the README tables, the workflow's "Send the environment" step, and GitHub secrets. The next deploy delivers them.

## Working agreements

- Work is staged for review before commits, one commit per phase.
