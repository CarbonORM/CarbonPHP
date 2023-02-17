#!/usr/bin/env bash

set -e

# @link https://stackoverflow.com/questions/5412761/using-colors-with-printf
MAGENTA=$(tput -T 'xterm-256color' setaf 5)
CYAN=$(tput -T 'xterm-256color' setaf 6)
NORMAL=$(tput -T 'xterm-256color' sgr0)

SQL_FILE="$1"

replaceDelimited="$2"

replace="$3"

replacementDelimited="$4"

replacement="$5"

if ! grep --quiet "$replace" "$SQL_FILE"; then

  echo "{$MAGENTA}The string ($replace) was not found in ($SQL_FILE){$NORMAL}"

  exit 0

fi

echo "{$CYAN}Will replace string ($replace) was found in ($SQL_FILE){$NORMAL}"

cp "$SQL_FILE" "$SQL_FILE.old.sql"

# @link https://stackoverflow.com/questions/29902647/sed-match-replace-url-and-update-serialized-array-count
# @link https://serverfault.com/questions/1114188/php-serialize-awk-command-speed-up/1114191#1114191
sed 's/;s:/;\ns:/g' "$SQL_FILE" |
  awk -F'"' '/s:.+'$replaceDelimited'/ {sub("'$replace'", "'$replacement'"); n=length($2)-1; sub(/:[[:digit:]]+:/, ":" n ":")} 1' 2>/dev/null |
  sed -e ':a' -e 'N' -e '$!ba' -e 's/;\ns:/;s:/g' |
  sed "s/$replaceDelimited/$replacementDelimited/g" >"$SQL_FILE.replaced.sql"

cp "$SQL_FILE.replaced.sql" "$SQL_FILE"
