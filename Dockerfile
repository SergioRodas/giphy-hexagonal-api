# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# GIPHY Hexagonal API - PHP-FPM image
# ---------------------------------------------------------------------------
FROM php:8.3-fpm AS app

# System dependencies + PHP extensions required by Laravel 11 / Passport.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        pkg-config \
        libonig-dev \
        libzip-dev \
        libsqlite3-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite mbstring bcmath opcache zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (from the official composer image).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first for better layer caching.
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy the application source and finish the install.
COPY . .
RUN composer dump-autoload --optimize --no-scripts \
    && cp docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini \
    && chmod +x docker/php/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

# Invoked via "sh" so the script runs even when a bind mount drops the exec bit.
ENTRYPOINT ["sh", "docker/php/entrypoint.sh"]
CMD ["php-fpm"]
