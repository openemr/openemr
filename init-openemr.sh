#!/bin/sh
set -e

# Create the sites directory structure if it doesn't exist yet
if [ ! -d /var/www/localhost/htdocs/openemr/sites/default ]; then
    echo "Creating initial directory structure..."
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/documents
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/edi
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/era
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/letter_templates
fi

# Make sure core OpenEMR files have the right permissions
chown -R apache:apache /var/www/localhost/htdocs/openemr

# Create run directory for Apache
mkdir -p /run/apache2

# Tell OpenEMR to skip the upgrade check that's causing the crashes
export SKIP_UPGRADE_CHECK=1

# Start Apache
echo "Starting OpenEMR - access the setup page to complete configuration"
exec /usr/sbin/httpd -D FOREGROUND