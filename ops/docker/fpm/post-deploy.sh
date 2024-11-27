#!/usr/bin/env sh
set -e

composer dump-autoload
php artisan optimize:clear
php artisan optimize
