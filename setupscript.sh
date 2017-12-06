#!/usr/bin/env bash

set -x

sudo chmod 666 \
'/var/www/html/sites/default/sqlconf.php' \
'/var/www/html/interface/modules/zend_modules/config/application.config.php'

sudo chown -R www-data \
'/var/www/html/sites/default/documents' \
'/var/www/html/sites/default/edi' \
'/var/www/html/sites/default/era' \
'/var/www/html/sites/default/letter_templates' \
'/var/www/html/gacl/admin/templates_c' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/compiled' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/cache'
