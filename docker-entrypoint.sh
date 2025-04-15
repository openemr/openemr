#!/bin/sh
set -e

# Ensure OpenEMR has proper permissions, including sites directory
echo "Setting proper permissions for OpenEMR..."
chown -R apache:apache /var/www/localhost/htdocs/openemr
chmod -R 755 /var/www/localhost/htdocs/openemr

# Make specific directories world-writable as required by OpenEMR
chmod 777 /var/www/localhost/htdocs/openemr/sites
find /var/www/localhost/htdocs/openemr/sites -type d -exec chmod 777 {} \;
find /var/www/localhost/htdocs/openemr/sites -type f -exec chmod 666 {} \;

# These specific directories need to be world-writable
echo "Setting specific directories to be world-writable..."
chmod 777 /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/compiled
chmod 777 /var/www/localhost/htdocs/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache
chmod 777 /var/www/localhost/htdocs/openemr/gacl/admin/templates_c
chmod 777 /var/www/localhost/htdocs/openemr/library/freeb
chmod 777 /var/www/localhost/htdocs/openemr/documents
chmod 777 /var/www/localhost/htdocs/openemr/era
chmod 777 /var/www/localhost/htdocs/openemr/library/pluginsystem/plugins

# Execute original entrypoint if it exists, but modify to avoid overriding our permissions
if [ -f /var/www/localhost/htdocs/openemr/docker-entrypoint.sh ]; then
    # Run the original entrypoint but ensure it doesn't mess with our permissions
    export SKIP_PERMISSIONS=true
    sh /var/www/localhost/htdocs/openemr/docker-entrypoint.sh
fi

# Execute the provided command
exec "$@"