# syntax=docker/dockerfile:1
# ============================================================================
# OpenEMR Dockerfile for Binary Static Build
# ============================================================================
# This Dockerfile builds a production-ready OpenEMR container image using:
#   - Apache web server for serving OpenEMR
#   - Static PHP binaries (php-fpm, php-cli) from openemr-static-binary-forge
#   - OpenEMR application code from PHAR/binary package
#   - Automated setup and configuration scripts
#   - Support for SSL/TLS certificates
#
# Build Targets:
#   - base: Default production image
#   - final: Alias for base (for consistency)
# ============================================================================

# ============================================================================
# BASE IMAGE CONFIGURATION
# ============================================================================
# Alpine Linux version - centralized setting for easy updates
ARG ALPINE_VERSION=3.22
FROM alpine:${ALPINE_VERSION} AS base

# OpenEMR version and binary release information
ARG OPENEMR_VERSION=7_0_4
ARG BINARY_RELEASE_DATE=12292025
ARG PHP_VERSION=8.5
ENV OPENEMR_VERSION=${OPENEMR_VERSION}
ENV PHP_VERSION=${PHP_VERSION}

# Architecture detection - Docker automatically provides TARGETARCH
# TARGETARCH values: amd64, arm64
# Binary release naming uses: amd64 (not x86_64), arm64
ARG TARGETARCH

# ============================================================================
# SYSTEM PACKAGE INSTALLATION
# ============================================================================
# Update Alpine packages to latest versions for security patches
RUN apk --no-cache upgrade

# Install system packages required for OpenEMR and Apache
# Note: PHP packages are NOT installed - we use static binaries instead
# Packages: apache2 (HTTP server), apache2-proxy (proxy module), apache2-ssl (SSL/TLS),
# apache2-utils (utilities), bash (shell), certbot (Let's Encrypt), curl (HTTP client),
# dcron (scheduled tasks), imagemagick (image processing),
# mariadb-client (database client), mariadb-connector-c (DB connector), ncurses (terminal),
# nodejs/npm (JavaScript runtime for CQM service), openssl/openssl-dev (cryptography), perl (interpreter),
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
# STATIC PHP BINARIES INSTALLATION
# ============================================================================
# Download and install static PHP binaries from openemr-static-binary-forge
# These binaries are statically compiled and include all necessary extensions
# We need php-fpm (for FastCGI) and php-cli (for command-line scripts like auto_configure.php)
# Architecture naming: amd64 and arm64 are used directly in release URLs and filenames
RUN ARCH_SUFFIX="${TARGETARCH}" \
    && cd /tmp \
    && curl -fSL "https://github.com/Jmevorach/openemr-static-binary-forge/releases/download/linux_${ARCH_SUFFIX}-php85-openemr-v${OPENEMR_VERSION}-${ARCH_SUFFIX}-${BINARY_RELEASE_DATE}/php-fpm-v${OPENEMR_VERSION}-linux-${ARCH_SUFFIX}" -o php-fpm \
    && test -s php-fpm || (echo "ERROR: php-fpm download failed - file is empty or missing" && exit 1) \
    && test "$(stat -f%z php-fpm 2>/dev/null || stat -c%s php-fpm 2>/dev/null || echo 0)" -gt 1000000 || (echo "ERROR: php-fpm download appears invalid (file too small)" && exit 1) \
    && curl -fSL "https://github.com/Jmevorach/openemr-static-binary-forge/releases/download/linux_${ARCH_SUFFIX}-php85-openemr-v${OPENEMR_VERSION}-${ARCH_SUFFIX}-${BINARY_RELEASE_DATE}/php-cli-v${OPENEMR_VERSION}-linux-${ARCH_SUFFIX}" -o php-cli \
    && test -s php-cli || (echo "ERROR: php-cli download failed - file is empty or missing" && exit 1) \
    && test "$(stat -f%z php-cli 2>/dev/null || stat -c%s php-cli 2>/dev/null || echo 0)" -gt 1000000 || (echo "ERROR: php-cli download appears invalid (file too small)" && exit 1) \
    && chmod +x php-fpm php-cli \
    && mv php-fpm /usr/local/bin/php-fpm \
    && mv php-cli /usr/local/bin/php \
    && ln -sf /usr/local/bin/php /usr/local/bin/php-cli

# Install Composer (PHP dependency manager) for OpenEMR package installation
# Required by the CI/CD test suite
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# ============================================================================
# OPENEMR PHAR INSTALLATION
# ============================================================================
# Download and extract OpenEMR from the PHAR file
# Use the separate openemr.phar file which is a standard PHAR archive
# Note: This is a BUILD-TIME operation extracting to a clean /tmp location.
# User data paths (sites/*/documents, sites/*/sqlconf.php, etc.) are preserved
# via volumes at runtime and never overwritten by this build-time extraction.
# Architecture naming: amd64 and arm64 are used directly in release URLs
RUN ARCH_SUFFIX="${TARGETARCH}" \
    && cd /tmp \
    && curl -fSL "https://github.com/Jmevorach/openemr-static-binary-forge/releases/download/linux_${ARCH_SUFFIX}-php85-openemr-v${OPENEMR_VERSION}-${ARCH_SUFFIX}-${BINARY_RELEASE_DATE}/openemr.phar" -o openemr.phar \
    && test -s openemr.phar || (echo "ERROR: openemr.phar download failed - file is empty or missing" && exit 1) \
    && test "$(stat -f%z openemr.phar 2>/dev/null || stat -c%s openemr.phar 2>/dev/null || echo 0)" -gt 10000000 || (echo "ERROR: openemr.phar download appears invalid (file too small, expected >10MB)" && exit 1) \
    && mkdir -p /tmp/openemr \
    && /usr/local/bin/php -d memory_limit=512M -r "try { \$phar = new Phar('openemr.phar'); \$phar->extractTo('/tmp/openemr'); } catch (Exception \$e) { echo 'Error: ' . \$e->getMessage() . PHP_EOL; exit(1); }" \
    && rm openemr.phar

# Download tests directory from GitHub to match the PHAR version
# GitHub tag format is v7_0_4 (with v prefix and underscores)
# Note: GitHub archives strip the 'v' prefix, so openemr-v7_0_4 becomes openemr-7_0_4
RUN OPENEMR_TAG="v${OPENEMR_VERSION}" \
    && cd /tmp \
    && curl -fSL "https://github.com/openemr/openemr/archive/refs/tags/${OPENEMR_TAG}.tar.gz" -o openemr-source.tar.gz \
    && OPENEMR_DIR="openemr-${OPENEMR_VERSION}" \
    && tar -xzf openemr-source.tar.gz \
    && test -d "${OPENEMR_DIR}/tests" || (echo "ERROR: tests directory not found in ${OPENEMR_DIR}" && exit 1) \
    && mv "${OPENEMR_DIR}/tests" /tmp/openemr/tests \
    && rm -rf "${OPENEMR_DIR}" openemr-source.tar.gz

# ============================================================================
# OPENEMR SETUP AND PERMISSIONS
# ============================================================================
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
    && find openemr/sites/default/documents -type d -exec chmod 700 {} + 2>/dev/null || true \
    && find openemr/sites/default/documents -type f -exec chmod 600 {} + 2>/dev/null || true \
    # Set ownership to apache user for proper file access
    && chown -R apache:apache openemr/ \
    # Move OpenEMR to web root directory
    && mv openemr /var/www/localhost/htdocs/ \
    # Create SSL certificate directories
    && mkdir -p /etc/ssl/certs /etc/ssl/private \
    # Create PHP-FPM runtime and log directories
    && mkdir -p /var/log/php-fpm /run/php-fpm \
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

# Copy Apache virtual host configuration for OpenEMR
COPY openemr.conf /etc/apache2/conf.d/

# Copy PHP-FPM configuration files
COPY php-fpm.conf /usr/local/etc/php-fpm.conf
RUN mkdir -p /usr/local/etc/php-fpm.d
COPY php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy PHP configuration file with OpenEMR-optimized settings
COPY php.ini /usr/local/etc/php/php.ini

# ============================================================================
# OPENEMR SCRIPTS AND UTILITIES
# ============================================================================
# Copy main startup and configuration scripts
# - openemr.sh: Main container startup script (handles setup, upgrades, PHP-FPM, Apache)
# - ssl.sh: SSL/TLS certificate management script
# - auto_configure.php: Automated OpenEMR installation script
COPY openemr.sh ssl.sh auto_configure.php /var/www/localhost/htdocs/openemr/

# Copy admin unlock utilities (for password recovery)
COPY utilities/unlock_admin.php utilities/unlock_admin.sh /root/

# Set script permissions:
# - Executable scripts: 500 (read and execute for owner only)
# - PHP scripts: 000 (no access) - prevents accidental execution until enabled
RUN chmod 500 openemr.sh ssl.sh /root/unlock_admin.sh \
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
    /root/fsupgrade-8.sh

# ============================================================================
# APACHE AND PHP-FPM RUNTIME DIRECTORIES
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
# This script handles database setup, configuration, PHP-FPM, and Apache startup
CMD [ "./openemr.sh" ]

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# ============================================================================
# FINAL BUILD TARGET (ALIAS)
# ============================================================================
# This target is an alias for the base target
FROM base AS final
