# contrib/util/docker/Dockerfile Automated setup of openemr in docker container
#
# Dockerfile providing environment to run openemr in for testing purposes.
#
# Copyright (C) 2017 Jeffrey McAteer <jeffrey.p.mcateer@gmail.com>
#
# LICENSE: This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
#
# @package OpenEMR
# @author  Jeffrey McAteer <jeffrey.p.mcateer@gmail.com>
# @link    http://www.open-emr.org

# Quick one-liner:
# docker build -f contrib/util/docker/Dockerfile . -t openemr && docker run -it -p 9000:80 openemr
# For debugging things: docker run -it -p 9000:80 openemr bash

# NB: 'localhost' does not resolve to this container - use 127.0.0.1 instead for things like sql server

FROM php:7.0-apache
LABEL maintainer "jeffrey.p.mcateer@gmail.com"

ENV DEBIAN_FRONTEND noninteractive

RUN yes | apt-get update && \
    yes | apt-get upgrade && \
    yes | apt-get install mysql-server libcurl4-gnutls-dev && \
    docker-php-ext-install mysqli curl pdo_mysql

COPY . /var/www/html/
WORKDIR /var/www/html
RUN chmod a+w \
        sites/default/sqlconf.php \
        interface/modules/zend_modules/config/application.config.php \
        gacl/admin/templates_c  \
        sites/default/edi       \
        sites/default/era       \
        sites/default/documents \
        custom/letter_templates \
        interface/main/calendar/modules/PostCalendar/pntemplates/compiled \
        interface/main/calendar/modules/PostCalendar/pntemplates/cache;   \
    find . -exec chown -R www-data:www-data {} \;

# Setup config per. req
ENV INI /usr/local/etc/php/conf.d/fixes.ini
RUN echo '<?php'                    > $INI; \
    echo 'short_open_tag=false;'    >> $INI; \
    echo 'max_input_vars=3001;'     >> $INI; \
    echo 'max_execution_time=61;'   >> $INI; \
    echo 'max_input_time=91;'       >> $INI; \
    echo 'post_max_size="31M";'     >> $INI; \
    echo 'memory_limit="129M";'     >> $INI; \
    echo 'file_uploads = "On";'     >> $INI; \
    echo '?>'                       >> $INI

# Protect proper dirs
ENV CONF /etc/apache2/apache2.conf
RUN echo '<Directory "/var/www/html">'                      >> $CONF; \
    echo '      AllowOverride FileInfo'                     >> $CONF; \
    echo '  </Directory>'                                   >> $CONF; \
    echo '  <Directory "/var/www/html/sites">'              >> $CONF; \
    echo '      AllowOverride None'                         >> $CONF; \
    echo '  </Directory>'                                   >> $CONF; \
    echo '  <Directory "/var/www/html/sites/*/documents">'  >> $CONF; \
    echo '      order deny,allow'                           >> $CONF; \
    echo '      Deny from all'                              >> $CONF; \
    echo '  </Directory>'                                   >> $CONF; \
    echo '  <Directory "/var/www/html/sites/*/edi">'        >> $CONF; \
    echo '      order deny,allow'                           >> $CONF; \
    echo '      Deny from all'                              >> $CONF; \
    echo '  </Directory>'                                   >> $CONF; \
    echo '  <Directory "/var/www/html/sites/*/era">'        >> $CONF; \
    echo '      order deny,allow'                           >> $CONF; \
    echo '      Deny from all'                              >> $CONF; \
    echo '  </Directory>'                                   >> $CONF; \
    echo 'LoadModule rewrite_module /usr/lib/apache2/modules/mod_rewrite.so' >> $CONF

CMD ["sh", "-c", "/etc/init.d/mysql start; apache2-foreground"]
