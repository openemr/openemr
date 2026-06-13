ARG ALPINE_VERSION=3.23
ARG NGINX_VERSION=1.29.8
ARG PHP_VERSION=8.5.5
ARG NODE_VERSION=24.14.1

FROM node:${NODE_VERSION}-alpine${ALPINE_VERSION} AS assets
WORKDIR /build
COPY package.json package-lock.json gulpfile.js ./
# gulp -i needs these directories to exist for the copy targets
RUN mkdir -p public/assets interface/themes
RUN npm ci
# Copy source SCSS files for theme compilation
COPY interface/themes interface/themes
# SCSS imports from public/assets/modified (tracked in git, not generated)
COPY public/assets/modified public/assets/modified
# Build production themes
RUN npm run build

FROM composer:2 AS dependencies
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-dev --no-interaction --no-autoloader

FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS env
RUN apk add --no-cache \
  # gd \
  libpng-dev \
  # intl \
  icu-dev \
  # imagemagick \
  imagemagick-dev \
  # ldap \
  openldap-dev \
  # sockets \
  linux-headers \
  # xsl + soap \
  libxslt-dev \
  # zip \
  libzip-dev \
  $PHPIZE_DEPS
RUN docker-php-ext-install \
  bcmath \
  calendar \
  ctype \
  gd \
  intl \
  ldap \
  mysqli \
  pdo \
  pdo_mysql \
  soap \
  sockets \
  xsl \
  zip

RUN pecl install imagick && docker-php-ext-enable imagick

RUN addgroup php-users && adduser -D -G php-users php
USER php
WORKDIR /app

FROM env AS server
## PHP config
COPY config/php $PHP_INI_DIR/conf.d/
ENV \
  PHP_ZEND_ASSERTIONS="-1"
## PHP-FPM config
ENV \
  PHP_FPM_LISTEN="9000" \
  PHP_FPM_ACCESS_LOG="/proc/self/fd/2" \
  # Keep this last
  PHP_FPM_INI_DIR="/usr/local/etc/php-fpm.d"
# set other ini-related values
COPY config/php-fpm/ $PHP_FPM_INI_DIR

COPY --from=dependencies /usr/bin/composer /usr/bin/composer
COPY --chown=php:php-users --from=dependencies /app/vendor ./vendor
COPY --chown=php:php-users . .
# preflight scripts in CLI

# Disabled, laminas gives false-positive warning
# RUN composer check-platform-reqs
RUN composer dump-autoload --optimize

FROM nginx:${NGINX_VERSION}-alpine${ALPINE_VERSION} AS nginx
COPY config/nginx/frontcontroller-fpm.conf.template /etc/nginx/templates/default.conf.template
COPY --from=assets /build/public/assets /app/public/assets
COPY --from=assets /build/public/themes /app/public/themes
COPY public/images /app/public/images
COPY public/certs /app/public/certs
COPY public/smart-styles /app/public/smart-styles
ENV FASTCGI_HOST=php-fpm
ENV FASTCGI_PORT=9000

# Apache + mod_php (Debian-based)
FROM php:${PHP_VERSION}-apache AS apache-modphp-base
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpng-dev \
  libicu-dev \
  libldap2-dev \
  libxslt1-dev \
  libzip-dev \
  libmagickwand-dev \
  && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install \
  bcmath \
  calendar \
  ctype \
  gd \
  intl \
  ldap \
  mysqli \
  pdo \
  pdo_mysql \
  soap \
  sockets \
  xsl \
  zip
RUN pecl install imagick && docker-php-ext-enable imagick
RUN a2enmod rewrite

FROM apache-modphp-base AS apache-modphp
WORKDIR /app
COPY config/apache/frontcontroller-modphp.conf /etc/apache2/sites-available/000-default.conf
COPY config/php/ $PHP_INI_DIR/conf.d/
COPY --from=dependencies /usr/bin/composer /usr/bin/composer
COPY --from=dependencies /app/vendor ./vendor
COPY . .
# Laminas; see above.
# RUN composer check-platform-reqs
RUN composer dump-autoload --optimize
