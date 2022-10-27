#!/usr/bin/env bash

set -e

SQL_FILE="$1"

maindomain="$2"

localdomain="$3"

sed 's/;s:/;\ns:/g' "$SQL_FILE" | \
  awk -F'"' '/s:.+'$maindomain'/ {sub("'$maindomain'", "'$localdomain'"); n=length($2)-1; sub(/:[[:digit:]]+:/, ":" n ":")} 1' | \
  sed -e ':a' -e 'N' -e '$!ba' -e 's/\n/ /g' | \
  sed "s/$maindomain/$localdomain/g" > "$SQL_FILE.txt" \
&& rm "$SQL_FILE" \
&& mv "$SQL_FILE.txt" "$SQL_FILE"
