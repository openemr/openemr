#
# Copyright (C) 2018 Brady Miller <brady.g.miller@gmail.com>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# php-fpm Dockerfile build for openemr development docker environment
# This docker is hosted here: https://hub.docker.com/r/openemr/dev-php-fpm/ <tag is 7.2>
#
FROM nginx

# Copy over the nginx.conf conf
COPY nginx.conf /etc/nginx/nginx.conf

# Copy over the dummy self signed key/cert
COPY dummy-cert /etc/nginx/dummy-cert
COPY dummy-key /etc/nginx/dummy-key

# Needed to ensure permissions work across shared volumes with openemr, nginx, and php-fpm dockers
RUN usermod -u 1000 nginx
