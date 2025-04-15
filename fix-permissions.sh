#!/bin/sh

# Log start of script execution
echo "Running permissions fix script for sites directory..."

# Fix permissions on sites directory to be more permissive while still secure
chmod -R 755 /var/www/localhost/htdocs/openemr/sites/
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites/

# Create a marker file so we don't run this multiple times
touch /var/www/localhost/htdocs/openemr/sites/.permissions-fixed

echo "Permissions fixed successfully."