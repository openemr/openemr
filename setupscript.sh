#! /usr/bin/env bash

set -x

sudo apt-get update && sudo apt-get -y upgrade \
sudo apt-get -y autoremove && sudo apt-get -y update

sudo apt-get install \ build-essential apache2 git tar curl \
python openssl git libffi-dev py-pip python-dev build-base openssl-dev \
dcron vim nano bash bash-doc bash-completion tree curl \ apache2
mariadb-server libapache2-mod-php libtiff-tools php php-mysql php-cli \
php-gd php-xsl php-curl php=mcrypt php-soap php-json php-gettext imagemagick \
php-mbstring php-zip php-ldap php-xml


sudo git clone https://github.com/vaibhavgupta3110/openemr.git -b test /var/www/html

# openemr's setup requireemnts

sudo chmod 666 \
'/var/www/html/sites/*/sqlconf.php' \
'/var/www/html/interface/modules/zend_modules/config/application.config.php'

sudo chown -R www-data \
'/var/www/html/sites/*/documents' \
'/var/www/html/sites/*/edi' \
'/var/www/html/sites/*/era' \
'/var/www/html/sites/*/letter_templates' \
'/var/www/html/gacl/admin/templates_c' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/compiled' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/cache'
