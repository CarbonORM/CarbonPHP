#!/usr/bin/env bash

APP_ROOT=$(pwd)

file="/etc/hosts"

if ! grep -q local.carbonphp.com "$file"; then
  sudo -- sh -c "echo 127.0.0.1 local.carbonphp.com >> $file"
fi

cd "$APP_ROOT" || exit

sudo php -S local.carbonphp.com:80 index.php || sudo php -S local.carbonphp.com:8080 index.php