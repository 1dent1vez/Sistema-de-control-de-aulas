#!/bin/bash
set -e

echo "==> Optimizando Laravel para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force

echo "==> Creando storage:link..."
php artisan storage:link

echo "==> Preparación completada!"
