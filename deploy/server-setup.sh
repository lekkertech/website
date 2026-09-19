#!/usr/bin/env bash
# One-time server preparation for the Docker deployment. Idempotent. Run as root:
#   sudo bash server-setup.sh
#
# 1. Creates the unprivileged deploy user (docker group, no sudo) that the
#    GitHub Actions workflow connects as, with the deploy public key.
# 2. Installs the nginx vhost from deploy/nginx into sites-available WITHOUT
#    enabling it. Enabling it, then running certbot for TLS, is the cutover:
#      ln -s /etc/nginx/sites-available/lekkertech.org.za /etc/nginx/sites-enabled/
#      nginx -t && systemctl reload nginx
#      certbot --nginx -d lekkertech.org.za -d www.lekkertech.org.za --redirect
set -euo pipefail

DOMAIN=lekkertech.org.za
DEPLOY_USER=lekkertech
DEPLOY_PUBKEY='ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBAQotkQPwBv1ENY9cANce/5YuJ9qhEHNqZGyHB2F29T lekkertech-deploy (GitHub Actions)'
VHOST_SRC="$(dirname "$0")/nginx/$DOMAIN.conf"
VHOST_DST="/etc/nginx/sites-available/$DOMAIN"

# 1. Deploy user
if ! id "$DEPLOY_USER" >/dev/null 2>&1; then
    adduser --disabled-password --gecos 'Lekker Tech deploy' "$DEPLOY_USER"
fi
usermod -aG docker "$DEPLOY_USER"

install -d -m 700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh"
# "restrict" turns off pty, agent/X11/port forwarding; the workflow only runs
# non-interactive commands and pipes files over stdin.
printf 'restrict %s\n' "$DEPLOY_PUBKEY" > "/home/$DEPLOY_USER/.ssh/authorized_keys"
chmod 600 "/home/$DEPLOY_USER/.ssh/authorized_keys"
chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh/authorized_keys"

# 2. Vhost, installed but not enabled
install -m 644 "$VHOST_SRC" "$VHOST_DST"
nginx -t

echo
echo "Done."
echo "  user:  $(id "$DEPLOY_USER")"
echo "  vhost: $VHOST_DST installed, not enabled. Enable it and run certbot at cutover."
