FROM php:7.2-fpm

RUN apt-get update

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions pdo_mysql \
                           ldap \
                           xsl \
                           gd \
                           zip \
                           soap \
                           mysqli \
                           sockets \
                           tokenizer \
                           xmlreader
