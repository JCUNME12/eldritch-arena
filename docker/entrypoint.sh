#!/bin/sh
set -eu

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link --force >/dev/null
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    attempt=1

    until php artisan migrate --force; do
        if [ "$attempt" -ge 12 ]; then
            echo "Não foi possível executar as migrations após $attempt tentativas." >&2
            exit 1
        fi

        echo "Banco ainda indisponível; nova tentativa em 5 segundos ($attempt/12)." >&2
        attempt=$((attempt + 1))
        sleep 5
    done
fi

exec "$@"
