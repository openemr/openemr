FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    mariadb-client \
    libxml2-dev \
    libcurl4-openssl-dev \
    curl \
    git \
    nodejs \
    npm \
    imagemagick \
    rsync \
    libldap2-dev \
    libgd-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli soap zip xml curl ldap calendar intl

# Configure PHP
RUN { \
    echo 'short_open_tag = Off'; \
    echo 'display_errors = On'; \
    echo 'log_errors = On'; \
    echo 'error_log = /dev/stderr'; \
    echo 'register_globals = Off'; \
    echo 'max_input_vars = 3000'; \
    echo 'max_execution_time = 60'; \
    echo 'max_input_time = -1'; \
    echo 'post_max_size = 30M'; \
    echo 'memory_limit = 256M'; \
    echo 'mysqli.allow_local_infile = On'; \
    echo 'file_uploads = On'; \
    echo 'upload_max_filesize = 30M'; \
    echo 'upload_tmp_dir = /tmp'; \
} > /usr/local/etc/php/conf.d/openemr-php.ini

# Copy the OpenEMR files (from your forked repo)
COPY . /var/www/html/

# Set permissions (very important for OpenEMR setup)
RUN chown -R www-data:www-data /var/www/html/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Build OpenEMR
RUN composer install --no-dev
RUN npm install
RUN npm run build
RUN composer dump-autoload -o

# Configure Apache
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN { \
    echo '<Directory "/var/www/html">'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/sites">'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/sites/*/documents">'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
} > /etc/apache2/conf-available/openemr.conf && \
    a2enconf openemr

# Create a dedicated health check endpoint
RUN echo "<?php\nheader('HTTP/1.1 200 OK');\necho 'OK';\n?>" > /var/www/html/health-check.php && \
    chown www-data:www-data /var/www/html/health-check.php

# Create startup script
RUN echo '#!/bin/bash\n\
# Wait for database to be ready\n\
echo "Waiting for database connection..."\n\
timeout=60\n\
counter=0\n\
while ! mysqladmin ping -h "${MYSQL_HOST}" --user="${MYSQL_USER}" --password="${MYSQL_PASSWORD}" --silent 2>/dev/null && [ $counter -lt $timeout ]; do\n\
    sleep 1\n\
    counter=$((counter+1))\n\
    echo "Waiting for database connection... ($counter/$timeout)"\n\
done\n\
\n\
if [ $counter -eq $timeout ]; then\n\
    echo "Failed to connect to database within timeout period"\n\
    exit 1\n\
fi\n\
\n\
echo "Database connection established"\n\
\n\
# Ensure sites directory exists and has proper permissions\n\
mkdir -p /var/www/html/sites/default\n\
mkdir -p /var/www/html/sites/default/documents\n\
mkdir -p /var/www/html/sites/default/edi\n\
mkdir -p /var/www/html/sites/default/era\n\
mkdir -p /var/www/html/sites/default/documents/smarty/main\n\
mkdir -p /var/www/html/sites/default/documents/smarty/gacl\n\
mkdir -p /var/www/html/sites/default/documents/smarty/compile\n\
\n\
# Check if this appears to be a fresh container with a mounted persistent volume\n\
if [ -f /var/www/html/sites/default/config.php ] && [ ! -d /var/www/html/sites/default/documents/smarty/main ]; then\n\
    echo "Detected a persistent volume with config but missing directories - fixing structure..."\n\
    # Recreate potentially missing directories in a persistent volume\n\
    mkdir -p /var/www/html/sites/default/documents/smarty/main\n\
    mkdir -p /var/www/html/sites/default/documents/smarty/gacl\n\
    mkdir -p /var/www/html/sites/default/documents/smarty/compile\n\
fi\n\
\n\
# Set proper permissions for Smarty and other directories\n\
chmod -R 777 /var/www/html/sites/default/documents/smarty\n\
chmod -R 777 /var/www/html/sites/default/documents\n\
chmod -R 777 /var/www/html/sites/default/edi\n\
chmod -R 777 /var/www/html/sites/default/era\n\
\n\
# Set permissions for OpenEMR\n\
if [ -f /var/www/html/library/acl.inc ]; then\n\
    chmod 666 /var/www/html/library/acl.inc\n\
fi\n\
\n\
if [ -f /var/www/html/interface/modules/zend_modules/config/application.config.php ]; then\n\
    chmod 666 /var/www/html/interface/modules/zend_modules/config/application.config.php\n\
fi\n\
\n\
# Set ownership for all files\n\
chown -R www-data:www-data /var/www/html/sites\n\
\n\
# Create required OpenEMR directories for file uploads and patient documents\n\
mkdir -p /var/www/html/sites/default/documents/categories\n\
mkdir -p /var/www/html/sites/default/documents/encounters\n\
mkdir -p /var/www/html/sites/default/documents/patient_id\n\
mkdir -p /var/www/html/sites/default/documents/procedures\n\
chmod -R 777 /var/www/html/sites/default/documents/categories\n\
chmod -R 777 /var/www/html/sites/default/documents/encounters\n\
chmod -R 777 /var/www/html/sites/default/documents/patient_id\n\
chmod -R 777 /var/www/html/sites/default/documents/procedures\n\
\n\
echo "OpenEMR is ready for setup. Visit the site to complete configuration."\n\
echo "Directory structure and permissions have been prepared."\n\
exec apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

VOLUME ["/var/www/html/sites"]

# Expose port 80
EXPOSE 80

# Start using the custom startup script
CMD ["/usr/local/bin/start.sh"]