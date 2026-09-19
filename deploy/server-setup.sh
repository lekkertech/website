#!/usr/bin/env bash
# One-time server preparation. Idempotent. Run as root from a checkout of the
# repository (it reads sibling files in deploy/ and ../compose-production.yml):
#   sudo bash deploy/server-setup.sh
#
# 1. Installs the nginx vhost from deploy/nginx into sites-available if it is
#    not there yet. It never overwrites a live vhost, because certbot edits
#    that file. Enabling it, then running certbot for TLS, is the cutover:
#      ln -s /etc/nginx/sites-available/lekkertech.org.za /etc/nginx/sites-enabled/
#      nginx -t && systemctl reload nginx
#      certbot --nginx -d lekkertech.org.za -d www.lekkertech.org.za --redirect
# 2. Pull-based deploy: the `webhook` package running unprivileged on
#    loopback with two hooks (env, which the workflow posts the application
#    environment to at the start of every deploy; deploy, rung by GHCR's
#    Packages event once the image is pushed), one sudo rule letting it run the
#    root-owned deploy script, the deploy directory under /opt, and the nginx
#    rate-limit zone and hook location. The two hook secrets are generated
#    here on first run, kept in /etc/lekkertech/hook-secrets, and printed
#    once: the deploy one goes into the GitHub webhook, the env one into the
#    ENV_HOOK_SECRET repository secret.
set -euo pipefail

DOMAIN=lekkertech.org.za
HERE="$(cd "$(dirname "$0")" && pwd)"
VHOST_SRC="$HERE/nginx/$DOMAIN.conf"
VHOST_DST="/etc/nginx/sites-available/$DOMAIN"

HOOK_USER=webhook
HOOK_CONF=/etc/webhook.conf
SECRETS_FILE=/etc/lekkertech/hook-secrets
DEPLOY_DIR=/opt/lekkertech
DEPLOY_SCRIPT=/usr/local/sbin/lekkertech-deploy
STORE_SCRIPT=/usr/local/bin/lekkertech-env-store
PENDING_DIR=/var/lib/lekkertech

# 1. Vhost, installed on first run only
if [ ! -e "$VHOST_DST" ]; then
    install -m 644 "$VHOST_SRC" "$VHOST_DST"
    VHOST_NOTE="installed, not enabled. Enable it and run certbot at cutover."
else
    VHOST_NOTE="already present, left alone."
fi

# 2a. Webhook receiver: package, unprivileged user, loopback-only drop-in
if ! dpkg -s webhook >/dev/null 2>&1; then
    apt-get update -qq
    DEBIAN_FRONTEND=noninteractive apt-get install -y -qq --no-install-recommends webhook
fi
if ! id "$HOOK_USER" >/dev/null 2>&1; then
    adduser --system --group --no-create-home --shell /usr/sbin/nologin "$HOOK_USER"
fi
install -d -m 755 /etc/systemd/system/webhook.service.d
install -m 644 "$HERE/webhook.service.override.conf" /etc/systemd/system/webhook.service.d/override.conf

# 2b. Scripts, the one sudo rule, and the hand-off directory for the env hook
install -m 755 -o root -g root "$HERE/lekkertech-deploy" "$DEPLOY_SCRIPT"
install -m 755 -o root -g root "$HERE/lekkertech-env-store" "$STORE_SCRIPT"
visudo -cf "$HERE/sudoers" >/dev/null
install -m 440 -o root -g root "$HERE/sudoers" /etc/sudoers.d/lekkertech-deploy
install -d -m 700 -o "$HOOK_USER" -g "$HOOK_USER" "$PENDING_DIR"

# 2c. Hook secrets: kept in one root-only file, generated when missing.
#     An /etc/webhook.conf from before this file existed holds the deploy
#     secret already in use by the GitHub webhook; carry it over.
install -d -m 700 -o root -g root /etc/lekkertech
touch "$SECRETS_FILE"
chmod 600 "$SECRETS_FILE"
NEW_SECRETS=""
if ! grep -q '^HOOK_SECRET=' "$SECRETS_FILE"; then
    carried="$(sed -n 's/.*"secret": *"\([0-9a-f]\{64\}\)".*/\1/p' "$HOOK_CONF" 2>/dev/null | head -n 1)"
    if [ -n "$carried" ]; then
        printf 'HOOK_SECRET=%s\n' "$carried" >> "$SECRETS_FILE"
    else
        printf 'HOOK_SECRET=%s\n' "$(openssl rand -hex 32)" >> "$SECRETS_FILE"
        NEW_SECRETS="$NEW_SECRETS HOOK_SECRET"
    fi
fi
if ! grep -q '^ENV_SECRET=' "$SECRETS_FILE"; then
    printf 'ENV_SECRET=%s\n' "$(openssl rand -hex 32)" >> "$SECRETS_FILE"
    NEW_SECRETS="$NEW_SECRETS ENV_SECRET"
fi
# shellcheck disable=SC1090
. "$SECRETS_FILE"

umask 077
sed -e "s/__HOOK_SECRET__/$HOOK_SECRET/" -e "s/__ENV_SECRET__/$ENV_SECRET/" \
    "$HERE/webhook.conf.template" > "$HOOK_CONF.tmp"
install -m 600 -o "$HOOK_USER" -g "$HOOK_USER" "$HOOK_CONF.tmp" "$HOOK_CONF"
rm -f "$HOOK_CONF.tmp"
umask 022

systemctl daemon-reload
systemctl enable --quiet --now webhook
systemctl restart webhook

# 2d. Deploy directory: compose file from the repo, .env delivered by the env hook
install -d -m 750 -o root -g root "$DEPLOY_DIR"
install -m 644 -o root -g root "$HERE/../compose-production.yml" "$DEPLOY_DIR/compose.yml"
if [ ! -e "$DEPLOY_DIR/.env" ]; then
    install -m 600 -o root -g root /dev/null "$DEPLOY_DIR/.env"
    ENV_NOTE="created empty; the first deploy fills it."
else
    ENV_NOTE="present, left alone."
fi

# 2e. nginx: rate-limit zone and hook location
install -m 644 "$HERE/nginx/lekkertech-hook-zone.conf" /etc/nginx/conf.d/lekkertech-hook-zone.conf
install -d -m 755 /etc/nginx/snippets
install -m 644 "$HERE/nginx/lekkertech-hook.conf" /etc/nginx/snippets/lekkertech-hook.conf
if grep -q 'snippets/lekkertech-hook.conf' "$VHOST_DST" 2>/dev/null; then
    HOOK_NOTE="included from the vhost."
else
    HOOK_NOTE="NOT yet included. Add this line inside the $DOMAIN server block that terminates TLS, then reload nginx:
             include snippets/lekkertech-hook.conf;"
fi
nginx -t
systemctl reload nginx

echo
echo "Done."
echo "  vhost:       $VHOST_DST $VHOST_NOTE"
echo "  hooks:       webhook $(systemctl is-active webhook) on 127.0.0.1:9000, location $HOOK_NOTE"
echo "  deploy dir:  $DEPLOY_DIR, .env $ENV_NOTE"
echo "  secrets:     $SECRETS_FILE"
case " $NEW_SECRETS " in
    *" HOOK_SECRET "*)
        echo
        echo "  New deploy hook secret (shown once):"
        echo "    $HOOK_SECRET"
        echo "  GitHub: Settings > Webhooks > Add: https://$DOMAIN/_hooks/deploy, JSON, this secret, event Packages." ;;
esac
case " $NEW_SECRETS " in
    *" ENV_SECRET "*)
        echo
        echo "  New env hook secret (shown once):"
        echo "    $ENV_SECRET"
        echo "  GitHub: gh secret set ENV_HOOK_SECRET   (paste it). Every deploy delivers the environment with it." ;;
esac
