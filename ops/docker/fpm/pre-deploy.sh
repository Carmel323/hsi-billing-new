#!/usr/bin/env sh
set -e

composer dump-autoload
php artisan migrate --force
php artisan db:seed --force
