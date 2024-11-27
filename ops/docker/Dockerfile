

# FPM SECTION
#FROM php:8.1-fpm-alpine3.14 as base
FROM public.ecr.aws/cldevops/laravel:8.2-nr-daemon as base
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=10000
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=192
COPY --from=segment/chamber:2.3.3 /chamber /usr/local/bin/chamber
COPY --from=hairyhenderson/gomplate:v2.5.0-slim /gomplate /usr/bin
COPY ops/docker/fpm/entrypoint.sh /usr/local/bin/
COPY ops/docker/fpm/pre-deploy.sh /usr/local/bin/
COPY ops/docker/fpm/post-deploy.sh /usr/local/bin/
COPY ops/docker/fpm/newrelic.ini.tpl /newrelic.ini.tpl
RUN chmod +x /usr/local/bin/pre-deploy.sh
RUN chmod +x /usr/local/bin/post-deploy.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chmod 755 /var/log

RUN apk add --no-cache build-base libpng-dev \
    #nodejs npm wget libgcc icu-dev
    wget libgcc icu-dev
#RUN docker-php-ext-install -j"$(nproc)" soap gd
#RUN docker-php-ext-configure intl && docker-php-ext-install intl

# PHPREDIS
RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis
RUN docker-php-ext-install redis

# COMPOSER AND NPM
FROM base as build
WORKDIR /app
COPY . ./
#COPY composer.json composer.lock ./
ARG COMPOSER_AUTH_BASE64

RUN COMPOSER_AUTH=$(echo $COMPOSER_AUTH_BASE64 | base64 -d) \
        composer install --no-scripts

# FPM CONFIGURATION SECTION
COPY ops/docker/fpm/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN mv ops /var/ops
RUN echo "expose_php=off" >> /usr/local/etc/php/php.ini
RUN echo "log_errors=on" >> /usr/local/etc/php/php.ini
RUN echo "memory_limit=1024M" >> /usr/local/etc/php/php.ini
RUN echo "upload_max_filesize=50M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size=55M" >> /usr/local/etc/php/php.ini
RUN chown -R www-data:www-data /app
RUN chown www-data:www-data bootstrap/cache
RUN chown www-data:www-data /usr/local/etc/php/conf.d
EXPOSE 9000
USER www-data
ENTRYPOINT ["entrypoint.sh"]
CMD ["start-fpm.sh"]

# NODE SECTION
FROM node:20-alpine3.18 as node
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY public ./public
COPY resources ./resources
RUN npm install
RUN npm run build

# NGINX SECTION
FROM public.ecr.aws/cldevops/nginx:stable-laravel as nginx
COPY --from=node /app/public /app/public
COPY --from=node /app/node_modules /app/node_modules
#COPY --from=build /app/public /app/public
#CMD ["/start-nginx.sh"]




