#!/usr/bin/env bash

echo "$@"

# @link https://www.gnu.org/software/bash/manual/html_node/The-Shopt-Builtin.html
# if a command fails and piped to `cat`, for example, the full command will exit failure,.. cat will not run.?
# @link https://distroid.net/set-pipefail-bash-scripts/?utm_source=rss&utm_medium=rss&utm_campaign=set-pipefail-bash-scripts
# @link https://transang.me/best-practice-to-make-a-shell-script/
# @link https://stackoverflow.com/questions/2853803/how-to-echo-shell-commands-as-they-are-executed
set -eEBx

# @link https://stackoverflow.com/questions/5412761/using-colors-with-printf
blue=$(tput setaf 4)

normal=$(tput sgr0)

function projectInfo() {
  # Spaces included before clone so linux doesnt store command, as it contains keys
  printf "\n\n"
  gcloud projects list
  printf "\n\n"
  gcloud compute instances list
  printf "\n\n"
}

function deploy() {

  if [ -z "$1" ]; then

    printf "\n\nNo instance argument supplied. Please enter the instance you would like to deploy to.\n"

    read -p "New instance name :: " -r varname

    # Todo - this should only be created once, currently throws an error
    # gcloud compute firewall-rules create "websocket" --allow=tcp:8888

    # @link see all - gcloud compute images list --filter ubuntu-os-cloud
    gcloud compute instances create "$varname" \
      --image="ubuntu-2204-lts-arm64" \
      --boot-disk-size=50GB \
      --image-project="ubuntu-os-cloud" \
      --zone="us-central1-b" \
      --tags="http-server,https-server,websocket"

       sleep 30


      # TODO - Username auth for DIG
      gcloud compute ssh "$varname" --zone="us-central1-b" --command \
        "     git clone https://github.com/RichardTMiles/CarbonPHP.git && sudo chmod 777 ./CarbonPHP/src/programs/gcpDeployment.sh && ./CarbonPHP/src/programs/gcpDeployment.sh -ubuntu;"

    exit 0

  fi

  gcloud compute instances add-tags "$1" --tags=websocket

  gcloud compute ssh "$1" --command \
    "     git clone https://github.com/RichardTMiles/CarbonPHP.git && sudo chmod 777 ./CarbonPHP/src/programs/gcpDeployment.sh && ./CarbonPHP/src/programs/gcpDeployment.sh -ubuntu;"

}

function delete() {

  if [ -z "$1" ]; then

    echo "No instance argument supplied. Please the name of the instance you would like to delete."

    read -p "Delete instance name :: " -r varname

    gcloud compute instances delete "$varname"

    exit 0

  fi

  gcloud compute instances delete "$1"

  printf "\n\n"

}

function setupUbuntu20() {

  printf "%40s\n" "${blue}sudo groupadd c6devteam${normal}"

  sudo groupadd c6devteam

  printf "%40s\n" "${blue}sudo usermod -a -G c6devteam \"$(whoami)\"${normal}"

  sudo usermod -a -G c6devteam "$(whoami)"

  # I update git in this script bc I regularly forget/find/changes before halfway through array deploymant
  # gcloud beta sql databases patch

  printf "\n\n\t\e[1;34m Remember to commit your changes! \e[0m\n"

  sudo apt update

  sudo apt autoremove

  sudo apt -y upgrade

  sudo apt -y install software-properties-common

  sudo add-apt-repository -y universe

  sudo apt -y install apache2

  sudo apt update  # i know this is done twice  https://certbot.eff.org/lets-encrypt/ubuntufocal-apache.html

  sudo ufw app list

  sudo apt -y install wget curl php composer node libapache2-mod-php php-curl php-mysql php-zip php-xml

  sudo apt -y autoremove

  sudo ufw allow 'Apache Full'

  sudo ufw delete allow 'Apache'

  sudo systemctl restart apache2

  sudo ufw status

  sudo apt -y install certbot python3-certbot-apache

  sudo apt -y install python3-certbot-dns-google

  sudo chown -R root:c6devteam /var/www

  sudo chmod g+rwX /var/www/ -R

  sudo chmod 777 "${DIR}/certbot-auto"

  printf "\n\n\t\e[1;34m Updating REPO && Installing Composer For GCP-DEPLOYMENT \e[0m\n"

  composer install -d "${DIR}"

  sleep 5

  git -C "$HOME/CarbonPHP" pull

  # This downloads our
  # github repositories and changes the Google DNS locations
  sudo php "${DIR}/index.php" Deployment

  sudo chown -R root:c6devteam /var/www

  sudo chmod g+rwX /var/www/ -R

  sleep 10

  printf "\n\n\t\e[1;34m Starting Certbot Auto \e[0m\n"

  sudo certbot --apache -m Richard@Miles.Systems

  sudo certbot renew --dry-run

  sudo a2dismod mpm_prefork

  sudo a2enmod mpm_event proxy_fcgi setenvif http2  # untested - sudo a2enmod http2

  sudo a2enconf php-fpm

  sudo systemctl enable php-fpm

  cat > /etc/apache2/mods-enabled/dir.conf << EOL
<IfModule mod_dir.c>
        DirectoryIndex index.php
</IfModule>
# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
EOL

  cd /etc/apache2/sites-available

  sudo sed -i 's/:443>/:443>\nProtocols h2 http\/1.1\n/g' *

  sudo a2enmod rewrite

  sudo a2enmod headers

  sudo a2enmod deflate

  sudo a2enmod proxy

  sudo a2enmod proxy_http

  sudo a2enmod proxy_wstunnel

  sudo a2enmod proxy_balancer

  sudo a2enmod lbmethod_byrequests

  gcloud sql instances patch carbonphpmaster --authorized-networks=$( gcloud compute instances describe $( hostname ) \
                                                                        --format='get(networkInterfaces[0].networkIP)' )

  sudo systemctl restart apache2

  printf "\n\n\t\e[1;34m Finished. \e[0m\n"

}

if [ $# -lt 1 ]; then
  echo "Usage : $0"
  echo "        -deploy instance_name (optional)"
  echo "        -delete instance_name (optional)"
  exit
fi

case "$1" in
-deploy)
  echo "Starting deployment routine"
  projectInfo
  deploy "$2"
  ;;
-delete)
  echo "starting delete instance routine"
  projectInfo
  delete "$2"
  ;;
-ubuntu)
  setupUbuntu20
  ;;
*)
  echo "The second argument may only be either [ -deploy, -delete ] "
  ;;
esac
