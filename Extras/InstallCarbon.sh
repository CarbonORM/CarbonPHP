#!/bin/bash
DIR=`pwd`

# we should see is brew and other tools are installed

# brew install php70 --with-homebrew-curl

# Setup File Structure

createFile() {
if [ -d $1 ]; then
  printf "dir $1 exists.\n"
else
  mkdir $1
  printf "Created $1 created.\n"
fi
}

createFile Application
createFile Public
createFile Tests
createFile Data

cd Application
createFile Configs
createFile Controller
createFile Model
createFile Services
createFile View

cd ${DIR}
cd Data
createFile Cache
createFile Indexes
createFile Logs
createFile Session
createFile Temp
createFile Uploads
createFile Views

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
cd vendor/richardtmiles/carbonphp/Extras
cp exRoutes.php  ${DIR}/Application/Routes.php
cp exOptions.php ${DIR}/Application/Configs/Options.php
cp exIndex.php   ${DIR}/index.php

cp AdminLTE.php ${DIR}/Public/Wrapper.php
cp robots.txt ${DIR}/Data/robots.txt
cp .htaccess ${DIR}/.htaccess
