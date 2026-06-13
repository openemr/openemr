# syntax=docker/dockerfile:1
# ============================================================================
# OpenEMR Flex Dockerfile
# ============================================================================
# This Dockerfile builds a flexible OpenEMR container image designed for
# development and testing. Unlike the versioned containers (e.g., 7.0.5),
# the flex container fetches OpenEMR source code at runtime from a configurable
# git repository, making it suitable for testing different branches, tags, or forks.
#
# Key Features:
#   - Runtime OpenEMR source fetching (configurable via FLEX_REPOSITORY env vars)
#   - Developer tools included (bash, vim, nano, tree, etc.)
#   - Runtime dependency building (composer/npm can run at container startup)
#   - Support for multiple development modes (EASY_DEV_MODE, EASY_DEV_MODE_NEW, etc.)
#   - Apache web server for serving OpenEMR
#   - PHP with all required extensions
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
ARG ALPINE_VERSION=3.23
FROM alpine:${ALPINE_VERSION} AS base

# PHP version configuration
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

# Install developer tools (this is a developer/tester-focused container)
# bash, bash-completion, bash-doc: Enhanced shell experience
# nano, vim: Text editors for development
# tree: Directory visualization
# unzip: Archive extraction
RUN apk add --no-cache \
    bash \
    bash-completion \
    bash-doc \
    nano \
    tree \
    unzip \
    vim

# Install system packages required for OpenEMR and Apache
# These packages provide web server, database clients, build tools, and utilities
# Packages: apache2 (HTTP server), apache2-proxy (proxy module), apache2-ssl (SSL/TLS),
# apache2-utils (utilities), certbot (Let's Encrypt), curl (HTTP client),
# dcron (scheduled tasks), git (version control), imagemagick (image processing),
# jq (JSON processor), mariadb-client (database client), mariadb-connector-c (DB connector),
# ncurses (terminal), nodejs/npm (JavaScript runtime), openssl/openssl-dev (cryptography),
# perl (interpreter), rsync (file sync), shadow (user management), su-exec (lightweight
# privilege-drop tool used by run_php_as_apache to invoke OpenEMR CLI scripts as
# the apache user without an intermediate shell — busybox `su` rescans options
# across the whole arg list, which breaks argv-passthrough), tar (archives)
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
    jq \
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
# Note: opcache was brought into core PHP for PHP 8.5+, so unable to install
#       separate extension for those versions.
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
    "$(if [ "${PHP_VERSION}" = "8.2" ] || [ "${PHP_VERSION}" = "8.3" ] || [ "${PHP_VERSION}" = "8.4" ]; then echo php${PHP_VERSION_ABBR}-json; fi)" \
    php${PHP_VERSION_ABBR}-ldap \
    php${PHP_VERSION_ABBR}-mbstring \
    php${PHP_VERSION_ABBR}-mysqli \
    "$(if [ "${PHP_VERSION}" = "8.2" ] || [ "${PHP_VERSION}" = "8.3" ] || [ "${PHP_VERSION}" = "8.4" ]; then echo php${PHP_VERSION_ABBR}-opcache; fi)" \
    php${PHP_VERSION_ABBR}-openssl \
    php${PHP_VERSION_ABBR}-pdo \
    php${PHP_VERSION_ABBR}-pdo_mysql \
    php${PHP_VERSION_ABBR}-pecl-apcu \
    php${PHP_VERSION_ABBR}-pecl-imagick \
    php${PHP_VERSION_ABBR}-phar \
    "$(if [ "${PHP_VERSION}" = "8.2" ] || [ "${PHP_VERSION}" = "8.3" ] || [ "${PHP_VERSION}" = "8.4" ]; then echo php${PHP_VERSION_ABBR}-redis; else echo php${PHP_VERSION_ABBR}-pecl-redis; fi)" \
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
# BUILD TOOLS AND DIRECTORY SETUP
# ============================================================================
# Note: build-base is kept in the image (not removed after use) because
# npm package libxmljs needs to be built during OpenEMR runtime builds.
# This is part of the ccda npm build process.
# TODO: When this issue is fixed, we can remove build-base installation,
#       which will decrease image size by ~190MB
#
# IMPORTANT: Unlike versioned containers (e.g., 7.0.5), the flex container
# does NOT use multi-stage builds for composer/npm dependencies because:
#   1. OpenEMR source is fetched at runtime (not build time) via git
#   2. Dependencies are built at runtime when the container starts
#   3. Different branches/tags may require different dependencies
#   4. Users may want to mount volumes with pre-built dependencies
#
# The 7.0.5 container uses multi-stage builds because it:
#   - Fetches source at build time (from GitHub archive)
#   - Builds dependencies at build time
#   - Can cache dependency builds using Docker buildkit cache mounts
#
# Flex container optimizations focus on:
#   - Efficient permission handling (batched find operations)
#   - Conditional dependency building (only when needed)
#   - Support for pre-built dependencies via volumes
RUN apk add --no-cache build-base \
    && mkdir -p /var/www/localhost/htdocs/openemr/sites \
    && chown -R apache:apache /var/www/localhost/htdocs/openemr \
    && mkdir -p /etc/ssl/certs /etc/ssl/private \
    # Disable Apache logging to reduce disk usage (logs handled by Docker)
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *ErrorLog/#ErrorLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/conf.d/ssl.conf \
    && sed -i 's/^ *TransferLog/#TransferLog/' /etc/apache2/conf.d/ssl.conf

# ============================================================================
# WORKING DIRECTORY AND VOLUMES
# ============================================================================
# Set working directory to web root (OpenEMR will be cloned here at runtime)
WORKDIR /var/www/localhost/htdocs

# Define volumes for SSL certificates and Let's Encrypt certificates
# These volumes persist certificates across container restarts
VOLUME [ "/etc/letsencrypt/", "/etc/ssl" ]

# ============================================================================
# APACHE AND PHP CONFIGURATION FILES
# ============================================================================
# Set Apache log directory environment variable
ENV APACHE_LOG_DIR=/var/log/apache2

# Copy PHP configuration file with OpenEMR-optimized settings
COPY configs/php${PHP_VERSION}/php.ini /etc/php${PHP_VERSION_ABBR}/php.ini

# Copy Apache virtual host configuration for OpenEMR
COPY openemr.conf /etc/apache2/conf.d/

# ============================================================================
# OPENEMR SCRIPTS AND UTILITIES
# ============================================================================
# Copy main startup and configuration scripts
# - openemr.sh: Main container startup script (handles setup, upgrades, Apache)
# - ssl.sh: SSL/TLS certificate management script
# - xdebug.sh: XDebug configuration script (for development debugging)
# - pcov.sh: PCOV configuration script (for code coverage)
# - auto_configure.php: Automated OpenEMR installation script
COPY openemr.sh ssl.sh xdebug.sh pcov.sh auto_configure.php /var/www/localhost/htdocs/

# Copy admin unlock utilities (for password recovery)
COPY utilities/unlock_admin.php utilities/unlock_admin.sh /root/

# Set script permissions:
# - Executable scripts: 500 (read and execute for owner only)
# - PHP scripts: 000 (no access) - prevents accidental execution until enabled
RUN chmod 500 openemr.sh ssl.sh xdebug.sh pcov.sh /root/unlock_admin.sh \
    && chmod 000 auto_configure.php /root/unlock_admin.php

# ============================================================================
# APACHE RUNTIME DIRECTORY
# ============================================================================
# Create Apache runtime directory to prevent premature process termination
# Apache requires this directory for PID files and shared memory
RUN mkdir -p /run/apache2

# ============================================================================
# DEVELOPMENT TOOLS AND UTILITIES
# ============================================================================
# Copy shared library of utility functions used by OpenEMR scripts
# This library provides database operations, configuration helpers, etc.
COPY utilities/devtools /root/
COPY utilities/devtoolsLibrary.source /root/

# Create directories for development and testing utilities
RUN mkdir -p /snapshots /certs /couchdb/original

# Demo data for testing (SQL dump from OpenEMR 5.0.0).
# Fetched at build time from the openemr-devops repo (pinned to a specific
# commit SHA so the URL stays valid even after later cleanup of the source
# path). Checksum verified to detect any mid-flight corruption or URL drift.
ARG DEMO_SQL_REPO_SHA=441d7b3db5b8033822e0e3da462e7553a2330477
ARG DEMO_SQL_SHA256=5d418c838446f3bdd4aa17d1276578106928a3ebcb27b40f4ab421694cc013d7
RUN curl -fsSL -o /root/demo_5_0_0_5.sql \
    "https://raw.githubusercontent.com/openemr/openemr-devops/${DEMO_SQL_REPO_SHA}/docker/openemr/flex/utilities/demo_5_0_0_5.sql" \
    && echo "${DEMO_SQL_SHA256}  /root/demo_5_0_0_5.sql" | sha256sum -c -

# Set devtools script as executable
RUN chmod 500 /root/devtools

# ============================================================================
# SWARM MODE SUPPORT
# ============================================================================
# Prepare directories for Docker Swarm/orchestration mode
# These directories contain templates that are restored when containers start
# with empty volumes, enabling multi-container deployments
RUN mkdir /swarm-pieces \
    && rsync --owner --group --perms --delete --recursive --links /etc/ssl /swarm-pieces/ \
    && mkdir -p /swarm-pieces/sites/default \
    && chown -R apache:apache /swarm-pieces/sites

# ============================================================================
# CONTAINER STARTUP
# ============================================================================
# Set default command to run OpenEMR startup script
# This script handles source fetching, database setup, configuration, and Apache startup
CMD [ "./openemr.sh" ]

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# ============================================================================
# KCOV COVERAGE BUILD TARGET
# ============================================================================
# This target extends the base image with kcov code coverage instrumentation
# Used for generating code coverage reports during testing
# Build with: docker build --target kcov -t openemr-flex:kcov .
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

# Copy kcov wrapper script
COPY kcov-wrapper.sh /var/www/localhost/htdocs/
RUN chmod 500 /var/www/localhost/htdocs/kcov-wrapper.sh

# Create directory for coverage report output
RUN mkdir -p /var/www/localhost/htdocs/coverage

# Set working directory so relative path in CMD works
WORKDIR /var/www/localhost/htdocs

# Use kcov wrapper as entrypoint instead of standard OpenEMR startup
CMD [ "./kcov-wrapper.sh" ]

# ============================================================================
# FINAL BUILD TARGET (ALIAS)
# ============================================================================
# This target is an alias for the base target
# Placed last to ensure kcov is not included in default builds
FROM base AS final
