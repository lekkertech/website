# Lekker Tech website

The site at https://lekkertech.org.za: a single `index.php` that shows the landing page and, on a verified reCAPTCHA POST, redirects to the Slack invite.

## Configuration

`index.php` reads `../lekker.config.json`, one directory above the web root:

| Key | Purpose |
|---|---|
| `site_key` | reCAPTCHA v3 site key |
| `secret_key` | reCAPTCHA v3 secret key |
| `slack_invite_url` | Where a verified visitor is redirected |

## Socials

See [SOCIALS.md](SOCIALS.md).

## Search and analytics

| Service | Property |
|---|---|
| Google Analytics 4 | `G-9K7PZHF635` |
| Google Search Console | `sc-domain:lekkertech.org.za` |
| Bing Webmaster Tools | `https://lekkertech.org.za/` |

`robots.txt` allows search and AI crawlers by name, and `llms.txt` summarises the community for AI assistants.
