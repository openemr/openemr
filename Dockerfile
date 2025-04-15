FROM openemr/openemr:7.0.3

# Copy the sites.tar.gz file into the container
COPY sites.tar.gz /tmp/

# Extract the sites.tar.gz file directly to the openemr directory
RUN tar -xzf /tmp/sites.tar.gz -C /var/www/localhost/htdocs/openemr/ && \
    rm /tmp/sites.tar.gz

# Set proper permissions for the extracted files
RUN chown -R apache:apache /var/www/localhost/htdocs/openemr/sites && \
    chmod -R 755 /var/www/localhost/htdocs/openemr/sites && \
    chmod 666 /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php

# Create a script to check for file existence at runtime
RUN echo '#!/bin/sh' > /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'echo "=== RUNTIME FILE CHECK ===" > /tmp/file-check.log' >> /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'ls -la /var/www/localhost/htdocs/openemr/sites/default >> /tmp/file-check.log' >> /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'echo "\nFile exists check:" >> /tmp/file-check.log' >> /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'if [ -f /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php ]; then echo "sqlconf.php exists" >> /tmp/file-check.log; else echo "sqlconf.php MISSING" >> /tmp/file-check.log; fi' >> /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'echo "\nMounted volumes:" >> /tmp/file-check.log' >> /docker-entrypoint-initdb.d/check-files.sh && \
    echo 'mount | grep -i railwayapp >> /tmp/file-check.log' >> /docker-entrypoint-initdb.d/check-files.sh && \
    chmod +x /docker-entrypoint-initdb.d/check-files.sh

# Make a backup copy of the sites directory in another location
RUN cp -r /var/www/localhost/htdocs/openemr/sites /sites-backup

# Print out for build logs
RUN echo "Directory structure:" && \
    ls -la /var/www/localhost/htdocs/openemr/sites && \
    echo "\nDetailed recursive listing with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type d -exec ls -ld {} \; && \
    echo "\nAll files with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type f -exec ls -la {} \; && \
    echo "\nFile content of sqlconf.php:" && \
    cat /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php && \
    echo "\nFile count by type:" && \
    find /var/www/localhost/htdocs/openemr/sites -type f | grep -o "\.[^.]*$" | sort | uniq -c

# Create an entrypoint script to restore sites directory if it gets overwritten by a volume mount
RUN mkdir -p /docker-entrypoint-initdb.d && \
    echo '#!/bin/sh' > /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo 'if [ ! -f /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php ]; then' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo '  echo "Restoring sites directory from backup..." >> /tmp/file-check.log' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo '  cp -r /sites-backup/* /var/www/localhost/htdocs/openemr/sites/' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo '  chown -R apache:apache /var/www/localhost/htdocs/openemr/sites' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo '  chmod -R 755 /var/www/localhost/htdocs/openemr/sites' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo '  chmod 666 /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    echo 'fi' >> /docker-entrypoint-initdb.d/restore-sites.sh && \
    chmod +x /docker-entrypoint-initdb.d/restore-sites.sh