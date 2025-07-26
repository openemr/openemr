# syntax=docker/dockerfile:1
#
# Copyright (C) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# php-fpm Dockerfile build for openemr development docker environment
# This docker is hosted here: https://hub.docker.com/r/openemr/dev-php-fpm/ <tag is 7.1>
#
FROM php:7.1-fpm

# Add mysql-client package that is needed in the OpenEMR Backup gui, which does direct command mysql commands
# Add imagemagick that is needed for some image processing in OpenEMR
# Note this basically add 160MB of space to the docker, so would be nice for OpenEMR to not require this stuff
#  and instead rely on php scripts, if possible.
RUN apt-get update \
 && apt-get install -y imagemagick \
                       mysql-client

# Add the php extensions (note using a very cool script by mlocati to do this)
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions \
 && sync \
 && install-php-extensions calendar \
                           gd \
                           gettext \
                           ldap \
                           mysqli \
                           pdo_mysql \
                           redis \
                           soap \
                           sockets \
                           tokenizer \
                           xmlreader \
                           xsl \
                           zip



# Needed to ensure permissions work across shared volumes with openemr, nginx, and php-fpm dockers
RUN usermod -u 1000 www-data

# Copy over the php.ini conf
COPY php.ini /usr/local/etc/php/php.ini
