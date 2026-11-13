#!/bin/bash

# An APP_ENV set in the container's environment takes precedence over .env, as in Laravel.
INITIAL_APP_ENV="${APP_ENV:-}"

# Import environment variables per Laravel config.
source .env

# Wait for the database to become available.
while ! mysqladmin ping -h"${DB_HOST}" ; do
    sleep 1
done

sleep 1

# Perform application first run initialization tasks

# Generate encryption keys
php artisan key:generate

# Configure and initialize with data the database
php artisan migrate

# Seed fake data only in the local environment, and only once. Seeders are not idempotent.
if [ "${INITIAL_APP_ENV:-${APP_ENV:-production}}" != "local" ]; then
    echo "Skipping db:seed outside the local environment."
elif ! seeded=$(MYSQL_PWD="${DB_PASSWORD}" mysql -h"${DB_HOST}" -u"${DB_USERNAME}" "${DB_DATABASE}" -N \
        -e "SELECT COUNT(*) FROM users WHERE email = 'superuser-dev@kwartzlab.ca'"); then
    echo "Could not check whether the database is seeded; skipping db:seed."
elif [ "${seeded}" != "0" ]; then
    echo "Database already seeded; skipping db:seed."
else
    php artisan db:seed
fi

php artisan serve --host="0.0.0.0" --no-interaction
