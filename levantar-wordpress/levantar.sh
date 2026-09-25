#!/bin/sh
# Emergencia en la VPS. El respaldo .wpress no esta en git: vive en BackNo.
set -eu

DIR=$(CDPATH= cd -- "$(dirname "$0")" && pwd)
BACKNO=/root/BackNo

cd "$DIR"

restaurar() {
  archivo=$(find "$BACKNO" -maxdepth 1 -type f -name '*.wpress' | head -n 1)
  if [ -z "$archivo" ]; then
    echo "No encontre un .wpress en $BACKNO"
    exit 1
  fi

  echo "Restaurando desde $archivo"
  rm -rf "$DIR/.restore"
  python3 "$DIR/scripts/extract_wpress.py" "$archivo" "$DIR/.restore" \
    --skip backups-dup-lite \
    --skip litespeed

  mkdir -p "$DIR/wp-content"
  for carpeta in plugins themes uploads languages mu-plugins; do
    if [ -d "$DIR/.restore/$carpeta" ]; then
      rm -rf "$DIR/wp-content/$carpeta"
      mv "$DIR/.restore/$carpeta" "$DIR/wp-content/$carpeta"
    fi
  done

  if [ -f "$DIR/.restore/database.sql" ]; then
    mv "$DIR/.restore/database.sql" "$DIR/database.sql"
  fi

  chown -R 33:33 "$DIR/wp-content"
  docker compose up -d

  set -a
  # shellcheck disable=SC1091
  . "$DIR/.env"
  set +a
  sed 's/SERVMASK_PREFIX_/wp_/g' "$DIR/database.sql" \
    | docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"
  echo "Restauracion lista desde $BACKNO"
}

if [ "${1:-}" = "--restaurar" ]; then
  restaurar
else
  echo "Levantando WordPress. Respaldo de emergencia en $BACKNO"
  docker compose up -d
fi
