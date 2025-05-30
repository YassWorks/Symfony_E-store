#!/bin/bash
set -e

echo "Waiting for database…"
until pg_isready -h database -U symfony; do sleep 2; done

echo "Ensuring database exists…"
php bin/console doctrine:database:create --if-not-exists --quiet

psql -h database -U symfony -d symfony -c 'ALTER TABLE "user" RENAME TO users;' || true

echo "Updating schema…"
php bin/console doctrine:schema:update --force --quiet

echo "Launching php-fpm"
exec php-fpm
