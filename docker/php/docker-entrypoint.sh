#!/bin/sh
set -e

setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

if [ "$APP_ENV" != 'prod' ]; then
    composer install --prefer-dist --no-progress --no-suggest --no-interaction
fi
exec docker-php-entrypoint "$@"
