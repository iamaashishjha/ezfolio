FROM php:8.1-fpm-alpine AS base

RUN apk add --no-cache \
        bash git unzip \
        icu-dev oniguruma-dev libzip-dev \
        freetype-dev libjpeg-turbo-dev libpng-dev \
        postgresql-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql \
        intl mbstring zip opcache gd \
        calendar \
    && apk del .build-deps

WORKDIR /var/www/html

#✅ Composer (2.2 LTS)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts \
 && composer dump-autoload -o

FROM node:18-alpine AS assets
WORKDIR /var/www/html
RUN apk add --no-cache make g++ python3
COPY package.json package-lock.json ./
RUN npm ci
COPY webpack.mix.js ./
COPY resources ./resources
COPY public ./public
COPY artisan ./
COPY .eslintrc.js ./
RUN npm run production

FROM base
WORKDIR /var/www/html

# Copy project sources first, then overwrite built artifacts from helper stages
COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /var/www/html/public ./public

#✅ Create Laravel required dirs BEFORE composer scripts would run
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

#✅ Git safe directory (optional)
RUN git config --global --add safe.directory /var/www/html || true

#✅ Non-root user
RUN addgroup -g 1000 app \
    && adduser -G app -g app -s /bin/sh -D app -u 1000 \
    && chown -R app:app /var/www/html

USER app

EXPOSE 9000
CMD ["php-fpm"]
