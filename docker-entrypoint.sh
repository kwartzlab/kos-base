#!/bin/bash

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
php artisan db:seed

php artisan serve --host="0.0.0.0" --no-interaction
