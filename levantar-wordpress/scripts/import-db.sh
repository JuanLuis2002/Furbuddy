#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
test -f database.sql
test -f .env
set -a
. ./.env
set +a
sed 's/SERVMASK_PREFIX_/wp_/g' database.sql \
  | docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"
echo "Base importada. Ajusta las URLs con wp-cli si el dominio cambio."
