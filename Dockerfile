FROM openemr/openemr:7.0.3

# Create sites directory if it doesn't exist and set permissions
RUN mkdir -p /var/www/localhost/htdocs/openemr/sites/default && \
    chown -R apache:apache /var/www/localhost/htdocs/openemr/sites

# Add your custom files or modifications here
# COPY ./custom-files/ /var/www/localhost/htdocs/openemr/

# If you need to install additional PHP extensions:
# RUN apk add --no-cache php8-some-extension