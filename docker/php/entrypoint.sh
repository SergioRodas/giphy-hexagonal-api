#!/bin/sh
set -e

cd /var/www/html

# Ensure an environment file exists (runtime config is supplied by container env).
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate an application key only when one has not been provided.
if [ -z "${APP_KEY}" ] && ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Wait for the database to accept connections.
echo "Waiting for database at ${DB_HOST}:${DB_PORT} ..."
until php -r '$h=getenv("DB_HOST");$p=(int)getenv("DB_PORT");exit(@fsockopen($h,$p)?0:1);' 2>/dev/null; do
    sleep 2
done
echo "Database is ready."

php artisan migrate --force

# Passport signing keys (generated once; skipped if already present).
if [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force
fi

# Seed the demo user and ensure a personal-access client exists (idempotent).
php artisan db:seed --force

# Cache configuration and routes for performance.
php artisan config:cache
php artisan route:cache

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
