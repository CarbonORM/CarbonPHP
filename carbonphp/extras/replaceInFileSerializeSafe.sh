#!/usr/bin/env bash

set -e

# Color setup
MAGENTA=$(tput -T 'xterm-256color' setaf 5)
CYAN=$(tput -T 'xterm-256color' setaf 6)
RED=$(tput -T 'xterm-256color' setaf 1)
NORMAL=$(tput -T 'xterm-256color' sgr0)

# Input variables
SQL_FILE="$1"
replaceDelimited="$2"
replace="$3"
replacementDelimited="$4"
replacement="$5"

# Backup original SQL file
cp "$SQL_FILE" "$SQL_FILE.original.sql"

# Check for string presence
if ! grep --quiet "$replace" "$SQL_FILE"; then
  echo "${MAGENTA}The string ($replace) was not found in ($SQL_FILE) $NORMAL"
  exit 0
fi

echo "${CYAN}Will replace string ($replace) was found in ($SQL_FILE)${NORMAL}"

# Function to detect OS and install missing packages
install_package_if_missing() {
    local package=$1
    echo "Checking for $package..."
    if ! command -v "$package" &> /dev/null; then
        echo "$package could not be found, attempting to install..."
        case "$(uname -s)" in
            Linux*)

                if ! sudo -n true 2>/dev/null; then
                    echo "User does not have passwordless sudo access."
                    if [ ! -t 1 ]; then
                        echo "This script is not running in a TTY. Please run it in a TTY or install $package manually."
                        # Handle non-TTY case or exit
                        exit 1
                    fi
                fi

                if [ -f /etc/debian_version ]; then
                    sudo apt-get update && sudo apt-get install -y "$package"
                elif [ -f /etc/redhat-release ]; then
                    sudo yum install -y "$package"
                elif [ -f /etc/fedora-release ]; then
                    sudo dnf install -y "$package"
                else
                    echo "Unsupported Linux distribution for automatic installation."
                    exit 1
                fi
                ;;
            Darwin*)
                if ! command -v brew &> /dev/null; then
                    echo "Homebrew not found. Please install Homebrew to automatically install $package."
                    exit 1
                fi
                brew install "$package"
                ;;
            *)
                echo "Unsupported OS for automatic installation."
                exit 1
                ;;
        esac
    else
        echo "$package is already installed."
    fi
}

# Assuming gawk and gsed are now installed and available in PATH
# Define the path for the cache file
CACHE_FILE="/tmp/perform_checks_and_installations.txt"

# Get the current parent process ID (PPID)
CURRENT_PPID=$PPID

# Function to perform necessary checks and installations
perform_checks_and_installations() {
    echo "Performing checks and installations if necessary..."
     # Install gawk and gsed if they are missing
     install_package_if_missing "gawk" || echo "Will use the default awk"
     install_package_if_missing "gsed" || echo "Will use the default sed"
    # Update the cache file with the current PPID
    echo "$CURRENT_PPID" > "$CACHE_FILE"
}

# Check if the cache file exists
if [ -f "$CACHE_FILE" ]; then
    # Cache file exists, read the PPID stored in it
    CACHED_PPID=$(cat "$CACHE_FILE")
    # Check if the cached PPID matches the current PPID
    if [ "$CACHED_PPID" -eq "$CURRENT_PPID" ]; then
        echo "${CYAN}Required checks already performed for the current parent process ($CURRENT_PPID).${NORMAL}"
    else
        # The PPIDs do not match, indicating a different parent process session
        perform_checks_and_installations
    fi
else
    # Cache file does not exist, perform checks and installations
    perform_checks_and_installations
fi


AWK=$(command -v gawk || command -v awk)

SED=$(command -v gsed || command -v sed)

# Ensure AWK and SED are not empty; otherwise, report an error
if [ -z "$AWK" ]; then
  echo "${RED}AWK (gawk or awk) could not be found. Please install it and rerun the script.${NORMAL}"
  exit 1
fi

# check if awk is used over gawk
if ! command -v gawk &>/dev/null; then
    echo "${RED}Using system awk instead of gawk; This could be incredibly,painfully,impossibly slow. Please install gawk and rerun the script.${NORMAL}"
fi

if [ -z "$SED" ]; then
  echo "${RED}SED (gsed or sed) could not be found. Please install it and rerun the script.${NORMAL}"
  exit 1
fi

# Perform the replacement operation
time ( $SED 's/;s:/;\ns:/g' "$SQL_FILE" |
  $AWK -F'"' '/s:.+'$replaceDelimited'/ {sub("'$replace'", "'$replacement'"); n=length($2)-1; sub(/:[[:digit:]]+:/, ":" n ":")} 1' 2>/dev/null |
  $SED -e ':a' -e 'N' -e '$!ba' -e 's/;\ns:/;s:/g' |
  $SED "s/$replaceDelimited/$replacementDelimited/g" > "$SQL_FILE.replaced.sql" )

# Final step to replace original file with modified one
cp "$SQL_FILE.replaced.sql" "$SQL_FILE"