#!/bin/sh
set -e

if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    composer install --prefer-dist --no-progress --no-suggest -o --no-interaction --ignore-platform-reqs
    chmod -R 777 var
    ./bin/console assets:install
    ./bin/console doctrine:mongodb:schema:update || echo "Warning: failed to update DB indexes"
fi

service nginx start

exec docker-php-entrypoint "$@"
