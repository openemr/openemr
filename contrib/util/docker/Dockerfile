FROM alpine:3.5

#Install dependencies
#php7-tokenizer will be needed when migrating to 7.1
RUN apk add --no-cache \
    apache2 git php7 php7-ctype php7-session php7-apache2 php7-xml \
    php7-json php7-pdo php7-pdo_mysql php7-curl php7-ldap php7-iconv \
    php7-xml php7-xsl php7-gd php7-zip php7-soap php7-mbstring php7-zlib \
    php7-mysqli php7-sockets php7-phar php7-openssl perl mysql-client tar curl imagemagick-dev nodejs unzip \
    python openssl git libffi-dev py-pip python-dev build-base openssl-dev \
    dcron vim nano bash bash-doc bash-completion tree curl && \
    apk add apache2-ssl --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.6/main
RUN npm install -g bower
RUN curl -sS https://getcomposer.org/installer | php7 -- --install-dir=/usr/bin --filename=composer
RUN git clone https://github.com/letsencrypt/letsencrypt /opt/certbot \
    && pip install -e /opt/certbot/acme -e /opt/certbot \
    && mkdir -p /etc/ssl/certs /etc/ssl/private \
    && apk del --no-cache git build-base libffi-dev python-dev
WORKDIR /var/www/localhost/htdocs/openemr
VOLUME [ "/etc/letsencrypt/", "/etc/ssl" ]
#configure apache & php properly
ENV APACHE_LOG_DIR=/var/log/apache2
COPY docker-assets.zip .
RUN unzip docker-assets.zip
RUN mv php.ini /etc/php7/php.ini
RUN mv openemr.conf /etc/apache2/conf.d/
#add runner and auto_configure and prevent auto_configure from being run w/o being enabled
RUN mv run_openemr.sh /var/www/localhost/htdocs/
RUN mv auto_configure.php /var/www/localhost/htdocs/
RUN mv utilities/unlock_admin.php /root/
RUN mv utilities/unlock_admin.sh /root/
RUN rm docker-assets.zip
RUN chmod 500 /var/www/localhost/htdocs/run_openemr.sh /root/unlock_admin.sh \
    && chmod 000 /var/www/localhost/htdocs/auto_configure.php /root/unlock_admin.php \
    && ln -s /usr/bin/php7 /usr/bin/php
#fix issue with apache2 dying prematurely
RUN mkdir -p /run/apache2
#go
CMD [ "/var/www/localhost/htdocs/run_openemr.sh" ]

EXPOSE 80 443
