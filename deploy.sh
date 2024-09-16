#!/usr/bin/env bash

# usage: sudo -u www-data deploy.sh

# Bash Strict Mode: http://redsymbol.net/articles/unofficial-bash-strict-mode/
set -euo pipefail
IFS=$'\n\t'

echo "Deployment started ..."

git fetch origin main

php artisan down || true

git checkout origin/main

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

php artisan cache:clear
php artisan optimize:clear
php artisan optimize

php artisan migrate --force

# Exit maintenance mode
php artisan up

echo "Deployment finished!"
