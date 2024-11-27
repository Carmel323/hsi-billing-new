#!/bin/sh
set -e

if [ -n "$BASIC_ENV_FILE" ]; then
    cp "$BASIC_ENV_FILE" .env
    # export a few vars so they are available for newrelic.ini
    export $(cat .env | grep 'NEWRELIC_ENABLED' | sed -e 's/"//g')
    export $(cat .env | grep 'APP_ENV' | sed -e 's/"//g')
fi

if [ -n "$NEWRELIC_ENABLED" ] && [ "1" -eq "$NEWRELIC_ENABLED" ]; then
    # export NEWRELIC_KEY for gomplate (see below)
    export $(/usr/local/bin/chamber export app/$APP_NAME/$ENVIRONMENT_NAME/run --format=dotenv | grep 'NEWRELIC_KEY' | sed -e 's/"//g')
    /usr/bin/gomplate -f /newrelic.ini.tpl -o /usr/local/etc/php/conf.d/newrelic.ini
fi

if [ -n "$CHAMBER_ENV" ]; then
    if [ -n "$IS_PRE_DEPLOY" ]; then
        if [ -n "$PREDEPLOY_BASIC_ENV_FILE" ]; then
            cat "$PREDEPLOY_BASIC_ENV_FILE" >> ./.env
        fi
        echo "importing secrets from app/$APP_NAME/$ENVIRONMENT_NAME/run, app/$APP_NAME/$ENVIRONMENT_NAME/pre-deploy"
        chamber exec app/$APP_NAME/$ENVIRONMENT_NAME/run app/$APP_NAME/$ENVIRONMENT_NAME/pre-deploy -- docker-php-entrypoint "$@"
    else 
        echo "importing secrets from app/$APP_NAME/$ENVIRONMENT_NAME/run"
        chamber exec app/$APP_NAME/$ENVIRONMENT_NAME/run -- docker-php-entrypoint "$@"
    fi
else
    exec docker-php-entrypoint "$@"
fi

