#!/bin/sh

# Check if we've already done this to avoid running multiple times
if [ ! -f /var/www/localhost/htdocs/openemr/sites-backup/.copied ]; then
    echo "Copying sites backup content..."
    cp -rf /var/www/localhost/htdocs/openemr/sites-backup/* /var/www/localhost/htdocs/openemr/sites/
    
    echo "Fixing permissions..."
    chown -R apache:apache /var/www/localhost/htdocs/openemr/sites/
    
    # Create a flag file to indicate we've done the copy
    touch /var/www/localhost/htdocs/openemr/sites-backup/.copied
    
    echo "Sites backup copy completed"
fi