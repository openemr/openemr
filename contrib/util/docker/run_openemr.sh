#!/bin/sh
# to be run by root with OpenEMR root dir as the CWD

# Allows customization of openemr credentials, preventing the need for manual setup
#  (Note can force a manual setup by setting MANUAL_SETUP to 'yes')
#  - Required settings for auto installation are MYSQL_HOST and MYSQL_ROOT_PASS
#  -  (note that can force MYSQL_ROOT_PASS to be empty by passing as 'BLANK' variable)
#  - Optional settings for auto installation are:
#    - Setting db parameters MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE
#    - Setting openemr parameters OE_USER, OE_PASS
set -e

if [ "$DOMAIN" != "" ]; then
    if [ "$EMAIL" != "" ]; then
        echo "WARNING: SETTING AN EMAIL VIA \$EMAIL is HIGHLY RECOMMENDED IN ORDER TO"
        echo "         RECEIVE ALERTS FROM LETSENCRYPT ABOUT YOUR SSL CERTIFICATE."
    fi
    # if a domain has been set
    certbot certonly --webroot -n -w /var/www/localhost/htdocs/openemr/ -d $DOMAIN -m $EMAIL --agree-tos
    echo "1\t23\t*\t*\t*\tcertbot renew -q --post-hook \"httpd -k graceful\"" >> /etc/crontabs/root
    # run letsencrypt as a daemon and reference the correct cert
    rm -f /etc/ssl/certs/webserver.cert.pem
    rm -f /etc/ssl/private/webserver.key.pem
    ln -s /etc/letsencrypt/live/$DOMAIN/fullchain.pem /etc/ssl/certs/webserver.cert.pem
    ln -s /etc/letsencrypt/live/$DOMAIN/privkey.pem /etc/ssl/private/webserver.key.pem
else
    # if a domain hasn't been set
    # ensure a self-signed cert has been generated and is referenced
    if ! [ -f /etc/ssl/private/selfsigned.key.pem ]; then
        openssl req -x509 -newkey rsa:4096 \
        -keyout /etc/ssl/private/selfsigned.key.pem \
        -out /etc/ssl/certs/selfsigned.cert.pem \
        -days 365 -nodes \
        -subj "/C=xx/ST=x/L=x/O=x/OU=x/CN=localhost"
    fi
    rm -f /etc/ssl/certs/webserver.cert.pem
    rm -f /etc/ssl/private/webserver.key.pem
    ln -s /etc/ssl/certs/selfsigned.cert.pem /etc/ssl/certs/webserver.cert.pem
    ln -s /etc/ssl/private/selfsigned.key.pem /etc/ssl/private/webserver.key.pem
fi

auto_setup() {
    chmod 666 /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php
    chmod 666 /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/config/application.config.php

    CONFIGURATION="server=${MYSQL_HOST} rootpass=${MYSQL_ROOT_PASS} loginhost=%"
    if [ "$MYSQL_ROOT_USER" != "" ]; then
        CONFIGURATION="${CONFIGURATION} root=${MYSQL_ROOT_USER}"
    fi
    if [ "$MYSQL_USER" != "" ]; then
        CONFIGURATION="${CONFIGURATION} login=${MYSQL_USER}"
    fi
    if [ "$MYSQL_PASS" != "" ]; then
        CONFIGURATION="${CONFIGURATION} pass=${MYSQL_PASS}"
    fi
    if [ "$MYSQL_DATABASE" != "" ]; then
        CONFIGURATION="${CONFIGURATION} dbname=${MYSQL_DATABASE}"
    fi
    if [ "$OE_USER" != "" ]; then
        CONFIGURATION="${CONFIGURATION} iuser=${OE_USER}"
    fi
    if [ "$OE_PASS" != "" ]; then
        CONFIGURATION="${CONFIGURATION} iuserpass=${OE_PASS}"
    fi

    #cp /var/www/localhost/htdocs/openemr_for_build/sites/default/sqlconf.php /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php
    #cp /var/www/localhost/htdocs/openemr_for_build/interface/modules/zend_modules/config/application.config.php /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/config/application.config.php
    #chmod -R 600 .
    php ../auto_configure.php -f ${CONFIGURATION} || return 1

    echo "OpenEMR configured."
    CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
    if [ "$CONFIG" == "0" ]; then
        echo "Error in auto-config. Configuration failed."
        exit 2
    fi
}

CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
if [ "$CONFIG" == "0" ] &&
   [ "$MYSQL_HOST" != "" ] &&
   [ "$MYSQL_ROOT_PASS" != "" ] &&
   [ "$MANUAL_SETUP" != "yes" ]; then

    echo "Running quick setup!"
    while ! auto_setup; do
        echo "Couldn't set up. Any of these reasons could be what's wrong:"
        echo " - You didn't spin up a MySQL container or connect your OpenEMR container to a mysql instance"
        echo " - MySQL is still starting up and wasn't ready for connection yet"
        echo " - The Mysql credentials were incorrect"
        sleep 1;
    done
    echo "Setup Complete!"
fi

if [ "$CONFIG" == "1" ]; then
    # echo "Setting user 'www' as owner of openemr/ and setting file/dir permissions to 400/500"
    # #set all directories to 500
    # find /var/www/localhost/htdocs/openemr -type d -print0 | xargs -0 chmod 500
    # #set all file access to 400
    # find /var/www/localhost/htdocs/openemr -type f -print0 | xargs -0 chmod 400

    echo "Default file permissions and ownership set, allowing writing to specific directories"
    chmod 700 /var/www/localhost/htdocs/run_openemr.sh
    # Set file and directory permissions
    chmod 600 /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/config/application.config.php
    find /var/www/localhost/htdocs/openemr/sites/default/documents -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/sites/default/edi -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/sites/default/era -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/sites/default/letter_templates -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/compiled -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache -type d -print0 | xargs -0 chmod 700
    find /var/www/localhost/htdocs/openemr/gacl/admin/templates_c -type d -print0 | xargs -0 chmod 700

    chmod -R 777 /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates
    chmod -R 777 /var/www/localhost/htdocs/openemr/sites/

    echo "Removing remaining setup scripts"
    #remove all setup scripts
    rm -f /var/www/localhost/htdocs/openemr/acl_setup.php
    rm -f /var/www/localhost/htdocs/openemr/acl_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/setup.php
    rm -f /var/www/localhost/htdocs/openemr/sql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/ippf_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/gacl/setup.php
    echo "Setup scripts removed, we should be ready to go now!"
fi
# ensure the auto_configure.php script has been removed
rm -f /var/www/localhost/htdocs/auto_configure.php

echo "Starting cron daemon!"
crond
echo "Starting apache!"
/usr/sbin/httpd -D FOREGROUND
