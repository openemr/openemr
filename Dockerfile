FROM openemr/openemr:7.0.3

# Copy the sites.tar.gz file into the container
COPY sites.tar.gz /tmp/

# Extract the sites.tar.gz file directly to the openemr directory
RUN tar -xzf /tmp/sites.tar.gz -C /var/www/localhost/htdocs/openemr/ && \
    rm /tmp/sites.tar.gz

# Set proper permissions for the extracted files - make them very permissive
RUN chown -R apache:apache /var/www/localhost/htdocs/openemr/sites && \
    chmod -R 777 /var/www/localhost/htdocs/openemr/sites

# Create a backup of the sites directory to /tmp in case it gets mounted over
RUN cp -r /var/www/localhost/htdocs/openemr/sites /tmp/sites-backup

# Add a simple script that can be executed manually if needed
RUN echo '#!/bin/sh' > /restore-sites.sh && \
    echo 'if [ ! -f /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php ]; then' >> /restore-sites.sh && \
    echo '  echo "Restoring sites directory from backup..."' >> /restore-sites.sh && \
    echo '  cp -r /tmp/sites-backup/* /var/www/localhost/htdocs/openemr/sites/' >> /restore-sites.sh && \
    echo '  chown -R apache:apache /var/www/localhost/htdocs/openemr/sites' >> /restore-sites.sh && \
    echo '  chmod -R 777 /var/www/localhost/htdocs/openemr/sites' >> /restore-sites.sh && \
    echo 'fi' >> /restore-sites.sh && \
    chmod +x /restore-sites.sh

# Print out for build logs
RUN echo "Directory structure:" && \
    ls -la /var/www/localhost/htdocs/openemr/sites && \
    echo "\nDetailed recursive listing with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type d -exec ls -ld {} \; && \
    echo "\nAll files with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type f -exec ls -la {} \; && \
    echo "\nFile content of sqlconf.php:" && \
    cat /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php