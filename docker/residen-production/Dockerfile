FROM alpine:3.20

#Install dependencies and fix issue in apache
RUN apk --no-cache upgrade
RUN apk add --no-cache \
    apache2 apache2-ssl apache2-utils apache2-proxy git php83 php83-tokenizer php83-ctype php83-session php83-apache2 \
    php83-json php83-pdo php83-pdo_mysql php83-curl php83-ldap php83-openssl php83-iconv \
    php83-xml php83-xsl php83-gd php83-zip php83-soap php83-mbstring php83-zlib \
    php83-mysqli php83-sockets php83-xmlreader php83-redis php83-simplexml php83-xmlwriter php83-phar php83-fileinfo \
    php83-sodium php83-calendar php83-intl php83-opcache php83-pecl-apcu php83-fpm \
    perl mysql-client mariadb-connector-c tar curl imagemagick nodejs npm \
    certbot openssl openssl-dev dcron \
    rsync shadow ncurses \
    && sed -i 's/^Listen 80$/Listen 0.0.0.0:80/' /etc/apache2/httpd.conf
# Needed to ensure permissions work across shared volumes with openemr, nginx, and php-fpm dockers
RUN usermod -u 1000 apache

#BELOW LINE NEEDED TO SUPPORT PHP8 ON ALPINE 3.13+; SHOULD BE ABLE TO REMOVE THIS IN FUTURE ALPINE VERSIONS
RUN cp /usr/bin/php83 /usr/bin/php
# Install composer for openemr package building
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY . ./openemr

RUN apk add --no-cache git build-base \
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
    && apk del --no-cache git build-base \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *ErrorLog/#ErrorLog/' /etc/apache2/httpd.conf \
    && sed -i 's/^ *CustomLog/#CustomLog/' /etc/apache2/conf.d/ssl.conf \
    && sed -i 's/^ *TransferLog/#TransferLog/' /etc/apache2/conf.d/ssl.conf
WORKDIR /var/www/localhost/htdocs/openemr
VOLUME [ "/etc/letsencrypt/", "/etc/ssl" ]
#configure apache & php properly
ENV APACHE_LOG_DIR=/var/log/apache2
COPY docker/residen-production/php.ini /etc/php83/php.ini
COPY docker/residen-production/openemr.conf /etc/apache2/conf.d/
#add runner and auto_configure and prevent auto_configure from being run w/o being enabled
COPY docker/residen-production/openemr.sh docker/residen-production/ssl.sh docker/residen-production/xdebug.sh docker/residen-production/auto_configure.php /var/www/localhost/htdocs/openemr/
COPY docker/residen-production/utilities/unlock_admin.php docker/residen-production/utilities/unlock_admin.sh /root/
RUN chmod 500 openemr.sh ssl.sh xdebug.sh /root/unlock_admin.sh \
    && chmod 000 auto_configure.php /root/unlock_admin.php
#bring in pieces used for automatic upgrade process
COPY docker/residen-production/upgrade/docker-version \
     docker/residen-production/upgrade/fsupgrade-1.sh \
     docker/residen-production/upgrade/fsupgrade-2.sh \
     docker/residen-production/upgrade/fsupgrade-3.sh \
     docker/residen-production/upgrade/fsupgrade-4.sh \
     docker/residen-production/upgrade/fsupgrade-5.sh \
     docker/residen-production/upgrade/fsupgrade-6.sh \
     /root/
RUN chmod 500 \
    /root/fsupgrade-1.sh \
    /root/fsupgrade-2.sh \
    /root/fsupgrade-3.sh \
    /root/fsupgrade-4.sh \
    /root/fsupgrade-5.sh \
    /root/fsupgrade-6.sh
#fix issue with apache2 dying prematurely
RUN mkdir -p /run/apache2
#Copy dev tools library to root
COPY docker/residen-production/utilities/devtoolsLibrary.source /root/
#Ensure swarm/orchestration pieces are available if needed
RUN mkdir /swarm-pieces \
    && rsync --owner --group --perms --delete --recursive --links /etc/ssl /swarm-pieces/ \
    && rsync --owner --group --perms --delete --recursive --links /var/www/localhost/htdocs/openemr/sites /swarm-pieces/
#go
CMD [ "./openemr.sh" ]

EXPOSE 80 443
