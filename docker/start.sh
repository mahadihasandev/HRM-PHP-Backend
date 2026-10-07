#!/bin/sh
set -eu
# Supply a persistent Laravel key generated with php artisan key:generate --show.
php -r '$key=getenv("APP_KEY"); if (!$key || !str_starts_with($key,"base64:") || strlen(base64_decode(substr($key,7),true) ?: "") !== 32) { fwrite(STDERR,"APP_KEY must contain a valid persistent Laravel key.\n"); exit(1); }'
mkdir -p "${PRIVATE_STORAGE_PATH:-/var/data/private}"
chown www-data:www-data "${PRIVATE_STORAGE_PATH:-/var/data/private}"
sed -i "s/^Listen 80$/Listen ${PORT:-8000}/" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT:-8000}/" /etc/apache2/sites-available/000-default.conf
php artisan config:cache
# Migrations run once per deployment; do not start serving an outdated schema.
php artisan migrate --force
php artisan route:cache
exec apache2-foreground
