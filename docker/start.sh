#!/usr/bin/env bash
set -e

: "${PORT:=80}"

echo "Configuring Apache to listen on port ${PORT}..."
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "Running Laravel cache commands..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

echo "Starting Apache..."
exec apache2-foreground
