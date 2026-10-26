FROM php:8.4.27-apache-bookworm@sha256:e653ff90d7f6e2e6f81eddb07c864f1eae224ccbf5143f82a44f25e2f110e320
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libicu-dev libzip-dev libonig-dev \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /usr/src/php \
    && tar -xJf /usr/src/php.tar.xz -C /usr/src/php --strip-components=1 --wildcards --exclude='*/tests/*' --exclude='*/tests' \
    && touch /usr/src/php/.docker-extracted /usr/src/php/.docker-delete-me \
    && docker-php-ext-install -j1 pdo_pgsql intl zip mbstring bcmath opcache \
    && a2enmod rewrite && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY . .
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN printf 'session.upload_progress.enabled=0\nmemory_limit=96M\nupload_max_filesize=5M\npost_max_size=6M\nexpose_php=Off\nopcache.enable=1\nopcache.memory_consumption=48\n' > /usr/local/etc/php/conf.d/arena.ini \
    && printf '<IfModule mpm_prefork_module>\nStartServers 1\nMinSpareServers 1\nMaxSpareServers 2\nMaxRequestWorkers 4\nMaxConnectionsPerChild 300\n</IfModule>\n' > /etc/apache2/mods-available/mpm_prefork.conf \
    && chown -R www-data:www-data storage bootstrap/cache
CMD ["sh", "start.sh"]
