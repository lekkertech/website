# Notes for agents

Lekker Tech landing page. Plain PHP today, may become Laravel later; keep
changes compatible with that.

## Structure

- `public/` is the web root and the only directory served. `public/index.php` is the whole site.
- Config is read from environment variables (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `SLACK_INVITE_URL`).
- `.env` is committed with local development defaults. `.env.example` lists every variable. Production gets its own `.env` on the host.
- `public/b5923ca771454a4cb31365e168e43f87.txt` is the IndexNow key; leave it in place.

## Docker

- `Dockerfile` has two stages: `base` (upstream `thecodingmachine/php:8.5-v5-slim-apache` pinned by digest, plus settings) and `production` (base plus `public/`). Dev builds `base` and bind-mounts source; CI builds `production`. Put extensions and php.ini settings in `base`, application copying in `production`. `.dockerignore` is an allowlist; add to it when the app grows.
- Do not bump the upstream digest casually; the base is meant to stay stable until deliberately changed.
- `compose.yml` is local development: `docker compose up --build`, then http://localhost:8800. Source is bind-mounted.
- `compose-production.yml` is what runs on the server, published on `127.0.0.1:8801` behind nginx.

## Deployment

- Push to `prod` triggers `.github/workflows/build-production-docker-image.yml`: build, push to `ghcr.io/lekkertech/website`, then SSH to the host and `docker compose pull && up -d` in `~/lekkertech`.
- Secrets and anything identifying the server live in GitHub secrets (listed in README). Never put hostnames or credentials in the repo. The host's public SSH key is pinned in the workflow.
- New env vars: add to `.env`, `.env.example`, the README table, the workflow's write step, and GitHub secrets.

## Working agreements

- Work is staged for review before commits, one commit per phase. See `refactor-plan.md` while the deployment refactor is in progress.
