FROM openemr/openemr:7.0.3

# Copy the sites.tar.gz file into the container
COPY sites.tar.gz /tmp/

# Extract the sites.tar.gz file directly to the openemr directory
# The -C flag specifies the directory to extract to
RUN tar -xzf /tmp/sites.tar.gz -C /var/www/localhost/htdocs/openemr/ && \
    rm /tmp/sites.tar.gz

# Set proper permissions for the extracted files
RUN chown -R apache:apache /var/www/localhost/htdocs/openemr/sites

# Print out the directory structure and permissions
RUN echo "Directory structure:" && \
    ls -la /var/www/localhost/htdocs/openemr/sites && \
    echo "\nDetailed recursive listing with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type d -exec ls -ld {} \; && \
    echo "\nAll files with permissions:" && \
    find /var/www/localhost/htdocs/openemr/sites -type f -exec ls -la {} \; && \
    echo "\nFile count by type:" && \
    find /var/www/localhost/htdocs/openemr/sites -type f | grep -o "\.[^.]*$" | sort | uniq -c