# Two stages in one file:
#
#   base        Ubuntu 24.04, PHP 8.5 and Apache from apt, our settings, no
#               application code. Local development builds this target and
#               bind-mounts the source over it, so dev builds never get slower
#               as the app grows.
#   production  base plus the application. CI builds and publishes this.
#
# The upstream image is pinned by digest so the base is reproducible until
# someone bumps it on purpose. To bump: change the tag if needed, then
#   docker buildx imagetools inspect thecodingmachine/php:<tag> --format '{{.Manifest.Digest}}'
# and paste the new digest here.
#
# The "slim" variant ships only the core extensions (curl, mbstring, openssl,
# opcache, zip ...), which is all this site needs. Extra extensions when
# Laravel arrives are one line in the base stage:
#   ENV PHP_EXTENSION_PDO_MYSQL=1
FROM thecodingmachine/php:8.5-v5-slim-apache@sha256:e4f2517120d2bbbaa7e5b73d4ce4a00640f7a4b44b16dfbedf8ecfb19aa00d98 AS base

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    TEMPLATE_PHP_INI=production \
    PHP_INI_EXPOSE_PHP=0

WORKDIR /var/www/html


FROM base AS production

# The git SHA the image was built from, passed by the workflow. The site
# returns it in an X-App-Version header so a deploy can be confirmed from
# outside. Local builds get "dev".
ARG APP_VERSION=dev
ENV APP_VERSION=$APP_VERSION

# Only public/ is served. When this becomes a Laravel app, copy the rest of the
# tree here as well and run composer install (see dylanbr/aibot for the shape).
COPY --chown=docker:docker public public
