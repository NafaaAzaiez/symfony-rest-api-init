ARG PHP_VERSION=8.0
ARG APP_ENV=dev

FROM php:${PHP_VERSION}-fpm-buster as php_fpm

# persistent / runtime deps
RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN docker-php-ext-configure gd --with-jpeg --with-freetype

RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring

RUN pecl install apcu \
    && docker-php-ext-enable apcu

COPY docker/php/config/php.ini /usr/local/etc/php/

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    ; \
    pecl clear-cache;

COPY docker/php/config/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update
WORKDIR /var/www/api

ENV PATH="${PATH}:/root/.composer/vendor/bin"

# copy only specifically what we need
COPY composer.json composer.lock symfony.lock .env* behat.yml.* ./
COPY bin bin/
COPY config config/
COPY features features/
#COPY fixtures fixtures/
COPY public public/
COPY src src/
COPY templates templates/
COPY tests tests/
COPY translations translations/

RUN set -eux; \
    composer install --prefer-dist --no-progress --no-suggest;

RUN set -eux; \
    mkdir -p var/cache var/log var/data config/jwt; \
    chmod +x bin/console; sync

VOLUME /var/www/api/var

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint"]

CMD ["php-fpm"]
