#!/bin/sh
set -e

echo "=== Rodando Migrations no PostgreSQL ==="
php spark migrate --all

echo "=== Iniciando Apache ==="
exec apache2-foreground