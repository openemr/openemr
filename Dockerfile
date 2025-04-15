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
    libtiff-tools \
    nano \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli soap zip xml curl ldap calendar intl

RUN { \
    echo 'short_open_tag = Off'; \
    echo 'display_errors = Off'; \
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

# Copy OpenEMR files
COPY . /var/www/html/

# Set permissions
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

RUN { \
    echo '<Directory "/var/www/html">'; \
    echo '    AllowOverride FileInfo'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/sites">'; \
    echo '    AllowOverride None'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/sites/*/documents">'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
} > /etc/apache2/conf-available/openemr.conf && \
    a2enconf openemr

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]