ARG PHP_VERSION=7.4
ARG APP_ENV=dev

FROM php:${PHP_VERSION}-fpm-alpine as php_fpm

# persistent / runtime deps
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    acl \
    file \
    gettext \
    git \
    freetype-dev \
    bzip2-dev \
    icu-dev \
    libsodium-dev \
    libzip-dev \
    ; \
    docker-php-ext-configure gd --with-freetype=/usr/include/ \
    && docker-php-ext-install bz2 \
    && docker-php-ext-install opcache \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install sodium \
    && docker-php-ext-install bcmath

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
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative;

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
