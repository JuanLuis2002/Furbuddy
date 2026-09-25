#!/bin/sh
# Despliega la vista Vue y la API Python. No toca WordPress.
set -eu
cd /root/Furbuddy
git fetch origin main
git reset --hard origin/main
docker compose up -d --build
