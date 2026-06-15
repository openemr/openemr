# syntax=docker/dockerfile:1

# Below allows easy centralized setting of alpine version
#  (note NOT meant to use this has an actually argument in building docker like the flex series does)
ARG ALPINE_VERSION=3.22
FROM alpine:${ALPINE_VERSION} AS base

# Below allows easy centralized setting of PHP version
#  (note NOT meant to use this has an actually argument in building docker like the flex series does)
#  (note when update the PHP versiom, will also need build a updated php.ini file to match the new version)
ARG PHP_VERSION=8.4
ENV PHP_VERSION=${PHP_VERSION}
ARG PHP_VERSION_ABBR=${PHP_VERSION//./}
ENV PHP_VERSION_ABBR=${PHP_VERSION_ABBR}

#Prepare to install dependencies
RUN apk --no-cache upgrade

# Install non-php packages
RUN apk add --no-cache \
    apache2 \
    apache2-proxy \
    apache2-ssl \
    apache2-utils \
    certbot \
    curl \
    dcron \
    git \
    imagemagick \
    mariadb-client \
    mariadb-connector-c \
    ncurses \
    nodejs \
    npm \
    openssl \
    openssl-dev \
    perl \
    rsync \
    shadow \
    tar

# Install PHP and its extensions
RUN apk add --no-cache \
    php${PHP_VERSION_ABBR} \
    php${PHP_VERSION_ABBR}-apache2 \
    php${PHP_VERSION_ABBR}-bcmath \
    php${PHP_VERSION_ABBR}-calendar \
    php${PHP_VERSION_ABBR}-ctype \
    php${PHP_VERSION_ABBR}-curl \
    php${PHP_VERSION_ABBR}-dom \
    php${PHP_VERSION_ABBR}-fileinfo \
    php${PHP_VERSION_ABBR}-fpm \
    php${PHP_VERSION_ABBR}-gd \
    php${PHP_VERSION_ABBR}-iconv \
    php${PHP_VERSION_ABBR}-intl \
    php${PHP_VERSION_ABBR}-json \
    php${PHP_VERSION_ABBR}-ldap \
    php${PHP_VERSION_ABBR}-mbstring \
    php${PHP_VERSION_ABBR}-mysqli \
    php${PHP_VERSION_ABBR}-opcache \
    php${PHP_VERSION_ABBR}-openssl \
    php${PHP_VERSION_ABBR}-pdo \
    php${PHP_VERSION_ABBR}-pdo_mysql \
    php${PHP_VERSION_ABBR}-pecl-apcu \
    php${PHP_VERSION_ABBR}-pecl-imagick \
    php${PHP_VERSION_ABBR}-phar \
    php${PHP_VERSION_ABBR}-redis \
    php${PHP_VERSION_ABBR}-session \
    php${PHP_VERSION_ABBR}-simplexml \
    php${PHP_VERSION_ABBR}-soap \
    php${PHP_VERSION_ABBR}-sockets \
    php${PHP_VERSION_ABBR}-sodium \
    php${PHP_VERSION_ABBR}-tokenizer \
    php${PHP_VERSION_ABBR}-xml \
    php${PHP_VERSION_ABBR}-xmlreader \
    php${PHP_VERSION_ABBR}-xmlwriter \
    php${PHP_VERSION_ABBR}-xsl \
    php${PHP_VERSION_ABBR}-zip \
    php${PHP_VERSION_ABBR}-zlib

# fix issue in apache
RUN sed -i 's/^Listen 80$/Listen 0.0.0.0:80/' /etc/apache2/httpd.conf

# Needed to ensure permissions work across shared volumes with openemr, nginx, and php-fpm dockers
RUN usermod -u 1000 apache

#BELOW LINE NEEDED TO SUPPORT PHP8 ON ALPINE 3.13+; SHOULD BE ABLE TO REMOVE THIS IN FUTURE ALPINE VERSIONS
RUN ln -sf /usr/bin/php${PHP_VERSION_ABBR} /usr/bin/php
# Install composer for openemr package building
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apk add --no-cache build-base \
    && git clone https://github.com/openemr/openemr.git --branch v7_0_4 --depth 1 \
    && rm -rf openemr/.git \
    && cd openemr \
    && composer install --no-dev \
    && npm install --unsafe-perm \
    && npm run build \
    && cd ccdaservice \
    && npm install --unsafe-perm \
    && cd ../ \
    && composer global require phing/phing \
    && /root/.composer/vendor/bin/phing vendor-clean \
    && /root/.composer/vendor/bin/phing assets-clean \
    && composer global remove phing/phing \
    && composer dump-autoload --optimize --apcu \
    && composer clearcache \
    && npm cache clear --force \
    && rm -fr node_modules \
    && cd ../ \
    && chmod 666 openemr/sites/default/sqlconf.php \
    && chown -R apache openemr/ \
    && mv openemr /var/www/localhost/htdocs/ \
    && mkdir -p /etc/ssl/certs /etc/ssl/private \
    && apk del --no-cache build-base \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *ErrorLog/#ErrorLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/conf.d/ssl.conf \
    && sed -i 's/^ *TransferLog/#TransferLog/' /etc/apache2/conf.d/ssl.conf
WORKDIR /var/www/localhost/htdocs/openemr
VOLUME [ "/etc/letsencrypt/", "/etc/ssl" ]
#configure apache & php properly
ENV APACHE_LOG_DIR=/var/log/apache2
COPY php.ini /etc/php${PHP_VERSION_ABBR}/php.ini
COPY openemr.conf /etc/apache2/conf.d/
#add runner and auto_configure and prevent auto_configure from being run w/o being enabled
COPY openemr.sh ssl.sh xdebug.sh auto_configure.php /var/www/localhost/htdocs/openemr/
COPY utilities/unlock_admin.php utilities/unlock_admin.sh /root/
RUN chmod 500 openemr.sh ssl.sh xdebug.sh /root/unlock_admin.sh \
    && chmod 000 auto_configure.php /root/unlock_admin.php
#bring in pieces used for automatic upgrade process
COPY upgrade/docker-version \
     upgrade/fsupgrade-1.sh \
     upgrade/fsupgrade-2.sh \
     upgrade/fsupgrade-3.sh \
     upgrade/fsupgrade-4.sh \
     upgrade/fsupgrade-5.sh \
     upgrade/fsupgrade-6.sh \
     upgrade/fsupgrade-7.sh \
     upgrade/fsupgrade-8.sh \
     /root/
RUN chmod 500 \
    /root/fsupgrade-1.sh \
    /root/fsupgrade-2.sh \
    /root/fsupgrade-3.sh \
    /root/fsupgrade-4.sh \
    /root/fsupgrade-5.sh \
    /root/fsupgrade-6.sh \
    /root/fsupgrade-7.sh \
    /root/fsupgrade-8.sh
#fix issue with apache2 dying prematurely
RUN mkdir -p /run/apache2
#Copy dev tools library to root
COPY utilities/devtoolsLibrary.source /root/
#Ensure swarm/orchestration pieces are available if needed
RUN mkdir /swarm-pieces \
    && rsync --owner --group --perms --delete --recursive --links /etc/ssl /swarm-pieces/ \
    && rsync --owner --group --perms --delete --recursive --links /var/www/localhost/htdocs/openemr/sites /swarm-pieces/
#go
CMD [ "./openemr.sh" ]

EXPOSE 80 443


# kcov coverage build target
FROM base AS kcov

# Install kcov dependencies
RUN apk add --no-cache bash \
                       build-base \
                       cmake \
                       binutils-dev \
                       curl-dev \
                       elfutils \
                       elfutils-dev \
                       g++ \
                       libcurl \
                       libdwarf-dev \
                       libelf-static \
                       pkgconfig \
                       python3

# Install kcov from source
RUN cd /tmp && \
    git clone https://github.com/SimonKagstrom/kcov && \
    cd kcov && \
    mkdir build && \
    cd build && \
    cmake .. && \
    make && \
    make install

# Create kcov wrapper script
COPY kcov-wrapper.sh /var/www/localhost/htdocs/openemr
RUN chmod 500 /var/www/localhost/htdocs/openemr/kcov-wrapper.sh

# Create directory for coverage reports
RUN mkdir -p /var/www/localhost/htdocs/coverage

# Use kcov wrapper as entrypoint
CMD [ "./kcov-wrapper.sh" ]


# Put this last because we don't want kcov in the default.
FROM base AS final
