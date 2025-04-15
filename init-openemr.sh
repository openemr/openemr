#!/bin/sh
set -e

# Create the default directory structure if it doesn't exist already
if [ ! -d /var/www/localhost/htdocs/openemr/sites/default ]; then
    echo "Initializing OpenEMR sites directory structure..."
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/documents
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/edi
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/era
    mkdir -p /var/www/localhost/htdocs/openemr/sites/default/letter_templates
    
    # Set proper permissions
    chown -R apache:apache /var/www/localhost/htdocs/openemr/sites
fi

# Skip upgrade check by setting a flag
echo "Skipping upgrade check..."
touch /var/www/localhost/htdocs/openemr/sites/default/no_upgrade_check

# Start original entrypoint - use the default command from the openemr image
exec /usr/local/bin/docker-entrypoint.sh apache2-foreground