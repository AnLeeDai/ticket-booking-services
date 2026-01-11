#!/usr/bin/env sh
set -eu

: "${PORT:=8080}"

# Render sets PORT; Nginx config needs it rendered.
mkdir -p /etc/nginx/conf.d

envsubst '${PORT}' < /var/www/docker/nginx.conf.template > /etc/nginx/nginx.conf
envsubst '${PORT}' < /var/www/docker/site.conf.template > /etc/nginx/conf.d/default.conf

# Ensure writable dirs
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# App optimizations (safe to run on boot)
if [ "${APP_ENV:-production}" = "production" ]; then
  php artisan package:discover --ansi
  php artisan optimize:clear || true
  php artisan config:cache
  php artisan event:cache || true
  php artisan route:cache || true
  php artisan view:cache
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
