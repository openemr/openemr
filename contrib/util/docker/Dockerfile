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
# cd contrib/util/docker; docker build . -t openemr; cd ../../..; docker run -it -p 8000:80 -p 3306:3306 -v `pwd`:/var/www/html/ openemr

# NB: The current directory now matters much more when building the container. Move to the contrib/util/docker container to build
#     so you aren't sending ~500mb to the docker server, then move to the root of the git repo (or hardcode the absolute path)
#     so the -v flag lines up (the absolute path of the git repo must be on the left, path to mount inside container on right)

# NB: 'localhost' does not resolve to this container - use 127.0.0.1 instead for things like sql server

FROM php:7.0-apache
LABEL maintainer "jeffrey.p.mcateer@gmail.com"

ENV DEBIAN_FRONTEND noninteractive

RUN yes | apt-get update && \
    yes | apt-get upgrade && \
    yes | apt-get install mysql-server libcurl4-gnutls-dev && \
    docker-php-ext-install mysqli curl pdo_mysql

# Configure SQL
RUN sed -i 's/bind-address.*127.0.0.1/bind-address = 0.0.0.0/g' /etc/mysql/my.cnf
RUN /etc/init.d/mysql start; sleep 3; \
    mysql -u root --password='' -e 'GRANT ALL PRIVILEGES ON *.* TO "root"@"%" IDENTIFIED BY "" WITH GRANT OPTION; FLUSH PRIVILEGES;'

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

CMD ["sh", "-c", "[ -e /var/www/html/contrib/util/docker/container_init.sh ] && /var/www/html/contrib/util/docker/container_init.sh || echo 'You have not mounted the openemr directory inside the container.'"]
