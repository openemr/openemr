#!/bin/bash

set -e

TEST_USER=$1
WORKSPACE=$2
JOB=$3

COMMAND=$(echo "${JOB}" | jq -r '.command')

if [[ ! ${COMMAND} =~ phpunit ]]; then
    exit 0
fi

PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

# Install CI version of phpunit config
cp .laminas-ci/phpunit.xml phpunit.xml

# Install dependendies
apt update -qq
apt install -y apache2 php${PHP_VERSION}-fpm

# Enable required modules
a2enmod rewrite actions proxy_fcgi setenvif alias
a2enconf php${PHP_VERSION}-fpm

# Setup and start php-fpm
echo "cgi.fix_pathinfo = 1" >> /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i -e "s,www-data,${TEST_USER},g" /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
sed -i -e "s,www-data,${TEST_USER},g" /etc/apache2/envvars
service php${PHP_VERSION}-fpm start

# configure apache virtual hosts
echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf
cp -f .laminas-ci/site.conf /etc/apache2/sites-available/000-default.conf
sed -i -e "s?%BUILD_DIR%?${WORKSPACE}?g" /etc/apache2/sites-available/000-default.conf
sed -i -e "s?%PHP_VERSION%?${PHP_VERSION}?g" /etc/apache2/sites-available/000-default.conf

# enable TRACE
sed -i -e "s?TraceEnable Off?TraceEnable On?g" /etc/apache2/conf-available/security.conf

# configure proxy
a2enmod proxy proxy_http proxy_connect
cp -f .laminas-ci/proxy.conf /etc/apache2/sites-available/proxy.conf
a2ensite proxy
sed -i -e "s/Listen 80/Listen 80\nListen 8081/" /etc/apache2/ports.conf
service apache2 restart
