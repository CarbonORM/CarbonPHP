#!/usr/bin/env bash

APP_ROOT=$(pwd)

file="/etc/hosts"

if ! grep -q dev.carbonphp.com "$file"; then
  sudo -- sh -c "echo 127.0.0.1 dev.carbonphp.com >> $file"
fi

cd "$APP_ROOT" || exit

sudo php -S dev.carbonphp.com:80 index.php