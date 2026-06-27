# syntax=docker/dockerfile:1
# ============================================================================
# OpenEMR Dockerfile for Version 8.1.1
# ============================================================================
# This Dockerfile builds a production-ready OpenEMR container image with:
#   - Apache web server for serving OpenEMR
#   - PHP 8.5 with all required extensions
#   - OpenEMR application code with dependencies installed
#   - Automated setup and configuration scripts
#   - Support for SSL/TLS certificates
#   - Multi-stage build support for coverage testing (kcov target)
#
# Build Targets:
#   - base: Default production image
#   - kcov: Coverage testing image with kcov instrumentation
#   - final: Alias for base (for consistency)
# ============================================================================

# ============================================================================
# BASE IMAGE CONFIGURATION
# ============================================================================
# Alpine Linux version - centralized setting for easy updates
# Note: This is NOT meant to be used as a build argument (unlike the flex series)
ARG ALPINE_VERSION=3.23
FROM alpine:${ALPINE_VERSION} AS base

# PHP version configuration
# Note: This is NOT meant to be used as a build argument (unlike the flex series)
# Important: When updating PHP version, also update php.ini file to match
ARG PHP_VERSION=8.5
ENV PHP_VERSION=${PHP_VERSION}
# Create abbreviated version (e.g., "8.5" -> "85") for package naming
ARG PHP_VERSION_ABBR=${PHP_VERSION//./}
ENV PHP_VERSION_ABBR=${PHP_VERSION_ABBR}

# ============================================================================
# SYSTEM PACKAGE INSTALLATION
# ============================================================================
# Update Alpine packages to latest versions for security patches
RUN apk --no-cache upgrade

# Install system packages required for OpenEMR and Apache
# These packages provide web server, database clients, build tools, and utilities
# Packages: apache2 (HTTP server), apache2-proxy (proxy module), apache2-ssl (SSL/TLS),
# apache2-utils (utilities), bash (shell), certbot (Let's Encrypt), curl (HTTP client),
# dcron (scheduled tasks), git (version control), imagemagick (image processing),
# mariadb-client (database client), mariadb-connector-c (DB connector), ncurses (terminal),
# nodejs/npm (JavaScript runtime), openssl/openssl-dev (cryptography), perl (interpreter),
# rsync (file sync), shadow (user management), su-exec (lightweight privilege-drop
# tool used by run_php_as_apache to invoke OpenEMR CLI scripts as the apache user
# without an intermediate shell — busybox `su` rescans options across the whole
# arg list, which breaks argv-passthrough), tar (archives)
RUN apk add --no-cache \
    apache2 \
    apache2-proxy \
    apache2-ssl \
    apache2-utils \
    bash \
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
    su-exec \
    tar

# Install PHP and all required extensions for OpenEMR
# OpenEMR requires a comprehensive set of PHP extensions for full functionality
# Core: php, php-apache2 (Apache integration)
# Database: php-mysqli, php-pdo, php-pdo_mysql (MySQL support)
# XML/Data: php-dom, php-xml, php-xmlreader, php-xmlwriter, php-xsl, php-simplexml, php-soap
# Graphics: php-gd, php-pecl-imagick (image processing)
# Crypto/Encoding: php-openssl, php-sodium, php-iconv, php-mbstring, php-intl
# Utilities: php-curl, php-json, php-bcmath, php-calendar, php-ctype, php-fileinfo
# Performance: php-opcache, php-pecl-apcu, php-redis, php-session
# Other: php-fpm, php-phar, php-zip, php-zlib, php-ldap, php-sockets, php-tokenizer
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
    php${PHP_VERSION_ABBR}-ldap \
    php${PHP_VERSION_ABBR}-mbstring \
    php${PHP_VERSION_ABBR}-mysqli \
    php${PHP_VERSION_ABBR}-openssl \
    php${PHP_VERSION_ABBR}-pdo \
    php${PHP_VERSION_ABBR}-pdo_mysql \
    php${PHP_VERSION_ABBR}-pecl-apcu \
    php${PHP_VERSION_ABBR}-pecl-imagick \
    php${PHP_VERSION_ABBR}-phar \
    php${PHP_VERSION_ABBR}-pecl-redis \
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

# ============================================================================
# APACHE CONFIGURATION
# ============================================================================
# Fix Apache to listen on all interfaces (0.0.0.0) instead of localhost only
# This is required for Docker containers to accept external connections
RUN sed -i 's/^Listen 80$/Listen 0.0.0.0:80/' /etc/apache2/httpd.conf

# ============================================================================
# USER AND PERMISSIONS CONFIGURATION
# ============================================================================
# Set Apache user UID to 1000 to ensure consistent permissions across
# shared volumes when using multiple containers (OpenEMR, nginx, php-fpm)
# This prevents permission conflicts in multi-container deployments
RUN usermod -u 1000 apache

# ============================================================================
# PHP CONFIGURATION
# ============================================================================
# Create symlink for PHP binary to support PHP 8+ on Alpine 3.13+
# Note: This workaround may be removable in future Alpine versions
# The symlink ensures 'php' command points to the correct versioned binary
RUN ln -sf /usr/bin/php${PHP_VERSION_ABBR} /usr/bin/php

# Install Composer (PHP dependency manager) for OpenEMR package installation
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# ============================================================================
# MULTI-STAGE BUILD: OpenEMR Source and Dependencies
# ============================================================================
# Stage 1: Fetch OpenEMR source code via git clone
# Use git clone instead of GitHub archive because OpenEMR's .gitattributes
# marks tests/, phpunit*.xml, and other dev files as export-ignore, which
# excludes them from archives but not from clones.
# Supports branches or tags via OPENEMR_VERSION build argument.
# Examples:
#   - Branch: --build-arg OPENEMR_VERSION=master
#   - Tag:    --build-arg OPENEMR_VERSION=v7.0.5
ARG OPENEMR_VERSION=master
FROM base AS openemr-source
RUN git clone https://github.com/openemr/openemr.git --branch "${OPENEMR_VERSION}" --depth 1 \
    && rm -rf openemr/.git

# Stage 2: Install PHP dependencies (Composer)
# Separate stage allows Docker to cache this layer independently
FROM base AS openemr-composer
COPY --from=openemr-source /openemr /openemr
WORKDIR /openemr
# Use Docker buildkit cache mount for Composer cache (requires DOCKER_BUILDKIT=1)
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --optimize-autoloader \
    && composer dump-autoload --optimize --apcu

# Stage 3: Build frontend assets (npm)
# Separate stage allows Docker to cache npm dependencies independently
FROM base AS openemr-assets
COPY --from=openemr-source /openemr /openemr
WORKDIR /openemr
RUN apk add --no-cache build-base nodejs npm
# Use Docker buildkit cache mount for npm cache (requires DOCKER_BUILDKIT=1)
RUN --mount=type=cache,target=/root/.npm \
    npm install --unsafe-perm \
    && npm run build \
    && cd ccdaservice \
    && npm install --unsafe-perm \
    && cd .. \
    && rm -fr node_modules \
    && apk del --no-cache build-base

# Stage 4: Final assembly - combine everything in base image
# Combine everything and set permissions
FROM base AS production
# Copy OpenEMR source
COPY --from=openemr-source /openemr /tmp/openemr
# Copy Composer dependencies
COPY --from=openemr-composer /openemr/vendor /tmp/openemr/vendor
COPY --from=openemr-composer /openemr/composer.json /tmp/openemr/composer.json
COPY --from=openemr-composer /openemr/composer.lock /tmp/openemr/composer.lock
# Copy built assets
COPY --from=openemr-assets /openemr/public /tmp/openemr/public
COPY --from=openemr-assets /openemr/ccdaservice /tmp/openemr/ccdaservice

RUN cd /tmp \
    # =========================================================================
    # PRE-SET FILE PERMISSIONS DURING BUILD (major startup optimization)
    # =========================================================================
    # Set secure permissions now so runtime only needs to handle exceptions
    # Directories: 500 (read + execute for owner)
    && find openemr -type d -exec chmod 500 {} + \
    # Files: 400 (read-only for owner)
    && find openemr -type f -exec chmod 400 {} + \
    # Exceptions that need to be writable during setup:
    # - sqlconf.php: Written during auto-configuration
    && chmod 666 openemr/sites/default/sqlconf.php \
    # - sites/default directory: Needs write access for setup
    && chmod 700 openemr/sites/default \
    # - documents directory: Needs write access for uploads
    && find openemr/sites/default/documents -type d -exec chmod 700 {} + \
    && find openemr/sites/default/documents -type f -exec chmod 600 {} + \
    # Set ownership to apache user for proper file access
    && chown -R apache:apache openemr/ \
    # Move OpenEMR to web root directory
    && mv openemr /var/www/localhost/htdocs/ \
    # Create SSL certificate directories
    && mkdir -p /etc/ssl/certs /etc/ssl/private \
    # Disable Apache logging to reduce disk usage (logs handled by Docker)
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *ErrorLog/#ErrorLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/conf.d/ssl.conf \
    && sed -i 's/^ *TransferLog/#TransferLog/' /etc/apache2/conf.d/ssl.conf

# ============================================================================
# WORKING DIRECTORY AND VOLUMES
# ============================================================================
# Set working directory to OpenEMR installation
WORKDIR /var/www/localhost/htdocs/openemr

# Define volumes for SSL certificates and Let's Encrypt certificates
# These volumes persist certificates across container restarts
VOLUME [ "/etc/letsencrypt/", "/etc/ssl" ]

# ============================================================================
# APACHE AND PHP CONFIGURATION FILES
# ============================================================================
# Set Apache log directory environment variable
ENV APACHE_LOG_DIR=/var/log/apache2

# Copy PHP configuration file with OpenEMR-optimized settings
COPY php.ini /etc/php${PHP_VERSION_ABBR}/php.ini

# Copy Apache virtual host configuration for OpenEMR
COPY openemr.conf /etc/apache2/conf.d/

# ============================================================================
# OPENEMR SCRIPTS AND UTILITIES
# ============================================================================
# Copy main startup and configuration scripts
# - openemr.sh: Main container startup script (handles setup, upgrades, Apache)
# - ssl.sh: SSL/TLS certificate management script
# - xdebug.sh: XDebug configuration script (for development)
# - auto_configure.php: Automated OpenEMR installation script
COPY openemr.sh ssl.sh xdebug.sh auto_configure.php /var/www/localhost/htdocs/openemr/

# Copy admin unlock utilities (for password recovery)
COPY utilities/unlock_admin.php utilities/unlock_admin.sh /root/

# Set script permissions:
# - Executable scripts: 500 (read and execute for owner only)
# - PHP scripts: 000 (no access) - prevents accidental execution until enabled
RUN chmod 500 openemr.sh ssl.sh xdebug.sh /root/unlock_admin.sh \
    && chmod 000 auto_configure.php /root/unlock_admin.php

# ============================================================================
# UPGRADE SYSTEM
# ============================================================================
# Copy upgrade scripts and version tracking file
# These scripts handle filesystem upgrades when moving between OpenEMR versions
# The docker-version file tracks the installed version for upgrade detection
COPY upgrade/docker-version \
     upgrade/fsupgrade-1.sh \
     upgrade/fsupgrade-2.sh \
     upgrade/fsupgrade-3.sh \
     upgrade/fsupgrade-4.sh \
     upgrade/fsupgrade-5.sh \
     upgrade/fsupgrade-6.sh \
     upgrade/fsupgrade-7.sh \
     upgrade/fsupgrade-8.sh \
     upgrade/fsupgrade-9.sh \
     upgrade/fsupgrade-10.sh \
     upgrade/fsupgrade-11.sh \
     /root/

# Set upgrade scripts as executable (read and execute for owner only)
RUN chmod 500 \
    /root/fsupgrade-1.sh \
    /root/fsupgrade-2.sh \
    /root/fsupgrade-3.sh \
    /root/fsupgrade-4.sh \
    /root/fsupgrade-5.sh \
    /root/fsupgrade-6.sh \
    /root/fsupgrade-7.sh \
    /root/fsupgrade-8.sh \
    /root/fsupgrade-9.sh \
    /root/fsupgrade-10.sh \
    /root/fsupgrade-11.sh

# ============================================================================
# APACHE RUNTIME DIRECTORY
# ============================================================================
# Create Apache runtime directory to prevent premature process termination
# Apache requires this directory for PID files and shared memory
RUN mkdir -p /run/apache2

# ============================================================================
# DEVELOPMENT TOOLS LIBRARY
# ============================================================================
# Copy shared library of utility functions used by OpenEMR scripts
# This library provides database operations, configuration helpers, etc.
COPY utilities/devtoolsLibrary.source /root/

# ============================================================================
# SWARM MODE SUPPORT
# ============================================================================
# Prepare directories for Docker Swarm/orchestration mode
# These directories contain templates that are restored when containers start
# with empty volumes, enabling multi-container deployments
RUN mkdir /swarm-pieces \
    && rsync --owner --group --perms --delete --recursive --links /etc/ssl /swarm-pieces/ \
    && rsync --owner --group --perms --delete --recursive --links /var/www/localhost/htdocs/openemr/sites /swarm-pieces/

# ============================================================================
# CONTAINER STARTUP
# ============================================================================
# Set default command to run OpenEMR startup script
# This script handles database setup, configuration, and Apache startup
CMD [ "./openemr.sh" ]

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# ============================================================================
# KCOV COVERAGE BUILD TARGET
# ============================================================================
# This target extends the base image with kcov code coverage instrumentation
# Used for generating code coverage reports during testing
# Build with: docker build --target kcov -t openemr:kcov .
FROM base AS kcov

# Install kcov build dependencies
# kcov is a code coverage tool that requires compilation from source
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

# Build and install kcov from source
RUN cd /tmp && \
    git clone https://github.com/SimonKagstrom/kcov && \
    cd kcov && \
    mkdir build && \
    cd build && \
    cmake .. && \
    make && \
    make install

# Copy the complete OpenEMR installation from production stage
# This includes all directories and files needed for code coverage, including
# contrib/util/installScripts that tests may require
COPY --from=production --chown=apache:apache /var/www/localhost/htdocs/openemr /var/www/localhost/htdocs/openemr

# Copy additional files needed by openemr.sh
COPY --from=production /root/devtoolsLibrary.source /root/

# Set Apache log directory environment variable (needed by Apache config)
ENV APACHE_LOG_DIR=/var/log/apache2

# Ensure directories exist before copying config files
RUN mkdir -p /etc/apache2/conf.d "/etc/php${PHP_VERSION_ABBR}" /etc/ssl/certs /etc/ssl/private /var/log/apache2

# Copy configuration files needed by Apache and PHP
COPY --from=production /etc/apache2/conf.d/openemr.conf /etc/apache2/conf.d/
COPY --from=production /etc/php${PHP_VERSION_ABBR}/php.ini /etc/php${PHP_VERSION_ABBR}/php.ini

# Apply Apache configuration changes (CustomLog/ErrorLog disabled, etc.)
# These changes should already be in production stage, but ensure they're applied
RUN sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *ErrorLog/#ErrorLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/conf.d/ssl.conf \
    && sed -i 's/^ *TransferLog/#TransferLog/' /etc/apache2/conf.d/ssl.conf

# Copy upgrade scripts from production stage
COPY --from=production /root/docker-version /root/
COPY --from=production /root/fsupgrade-*.sh /root/
RUN chmod 500 /root/fsupgrade-*.sh

# Copy swarm-pieces directory (for swarm mode support)
COPY --from=production /swarm-pieces /swarm-pieces

# Ensure Apache runtime directory exists
RUN mkdir -p /run/apache2

# Copy kcov wrapper script
COPY kcov-wrapper.sh /var/www/localhost/htdocs/openemr
RUN chmod 500 /var/www/localhost/htdocs/openemr/kcov-wrapper.sh

# Create directory for coverage report output
RUN mkdir -p /var/www/localhost/htdocs/coverage

# Set working directory so relative path in CMD works
WORKDIR /var/www/localhost/htdocs/openemr

# Use kcov wrapper as entrypoint instead of standard OpenEMR startup
CMD [ "./kcov-wrapper.sh" ]

# ============================================================================
# FINAL BUILD TARGET (ALIAS)
# ============================================================================
# This target is an alias for the production target
# Placed last to ensure kcov is not included in default builds
FROM production AS final

# Standard OCI image labels so the published image self-documents what source
# went in. All three values are supplied at build time by the CI workflow
# (docker-build-release.yml), which derives:
#   OPENEMR_VERSION  = the openemr_version_ref from release-targets.yml
#                      (e.g., "master", "v8_1_0", "rel-810")
#   IMAGE_VERSION    = the human-facing openemr release version, composed
#                      from version.php's $v_major/$v_minor/$v_patch/$v_tag/
#                      $v_realpatch at the openemr_version_ref
#                      (e.g., "8.1.1-dev" for master, "8.1.0" for v8_1_0,
#                      "8.1.0.1" for v8_1_0_1)
#   BUILD_DATE       = ISO-8601 date the image was built
ARG IMAGE_VERSION=
ARG BUILD_DATE=unknown
LABEL org.opencontainers.image.title="OpenEMR"
LABEL org.opencontainers.image.source="https://github.com/openemr/openemr"
LABEL org.opencontainers.image.url="https://www.open-emr.org"
LABEL org.opencontainers.image.licenses="GPL-3.0-or-later"
LABEL org.opencontainers.image.revision="${OPENEMR_VERSION}"
LABEL org.opencontainers.image.version="${IMAGE_VERSION}"
LABEL org.opencontainers.image.created="${BUILD_DATE}"
