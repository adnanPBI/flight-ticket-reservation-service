# Optional Docker image for cloud/VPS deployment.
# cPanel VPS remains the preferred path for this project.
FROM php:8.4-fpm-bookworm AS base

ARG NODE_VERSION=22

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        curl \
        git \
        unzip \
        zip \
        ca-certificates \
        supervisor \
        libicu-dev \
        libzip-dev \
        libonig-dev \
        default-mysql-client \
    && docker-php-ext-install intl pdo_mysql zip opcache bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/ota.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/scripts/entrypoint.sh /usr/local/bin/ota-entrypoint
RUN chmod +x /usr/local/bin/ota-entrypoint

COPY . .

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts \
    && npm ci || npm install \
    && npm run build \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["ota-entrypoint"]
CMD ["php-fpm"]
