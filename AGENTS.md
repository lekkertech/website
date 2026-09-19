# Notes for agents

Lekker Tech landing page. Plain PHP today, may become Laravel later; keep
changes compatible with that.

## Structure

- `public/` is the web root and the only directory served. `public/index.php` is the whole site.
- Config is read from environment variables (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `SLACK_INVITE_URL`).
- `.env` is committed with local development defaults. `.env.example` lists every variable. Production gets its own `.env` on the host.
- `public/b5923ca771454a4cb31365e168e43f87.txt` is the IndexNow key; leave it in place.

## Working agreements

- Work is staged for review before commits, one commit per phase. See `refactor-plan.md` while the deployment refactor is in progress.
