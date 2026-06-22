#!/usr/bin/env bash
set -e

: "${PORT:=80}"

echo "Configuring Apache to listen on port ${PORT}..."
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "Fixing Apache MPM modules..."
# Remove any MPM modules except prefork
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
# Ensure only prefork is loaded
if [ ! -f /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/
fi

echo "Running Laravel cache commands..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Creating storage symlink..."
php artisan storage:link --force

echo "Running migrations..."
php artisan migrate --force

echo "Starting Laravel Queue Worker..."
php artisan queue:work --tries=3 &

echo "Starting Apache..."
exec apache2-foreground
