#!/usr/bin/env bash

set -x

# openemr's setup requireemnts

sudo chmod 666 \
'/var/www/html/sites/*/sqlconf.php' \
'/var/www/html/interface/modules/zend_modules/config/application.config.php'

sudo chown -R www-data \
'/var/www/html/sites/*/documents' \
'/var/www/html/sites/*/edi' \
'/var/www/html/sites/*/era' \
'/var/www/html/sites/*/letter_templates' \
'/var/www/html/gacl/admin/templates_c' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/compiled' \
'/var/www/html/interface/main/calendar/modules/PostCalendar/pntemplates/cache'
