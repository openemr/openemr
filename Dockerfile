FROM openemr/openemr:7.0.3

# Copy the sites.tar.gz file into the container
COPY sites.tar.gz /tmp/

# Extract the sites.tar.gz file directly to the openemr directory
# The -C flag specifies the directory to extract to
# The --strip-components=0 ensures the sites folder structure is preserved correctly
RUN tar -xzf /tmp/sites.tar.gz -C /var/www/localhost/htdocs/openemr/ && \
    rm /tmp/sites.tar.gz

# Set proper permissions for the extracted files
RUN chown -R apache:apache /var/www/localhost/htdocs/openemr/sites