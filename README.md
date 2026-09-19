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
