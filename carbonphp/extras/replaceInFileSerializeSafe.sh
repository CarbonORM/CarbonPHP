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

cp "$SQL_FILE" "$SQL_FILE.original.sql"


if ! grep --quiet "$replace" "$SQL_FILE"; then

  echo "${MAGENTA}The string ($replace) was not found in ($SQL_FILE) $NORMAL"

  exit 0

fi

echo "${CYAN}Will replace string ($replace) was found in ($SQL_FILE)${NORMAL}"

if [ -x /usr/local/bin/gawk ]
then
    AWK=/usr/local/bin/gawk
else
    AWK=/usr/bin/awk
fi


if [ -x /usr/local/bin/gsed ]
then
    SED=/usr/local/bin/gsed
else
    SED=/usr/bin/sed
fi

# @link https://stackoverflow.com/questions/29902647/sed-match-replace-url-and-update-serialized-array-count
# @link https://serverfault.com/questions/1114188/php-serialize-awk-command-speed-up/1114191#1114191
time ( $SED 's/;s:/;\ns:/g' "$SQL_FILE" |
  $AWK -F'"' '/s:.+'$replaceDelimited'/ {sub("'$replace'", "'$replacement'"); n=length($2)-1; sub(/:[[:digit:]]+:/, ":" n ":")} 1' 2>/dev/null |
  $SED -e ':a' -e 'N' -e '$!ba' -e 's/;\ns:/;s:/g' |
  $SED "s/$replaceDelimited/$replacementDelimited/g" > "$SQL_FILE.replaced.sql" )

# the pipe above Absolutely MUST to feed into a new file and get moved here below; removing the new file step will cause an empty final file
cp "$SQL_FILE.replaced.sql" "$SQL_FILE"
