#!/bin/bash
DIR=`pwd`

# we should see is brew and other tools are installed

# brew install php70 --with-homebrew-curl

# Setup File Structure

createFolder() {
if [ -d $1 ]; then
  printf "dir $1 exists.\n"
else
  mkdir $1
  printf "Created $1 \n"
fi
}

createFolder Application
createFolder Public
createFolder Tests
createFolder Data

cd Application
createFolder Configs
createFolder Controller
createFolder Model
createFolder Services
createFolder View

cd ../
cd Data
createFolder Cache
createFolder Indexes
createFolder Logs
createFolder Session
createFolder Temp
createFolder Uploads
createFolder Views

# Install Composer and CarbonPHP
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php -f composer.phar global require "fxp/composer-asset-plugin:~1.3"
php -f composer.phar require --dev --no-suggest "richardtmiles/carbonphp:dev-master"
php -f composer.phar require --dev --prefer-stable "almasaeed2010/adminlte:>=2.4"
php -f composer.phar require "bower-asset/jquery-backstretch:^2.1.16"

# Move files to Root
cp ./vendor/richardtmiles/carbonphp/Extras/exIndex.php    ./index.php
cp ./vendor/richardtmiles/carbonphp/Extras/exRoutes.php   ./Application/Routes.php
cp ./vendor/richardtmiles/carbonphp/Extras/exOptions.php  ./Application/Configs/Options.php
cp ./vendor/richardtmiles/carbonphp/Extras/AdminLTE.php   ./Public/Wrapper.php
cp ./vendor/richardtmiles/carbonphp/Extras/robots.txt     ./Data/robots.txt
cp ./vendor/richardtmiles/carbonphp/Extras/.htaccess      ./.htaccess
