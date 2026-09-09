# syntax=docker/dockerfile:1
#
# Copyright (C) 2018-2025 Brady Miller <brady.g.miller@gmail.com>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# php-fpm Dockerfile build for openemr development docker environment
# This docker is hosted here: https://hub.docker.com/r/openemr/dev-php-fpm/ <tag is 7.2>
#
FROM openemr/dev-php-fpm:pre-build-dev-86

# Add mariadb-client package that is needed in the OpenEMR Backup gui, which does direct command mysql commands
# Add imagemagick that is needed for some image processing in OpenEMR
# Note this basically add 160MB of space to the docker, so would be nice for OpenEMR to not require this stuff
#  and instead rely on php scripts, if possible.
RUN apt-get update \
 && apt-get install -y curl \
                       imagemagick \
                       mariadb-client

# Install correct version nodejs
RUN curl -sL https://deb.nodesource.com/setup_24.x \
  | bash - \
 && apt-get update \
 && apt-get install -y nodejs

# Fetch the docker-php-extension-installer script here (rather than just before
# the standard-install RUN below). It's needed both by the tripwire immediately
# after and by the standard install several steps down; installing it once
# keeps the two callers on the same version.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions \
 && sync

# Tripwire: probe whether install-php-extensions can now install any of the
# extensions we currently work around from source (imagick, redis, pcov). If
# so, retire the corresponding from-source block below and add the extension
# name(s) to the install-php-extensions arg list further down. Fires per-
# extension so the failure message names which workarounds can be retired.
#
# Must run BEFORE the from-source blocks: install-php-extensions is
# idempotent and reports success if the extension is already registered,
# even without doing any real build work. Probing on a clean image forces a
# real compile attempt, whose exit code is the honest signal.
#
# On probe success we exit 1 -- the image build fails loudly to signal
# maintenance is due. Because install-php-extensions installs on success
# (no dry-run mode), successful probes leave those extensions installed;
# the exit-1 stops the build before that state gets published.
RUN failed=""; \
    for ext in imagick redis pcov; do \
        if install-php-extensions "$ext" >/dev/null 2>&1; then \
            failed="${failed}${ext} "; \
        fi; \
    done; \
    if [ -n "$failed" ]; then \
        echo "TRIPWIRE FIRED: install-php-extensions now supports: ${failed}on this PHP." >&2; \
        echo "                Retire the from-source workaround(s) below and add the" >&2; \
        echo "                extension name(s) to the install-php-extensions arg list further down." >&2; \
        exit 1; \
    fi

# Temporary fix to get magick install working by bypassing below install-php-extensions run (these issues are usually temporary
#  in dev versions of PHP, so this is not a permanent solution)
# TODO:
# TODO: intermittently try to remove this block of code and add back imagick in below ../install-php-extensions run
# TODO:
RUN apt-get update && apt-get install -y \
    libmagickwand-dev \
    libmagickcore-dev \
    git \
 && cd /tmp \
 && git clone https://github.com/Imagick/imagick.git \
 && cd imagick \
 && phpize \
 && ./configure \
 && make -j$(nproc) \
 && make install \
 && docker-php-ext-enable imagick \
 && rm -rf /tmp/imagick

# Temporary fix to get redis install working by bypassing below install-php-extensions run (these issues are usually temporary
#  in dev versions of PHP, so this is not a permanent solution)
# TODO:
# TODO: intermittently try to remove this block of code and add back redis in below ../install-php-extensions run
# TODO:
# Install Redis from GitHub develop branch
RUN apt-get update && apt-get install -y git \
 && mkdir -p /tmp/redis \
 && cd /tmp/redis \
 && git clone https://github.com/phpredis/phpredis.git . \
 && git checkout develop \
 && phpize \
 && ./configure \
 && make && make install \
 && docker-php-ext-enable redis \
 && rm -rf /tmp/redis

# Temporary fix to get pcov install working by bypassing below install-php-extensions run (these issues are usually temporary
#  in dev versions of PHP, so this is not a permanent solution)
# TODO:
# TODO: intermittently try to remove this block of code and add back pcov in below ../install-php-extensions run
# TODO:
# krakjoe/pcov's `develop` (default) branch still uses INI_BOOL/INI_INT/INI_STR
# macros that PHP 8.6's Zend engine removed. The fix lives on the
# `issue-php86a2` topic branch (single commit from 2026-07-21), unmerged so
# far. Track that branch until upstream merges it into develop; the tripwire
# above fires when install-php-extensions can install pcov cleanly, prompting
# a switch back to the standard install path.
RUN apt-get update && apt-get install -y git \
 && mkdir -p /tmp/pcov \
 && cd /tmp/pcov \
 && git clone https://github.com/krakjoe/pcov.git . \
 && git checkout issue-php86a2 \
 && phpize \
 && ./configure \
 && make && make install \
 && docker-php-ext-enable pcov \
 && rm -rf /tmp/pcov

# Add the standard php extensions (note using a very cool script by mlocati to do this).
# imagick, redis, and pcov are handled by the from-source workaround blocks above;
# the tripwire above monitors when they can move back into this list.
RUN install-php-extensions bcmath \
                           calendar \
                           dom \
                           gd \
                           gettext \
                           intl \
                           ldap \
                           mysqli \
                           pdo_mysql \
                           soap \
                           sockets \
                           tokenizer \
                           xmlreader \
                           xsl \
                           zip

# Needed to ensure permissions work across shared volumes with openemr, nginx, and php-fpm dockers
RUN usermod -u 1000 www-data

# Copy over the php.ini conf (default: plain; insane bind-mounts php-ini/redis/php.ini
# for the redis-flavored fpm sidecars to flip session storage to redis)
COPY php-ini/php.ini /usr/local/etc/php/php.ini

# Copy xdebug installation script for CI coverage support
# Script is placed at /usr/share/nginx/html/xdebug.sh so CI can call ../xdebug.sh
# from the OpenEMR directory at /usr/share/nginx/html/openemr
COPY xdebug.sh /usr/share/nginx/html/xdebug.sh
RUN chmod +x /usr/share/nginx/html/xdebug.sh
