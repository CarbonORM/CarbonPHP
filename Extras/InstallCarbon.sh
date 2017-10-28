#!/bin/bash
DIR=`pwd`

# we should see is brew and other tools are installed

# brew install php70 --with-homebrew-curl

# Setup File Structure
mkdir Application
mkdir Public
mkdir Tests
mkdir Data

cd Application
mkdir Configs
mkdir Controller
mkdir Model
mkdir Services
mkdir View

cd ${DIR}
cd Data
mkdir Cache
mkdir Indexes
mkdir Logs
mkdir Session
mkdir Temp
mkdir Uploads
mkdir Views

# Install Composer and CarbonPHP
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php -f composer.phar global require "fxp/composer-asset-plugin:~1.3"
php -f composer.phar require "richardtmiles/carbonphp:dev-master"
php -f composer.phar require "almasaeed2010/adminlte:>=2.4"
php -f composer.phar require "bower-asset/jquery-backstretch:^2.1.16"

# Move files to Root
cd vendor/richardtmiles/carbonphp/Extras
cp exRoutes.php ${DIR}/Application/Routes.php
cp AdminLTE.php ${DIR}/Public/Wrapper.php
cp exIndex.php ${DIR}/index.php
cp robots.txt ${DIR}/Data/robots.txt
cp .htaccess ${DIR}/.htaccess
