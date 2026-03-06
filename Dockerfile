FROM php:8.2-cli AS base

LABEL maintainer="Liberu CMS"

ARG WWWUSER=1000
ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC \
    ROOT=/var/www/html \
    COMPOSER_FUND=0

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y --no-install-recommends \
    supervisor \
    curl \
    wget \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

RUN pecl install swoole \
    && docker-php-ext-enable swoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g $WWWGROUP octane \
    && useradd -u $WWWUSER -g octane -d /var/www/html -s /bin/sh octane

COPY .docker/octane/php.ini /usr/local/etc/php/conf.d/99-octane.ini
COPY .docker/octane/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d /etc/supercronic \
    && echo "* * * * * php /var/www/html/artisan schedule:run" > /etc/supercronic/laravel

COPY .docker/octane/supervisord.app.conf /etc/supervisor/conf.d/supervisord.app.conf
COPY .docker/octane/supervisord.horizon.conf /etc/supervisor/conf.d/supervisord.horizon.conf
COPY .docker/octane/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# ---- dependencies stage ----
FROM base AS deps

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ---- final stage ----
FROM base AS final

COPY --from=deps /var/www/html/vendor /var/www/html/vendor

COPY . .

RUN composer dump-autoload --optimize \
    && chown -R octane:octane /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
