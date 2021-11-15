FROM php:7.4-cli

RUN docker-php-ext-install -j$(nproc) bcmath
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.remote_autostart=1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.remote_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

CMD [ "php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]