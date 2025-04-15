#!/bin/sh
set -e

# Copy the backup sites content to the sites directory
echo "Copying sites backup content..."
cp -rf /var/www/localhost/htdocs/openemr/sites-backup/* /var/www/localhost/htdocs/openemr/sites/

# Fix permissions
echo "Fixing permissions..."
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites/

# Execute the original entrypoint to start OpenEMR
echo "Starting OpenEMR..."
exec "$@"