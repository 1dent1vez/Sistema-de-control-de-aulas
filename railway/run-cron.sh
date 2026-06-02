#!/bin/bash
set -e

echo "==> Iniciando Laravel Scheduler Loop..."
while [ true ]
do
  echo "==> Ejecutando scheduler..."
  php artisan schedule:run --no-interaction
  sleep 60
done
