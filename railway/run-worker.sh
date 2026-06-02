#!/bin/bash
set -e

echo "==> Iniciando Laravel Queue Worker..."
exec php artisan queue:work --verbose --tries=3 --timeout=90
