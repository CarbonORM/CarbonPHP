#!/usr/bin/env bash

set -e

SQL_FILE="$1"

replaceDelimited="$2"

replace="$3"

replacementDelimited="$4"

replacement="$5"

cp "$SQL_FILE" "$SQL_FILE.old.sql"

# @link https://stackoverflow.com/questions/29902647/sed-match-replace-url-and-update-serialized-array-count
# @link https://serverfault.com/questions/1114188/php-serialize-awk-command-speed-up/1114191#1114191
sed 's/;s:/;\ns:/g' "$SQL_FILE" | \
  awk -F'"' '/s:.+'$replaceDelimited'/ {sub("'$replace'", "'$replacement'"); n=length($2)-1; sub(/:[[:digit:]]+:/, ":" n ":")} 1' 2>/dev/null  | \
  sed -e ':a' -e 'N' -e '$!ba' -e 's/;\ns:/;s:/g' | \
  sed "s/$replaceDelimited/$replacementDelimited/g" > "$SQL_FILE.txt"

mv "$SQL_FILE.txt" "$SQL_FILE"
