# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY postcss.config.js tailwind.config.js vite.config.js ./
RUN npm run build


FROM composer:2.10 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources/views ./resources/views
COPY routes ./routes
COPY artisan ./artisan

RUN composer dump-autoload --no-dev --optimize --no-interaction --no-scripts


FROM php:8.4-apache-bookworm AS production

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl opcache pdo_pgsql zip \
    && a2enmod expires headers rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY docker/php-production.ini /usr/local/etc/php/conf.d/eldritch-production.ini
COPY docker/entrypoint.sh /usr/local/bin/eldritch-entrypoint

RUN chmod +x /usr/local/bin/eldritch-entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/up >/dev/null || exit 1

ENTRYPOINT ["eldritch-entrypoint"]
CMD ["apache2-foreground"]
