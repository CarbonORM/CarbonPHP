#!/usr/bin/env bash

# @link https://stackoverflow.com/questions/5412761/using-colors-with-printf
blue=$(tput setaf 4)
normal=$(tput sgr0)

function projectInfo() {
  # Spaces included before clone so linux doesnt store command, as it contains keys
  printf "\n\n"
  printf "%40s\n" "${blue}gcloud projects list${normal}"
  gcloud projects list
  printf "\n\n"
  printf "%40s\n" "${blue}gcloud compute instances list${normal}"
  gcloud compute instances list
  printf "\n\n"
}

function deploy() {
  if [ -z "$1" ]; then
    printf "\n\nNo instance argument supplied. Please enter the instance you would like to deploy to.\n"

    read -p "New instance name :: " -r varname

    # Todo - this should only be created once, currently throws an error
    gcloud compute firewall-rules create "websocket" --allow=tcp:8888

    gcloud compute instances create "$varname" \
      --image="ubuntu-2004-focal-v20200720" \
      --boot-disk-size=10GB \
      --image-project="ubuntu-os-cloud" \
      --zone="us-central1-a" \
      --tags="http-server,https-server,websocket" && sleep 30s &&

      # TODO - Username auth for DIG
      gcloud compute ssh "$varname" --zone="us-central1-a" --command \
        "     git clone https://github.com/RichardTMiles/CarbonPHP.git && sudo chmod 777 ./CarbonPHP/src/programs/shell/gcpDeployment.sh && ./CarbonPHP/src/programs/shell/gcpDeployment.sh -ubuntu;"
    exit 0
  fi

  gcloud compute instances add-tags "$1" --tags=websocket

  gcloud compute ssh "$1" --command \
    "     git clone https://github.com/RichardTMiles/CarbonPHP.git && sudo chmod 777 ./CarbonPHP/src/programs/shell/gcpDeployment.sh && ./CarbonPHP/src/programs/shell/gcpDeployment.sh -ubuntu;"

}

function delete() {

  if [ -z "$1" ]; then
    echo "No instance argument supplied. Please the name of the instance you would like to delete."

    read -p "Delete instance name :: " -r varname

    gcloud compute instances delete "$varname"

    printf "\n\n"

    exit 1
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

  printf "\n\n\t\e[1;34m Remeber to commit your changes! \e[0m\n" &&
    printf "%40s\n" "${blue}sudo apt update${normal}" &&
    sudo apt update &&
    printf "%40s\n" "${blue}sudo apt autoremove${normal}" &&
    sudo apt autoremove &&
    printf "%40s\n" "${blue}sudo apt -y upgrade${normal}" &&
    sudo apt -y upgrade &&
    printf "%40s\n" "${blue}sudo apt -y install software-properties-common${normal}" &&
    sudo apt -y install software-properties-common &&
    printf "%40s\n" "${blue}sudo add-apt-repository -y universe ${normal}" &&
    sudo add-apt-repository -y universe &&
    printf "%40s\n" "${blue}sudo apt -y install apache2 ${normal}" &&
    sudo apt -y install apache2 &&
    printf "%40s\n" "${blue}sudo apt update${normal}" &&
    sudo apt update && # i know this is done twice  https://certbot.eff.org/lets-encrypt/ubuntufocal-apache.html
    printf "%40s\n" "${blue}sudo ufw app list${normal}" &&
    sudo ufw app list &&
    printf "%40s\n" "${blue}sudo apt -y install wget ${normal}" &&
    sudo apt -y install wget &&
    printf "%40s\n" "${blue}sudo apt -y install curl${normal}" &&
    sudo apt -y install curl &&
    printf "%40s\n" "${blue}sudo apt -y install composer ${normal}" &&
    sudo apt -y install composer &&
    printf "%40s\n" "${blue}sudo apt -y install npm${normal}" &&
    sudo apt -y install npm &&
    printf "%40s\n" "${blue}sudo apt -y install php libapache2-mod-php php-curl php-mysql php-zip php-xml php7.4-cli${normal}" &&
    sudo apt -y install php libapache2-mod-php php-curl php-mysql php-zip php-xml php7.4-cli &&
    printf "%40s\n" "${blue}sudo apt -y autoremove ${normal}" &&
    sudo apt -y autoremove &&
    printf "%40s\n" "${blue}sudo ufw allow 'Apache Full'${normal}" &&
    sudo ufw allow 'Apache Full' &&
    printf "%40s\n" "${blue}sudo ufw delete allow 'Apache'${normal}" &&
    sudo ufw delete allow 'Apache' &&
    printf "%40s\n" "${blue}sudo systemctl restart apache2${normal}" &&
    sudo systemctl restart apache2 &&
    printf "%40s\n" "${blue}sudo ufw status${normal}" &&
    sudo ufw status &&
    printf "%40s\n" "${blue}sudo apt -y install certbot python3-certbot-apache ${normal}" &&
    sudo apt -y install certbot python3-certbot-apache &&
    printf "%40s\n" "${blue}sudo apt -y install python3-certbot-dns-google${normal}" &&
    sudo apt -y install python3-certbot-dns-google &&
    printf "%40s\n" "${blue}sudo chown -R root:c6devteam /var/www${normal}" &&
    sudo chown -R root:c6devteam /var/www &&
    printf "%40s\n" "${blue}sudo chmod g+rwX /var/www/ -R${normal}" &&
    sudo chmod g+rwX /var/www/ -R &&
    printf "%40s\n" "${blue}sudo chmod 777 \"${DIR}/certbot-auto\"${normal}" &&
    sudo chmod 777 "${DIR}/certbot-auto" &&
    printf "\n\n\t\e[1;34m Updating REPO && Installing Composer For GCP-DEPLOYMENT \e[0m\n" &&
    printf "%40s\n" "${blue}omposer install -d \"${DIR}\"${normal}" &&
    composer install -d "${DIR}" &&
    printf "%40s\n" "${blue}sleep 5${normal}" &&
    sleep 5 &&
    printf "%40s\n" "${blue}git -C \"$HOME/CarbonPHP\" pull ${normal}" &&
    git -C "$HOME/CarbonPHP" pull &&
    printf "%40s\n" "${blue}php \"${DIR}/index.php\" Deployment ${normal}" &&
    sudo php "${DIR}/index.php" Deployment &&
    printf "%40s\n" "${blue}sudo chown -R root:c6devteam /var/www ${normal}" &&
    sudo chown -R root:c6devteam /var/www &&
    printf "%40s\n" "${blue}sudo chmod g+rwX /var/www/ -R${normal}" &&
    sudo chmod g+rwX /var/www/ -R &&
    printf "\n\n\t\e[1;34m Sleeping for 10 seconds so DNS records can update \e[0m\n" &&
    sleep 10 &&
    printf "\n\n\t\e[1;34m Starting Certbot Auto \e[0m\n" &&
    printf "%40s\n" "${blue}sudo certbot --apache -m Richard@Miles.Systems${normal}" &&
    sudo certbot --apache -m Richard@Miles.Systems &&
    printf "%40s\n" "${blue}sudo certbot renew --dry-run ${normal}" &&
    sudo certbot renew --dry-run &&
    printf "\n\n\t\e[1;34m Running Apache H2 Enable Process \e[0m\n" &&
    # TODO - send correct resources - https://www.linuxbabe.com/ubuntu/enable-http-2-apache-ubuntu-20-04
    printf "%40s\n" "${blue} sudo apt install php7.4-fpm${normal}" &&
    sudo apt install php7.4-fpm &&
    printf "%40s\n" "${blue}sudo systemctl start php7.4-fpm ${normal}" &&
    sudo systemctl start php7.4-fpm &&
    printf "%40s\n" "${blue}sudo a2dismod php7.4${normal}" &&
    sudo a2dismod php7.4 &&
    printf "%40s\n" "${blue}sudo a2dismod mpm_prefork${normal}" &&
    sudo a2dismod mpm_prefork &&
    printf "%40s\n" "${blue}sudo a2enmod mpm_event proxy_fcgi setenvif http2${normal}" &&
    sudo a2enmod mpm_event proxy_fcgi setenvif http2 && # untested - sudo a2enmod http2
    printf "%40s\n" "${blue}sudo a2enconf php7.4-fpm${normal}" &&
    sudo a2enconf php7.4-fpm &&
    printf "%40s\n" "${blue}sudo systemctl enable php7.4-fpm ${normal}" &&
    sudo systemctl enable php7.4-fpm &&
    printf "%40s\n" "${blue}cd /etc/apache2/sites-available${normal}" &&
    cd /etc/apache2/sites-available &&
    printf "%40s\n" "${blue}sudo sed -i 's/:443>/:443>\nProtocols h2 http\/1.1\n/g' * ${normal}" &&
    sudo sed -i 's/:443>/:443>\nProtocols h2 http\/1.1\n/g' * &&
    printf "%40s\n" "${blue}sudo systemctl restart apache2${normal}" &&
    sudo systemctl restart apache2 &&
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
