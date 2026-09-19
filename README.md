# Lekker Tech website

The site at https://lekkertech.org.za: a single `index.php` that shows the landing page and, on a verified reCAPTCHA POST, redirects to the Slack invite.

## Configuration

`index.php` reads `../lekker.config.json`, one directory above the web root:

| Key | Purpose |
|---|---|
| `site_key` | reCAPTCHA v3 site key |
| `secret_key` | reCAPTCHA v3 secret key |
| `slack_invite_url` | Where a verified visitor is redirected |

## Links

| Where | URL |
|---|---|
| Website | https://lekkertech.org.za |
| X | https://x.com/LekkerTechSlack |
| Bluesky | https://bsky.app/profile/lekkertech.bsky.social |
| LinkedIn | https://www.linkedin.com/company/lekker-tech |
| Facebook | https://www.facebook.com/lekkertechslack |
| GitHub | https://github.com/lekkertech |
| Emojis | https://github.com/lekkertech/emojis |

A new social profile goes in this table, in the Organization `sameAs` list in `index.php`, in the footer icons, and in `llms.txt`.

## Search and analytics

| Service | Property |
|---|---|
| Google Analytics 4 | `G-9K7PZHF635` |
| Google Search Console | `sc-domain:lekkertech.org.za` |
| Bing Webmaster Tools | `https://lekkertech.org.za/` |

`robots.txt` allows search and AI crawlers by name, and `llms.txt` summarises the community for AI assistants.
