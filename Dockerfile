FROM openemr/openemr:7.0.3

# Add your custom files or modifications here
# For example:
# COPY ./custom-files/ /var/www/localhost/htdocs/openemr/

# If you need to install additional PHP extensions:
# RUN apk add --no-cache php8-some-extension

# Apply any custom configurations
# COPY ./custom-configs/ /etc/