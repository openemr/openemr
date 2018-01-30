#! /usr/bin/env bash

set -x

sudo apt-get update
sudo apt-get -y upgrade

sudo apt-get -y -f install build-essential apache2 git tar curl python openssl git \
libffi-dev python-dev vim nano bash bash-doc \
bash-completion tree curl apache2 mariadb-server libapache2-mod-php \
libtiff-tools php php-mysql php-cli php-gd php-xsl php-curl php-mcrypt php-soap \
php-json php-gettext imagemagick php-mbstring php-zip php-ldap php-xml

sudo mysql -u root
# use mysql;
# update user set plugin='' where User='root';
# flush privileges;
# \q
sudo mysql_secure_installation

sudo service apache2 restart
sudo service mysql restart

cd /var/www/ || return

sudo rm -r html || return

sudo git clone https://github.com/vaibhavgupta3110/openemr --branch test html

sudo service apache2 restart
sudo service mysql restart

# openemr's setup requireemnts

sudo chmod 666 \
'/var/www/*/sites/default/sqlconf.php' \
'/var/www/*/interface/modules/zend_modules/config/application.config.php'

sudo chown -R www-data \
'/var/www/*/sites/default/documents' \
'/var/www/*/sites/default/edi' \
'/var/www/*/sites/default/era' \
'/var/www/*/sites/default/letter_templates' \
'/var/www/*/gacl/admin/templates_c' \
'/var/www/*/interface/main/calendar/modules/PostCalendar/pntemplates/compiled' \
'/var/www/*/interface/main/calendar/modules/PostCalendar/pntemplates/cache'
