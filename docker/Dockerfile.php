FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

#✅ System deps
RUN apk add --no-cache \
    bash git unzip \
    icu-dev oniguruma-dev libzip-dev \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    postgresql-dev \
  && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS

#✅ PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_mysql pdo_mysql \
    intl mbstring zip opcache gd \
    calendar \
 && apk del .build-deps

#✅ Composer (2.2 LTS)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Copy code
COPY . .

COPY --from=vendor /var/www/html/vendor ./vendor

COPY --from=assets /var/www/html/public/js ./public/js
COPY --from=assets /var/www/html/public/css ./public/css
COPY --from=assets /var/www/html/public/mix-manifest.json ./public/mix-manifest.json


#✅ Create Laravel required dirs BEFORE composer scripts would run
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

#✅ Git safe directory (optional)
RUN git config --global --add safe.directory /var/www/html || true

# ✅ Install deps WITHOUT running artisan scripts during build
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --optimize-autoloader \
  --no-scripts

# ✅ Ensure helper file autoload is generated (modelCollection available)
RUN composer dump-autoload -o

#✅ Non-root user
RUN addgroup -g 1000 app \
 && adduser -G app -g app -s /bin/sh -D app -u 1000 \
 && chown -R app:app /var/www/html

USER app

EXPOSE 9000
CMD ["php-fpm"]
