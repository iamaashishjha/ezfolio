FROM laravelsail/php83-composer:latest AS vendor

WORKDIR /var/www/html

COPY . .
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

FROM node:18-alpine AS assets

WORKDIR /var/www/html
ENV NODE_ENV=production

COPY package*.json ./
COPY webpack.mix.js ./
COPY resources resources
COPY public public

RUN NODE_ENV=development npm ci
RUN npm run prod

FROM php:8.3-fpm-alpine AS app

WORKDIR /var/www/html

RUN set -eux; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev oniguruma-dev icu-dev libpng-dev libjpeg-turbo-dev freetype-dev; \
    apk add --no-cache libzip oniguruma icu-libs libpng libjpeg-turbo freetype zip unzip git curl bash; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-configure intl; \
    docker-php-ext-install pdo_mysql mbstring bcmath zip gd intl; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/*

COPY . .

COPY --from=vendor /var/www/html/vendor ./vendor

COPY --from=assets /var/www/html/public/js ./public/js
COPY --from=assets /var/www/html/public/css ./public/css
COPY --from=assets /var/www/html/public/mix-manifest.json ./public/mix-manifest.json

RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stack
ENV CACHE_DRIVER=file
ENV SESSION_DRIVER=file
ENV QUEUE_CONNECTION=database

EXPOSE 9000

CMD ["php-fpm"]
