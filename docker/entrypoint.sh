#!/bin/sh
# Container entrypoint: prepare Laravel, then run the role's command.
set -e

# Make sure storage subfolders exist (the storage volume starts empty).
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
         storage/framework/sessions storage/framework/views storage/logs

# Cache config/routes/views/events using the runtime environment (.env values).
php artisan optimize

# Only the web container runs migrations (RUN_MIGRATIONS=true in compose).
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
