# ------------------------------
# Build PHP extensions
# ------------------------------
FROM php:8.3-fpm-alpine AS php-ext

RUN apk add --no-cache --virtual .build-deps \
      $PHPIZE_DEPS \
      icu-dev oniguruma-dev libzip-dev \
      freetype-dev libjpeg-turbo-dev libpng-dev \
      postgresql-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install \
      pdo pdo_mysql \
      intl mbstring zip opcache gd \
      sockets \
      calendar \
  && apk del .build-deps


# ------------------------------
# Base PHP runtime image
# ------------------------------
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
      icu-libs oniguruma libzip \
      freetype libjpeg-turbo libpng \
      libpq

WORKDIR /var/www/html

# Copy compiled extensions + ini config from build stage
COPY --from=php-ext /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=php-ext /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d


# ------------------------------
# Vendor stage (NO scripts)
# ------------------------------
FROM base AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache git unzip

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./

RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --optimize-autoloader \
      --no-scripts \
  && composer clear-cache


# ------------------------------
# Node assets stage (Debian, not Alpine)
# ------------------------------
FROM node:18-bullseye-slim AS assets

WORKDIR /var/www/html

# Build tools for native deps when needed
RUN apt-get update && apt-get install -y --no-install-recommends \
      python3 make g++ \
  && rm -rf /var/lib/apt/lists/*

# Prevent webpack/terser OOM
ENV NODE_OPTIONS=--max_old_space_size=4096
ENV CI=true

# Install deps with cache-friendly layering.
# Prefer reproducible installs via npm ci when lockfile exists.
COPY package*.json ./
RUN if [ -f package-lock.json ]; then \
      npm ci --no-audit --no-fund --legacy-peer-deps \
      || npm install --no-audit --no-fund --legacy-peer-deps; \
    else \
      npm install --no-audit --no-fund --legacy-peer-deps; \
    fi

# Copy only what's needed for build
COPY webpack.mix.js ./
COPY resources ./resources
COPY public ./public

# Some builds expect these files
COPY artisan ./
COPY .eslintrc.js ./

RUN npm run production


# ------------------------------
# Final runtime image
# ------------------------------
FROM base AS runtime

WORKDIR /var/www/html

# Non-root user
RUN addgroup -g 1000 app \
  && adduser -G app -g app -s /bin/sh -D app -u 1000

# Copy full source
COPY --chown=app:app . .

# Bring in vendor + built public assets
COPY --from=vendor --chown=app:app /var/www/html/vendor ./vendor
COPY --from=assets --chown=app:app /var/www/html/public ./public

# Laravel required dirs + perms
RUN mkdir -p \
      storage/app/public \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      bootstrap/cache \
  && rm -rf public/storage \
  && ln -s ../storage/app/public public/storage \
  && chmod -R 775 storage bootstrap/cache

USER app

EXPOSE 9000
CMD ["php-fpm"]
