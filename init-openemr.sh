#!/bin/sh
set -e

# Make sure directories exist
mkdir -p /var/www/localhost/htdocs/openemr/sites/default
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/documents
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/edi
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/era
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/letter_templates

# Set proper permissions
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites

# Create run directory for Apache
mkdir -p /run/apache2

# Create SSL directories and self-signed certificate
mkdir -p /etc/ssl/certs
mkdir -p /etc/ssl/private

# Generate a self-signed certificate for initial setup
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/webserver.key.pem \
  -out /etc/ssl/certs/webserver.cert.pem \
  -subj "/CN=localhost"

# Skip the upgrade check by setting the environment variable
export SKIP_UPGRADE_CHECK=1

echo "Starting OpenEMR - access the setup page to complete configuration"
# Start Apache
exec /usr/sbin/httpd -D FOREGROUND