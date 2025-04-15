#!/bin/sh
set -e

# Ensure sites directory exists
if [ ! -d /var/www/localhost/htdocs/openemr/sites/default ]; then
    # If mounted volume is empty, copy default sites content
    if [ "$(ls -A /var/www/localhost/htdocs/openemr/sites 2>/dev/null)" = "" ]; then
        echo "Initializing sites directory with default content..."
        cp -r /var/www/localhost/htdocs/openemr/sites.default/* /var/www/localhost/htdocs/openemr/sites/
    fi
fi

# Set proper permissions
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites
chmod -R 755 /var/www/localhost/htdocs/openemr/sites

# Execute original entrypoint if it exists
if [ -f /var/www/localhost/htdocs/openemr/docker-entrypoint.sh ]; then
    sh /var/www/localhost/htdocs/openemr/docker-entrypoint.sh
fi

# Execute the provided command
exec "$@"