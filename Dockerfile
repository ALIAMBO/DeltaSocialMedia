# syntax=docker/dockerfile:1
#
# Production image for DeltaSocialMedia.
# One image, three roles (chosen by the compose `command`):
#   app    -> FrankenPHP web server on :8000 (default CMD)
#   reverb -> php artisan reverb:start
#   queue  -> php artisan queue:work
#
# Builds for amd64 and arm64 (Oracle Cloud Ampere A1).

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm

# ---------------------------------------------------------------------------
# Stage 1: PHP base with the extensions Laravel needs
# ---------------------------------------------------------------------------
FROM ${PHP_IMAGE} AS base

RUN install-php-extensions pdo_mysql bcmath intl zip pcntl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ---------------------------------------------------------------------------
# Stage 2: Composer dependencies (cached separately from app code)
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
        --no-scripts --no-autoloader --no-progress

# ---------------------------------------------------------------------------
# Stage 3: Frontend assets (Vite)
# VITE_* values are baked into the JavaScript at build time, so the public
# Reverb host/port must be passed as build args.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

ARG VITE_APP_NAME=DeltaSocialMedia
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https

WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind scans some vendor views (e.g. pagination) for class names.
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 4: Final runtime image
# ---------------------------------------------------------------------------
FROM base AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    # FrankenPHP: plain HTTP on :8000. TLS is handled by the Caddy edge proxy.
    SERVER_NAME=:8000

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/opcache.ini "$PHP_INI_DIR/conf.d/zz-opcache.ini"

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php artisan package:discover --ansi \
    && rm -f public/hot \
    && ln -sfn ../storage/app/public public/storage \
    && chmod +x docker/entrypoint.sh \
    # Run as an unprivileged user.
    && chown -R www-data:www-data storage bootstrap/cache /data/caddy /config/caddy

USER www-data

EXPOSE 8000

# The FrankenPHP base image ships a HEALTHCHECK that only the web role can pass.
# Health checks are defined per service in docker-compose.prod.yml instead.
HEALTHCHECK NONE

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
