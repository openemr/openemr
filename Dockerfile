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

# Add a script to check for mounted volumes and file existence at runtime
RUN echo '#!/bin/sh' > /check-files.sh && \
    echo 'echo "=== RUNTIME FILE CHECK ===" >> /tmp/file-check.log' >> /check-files.sh && \
    echo 'ls -la /var/www/localhost/htdocs/openemr/sites/default >> /tmp/file-check.log' >> /check-files.sh && \
    echo 'echo "\nFile exists check:" >> /tmp/file-check.log' >> /check-files.sh && \
    echo 'if [ -f /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php ]; then echo "sqlconf.php exists" >> /tmp/file-check.log; else echo "sqlconf.php MISSING" >> /tmp/file-check.log; fi' >> /check-files.sh && \
    echo 'echo "\nMounted volumes:" >> /tmp/file-check.log' >> /check-files.sh && \
    echo 'mount | grep -i openemr >> /tmp/file-check.log' >> /check-files.sh && \
    echo 'cat /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php >> /tmp/file-check.log' >> /check-files.sh && \
    chmod +x /check-files.sh

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

# Modify the entrypoint to run our check script
COPY --from=busybox:1.36 /bin/sh /bin/busybox_sh
RUN sed -i '1s/^/\/check-files.sh \&\n/' /etc/docker-entrypoint.d/20-openemr-env-init.sh