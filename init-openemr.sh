#!/bin/sh
set -e

# Make sure directories exist
mkdir -p /var/www/localhost/htdocs/openemr/sites/default
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/documents
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/edi
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/era
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/letter_templates

# Create SSL directories and self-signed certificate
mkdir -p /etc/ssl/certs
mkdir -p /etc/ssl/private

# Generate a self-signed certificate for initial setup
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/webserver.key.pem \
  -out /etc/ssl/certs/webserver.cert.pem \
  -subj "/CN=localhost"

# Fix permissions for OpenEMR setup
chmod 666 /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php
chmod -R 777 /var/www/localhost/htdocs/openemr/sites/default/documents
chmod -R 777 /var/www/localhost/htdocs/openemr/sites/default/edi
chmod -R 777 /var/www/localhost/htdocs/openemr/sites/default/era
chmod -R 777 /var/www/localhost/htdocs/openemr/sites/default/letter_templates

# Make sure the webserver can write to these files
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites
chmod 666 /var/www/localhost/htdocs/openemr/library/sqlconf.php
chmod 666 /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/config/application.config.php

# Create run directory for Apache
mkdir -p /run/apache2

# Skip the upgrade check by setting the environment variable
export SKIP_UPGRADE_CHECK=1

echo "Starting OpenEMR - access the setup page to complete configuration"
# Start Apache
exec /usr/sbin/httpd -D FOREGROUND