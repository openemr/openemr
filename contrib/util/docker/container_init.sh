#!/bin/bash

# contrib/util/docker/container_init.sh Docker container file to begin execution of openemr system
#
# A script which executes things when an openemr container starts.
# Necessary because setup steps (changing file permissions) must happen
# after we have access to files under /var/www/html/, and with
# the newer setup this does not happen until after the container starts running.
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

# Fix permissions
cd /var/www/html/
chmod a+w \
    sites/default/sqlconf.php \
    interface/modules/zend_modules/config/application.config.php \
    gacl/admin/templates_c  \
    sites/default/edi       \
    sites/default/era       \
    sites/default/documents \
    custom/letter_templates \
    interface/main/calendar/modules/PostCalendar/pntemplates/compiled \
    interface/main/calendar/modules/PostCalendar/pntemplates/cache
find . -exec chown -R www-data:www-data

# Start mysql & apache
service mysql start
service apache2 start
sleep 2 # politeness

# Run auto-setup things
sed -i 's/$config = 1;/$config = 0;/g' /var/www/html/sites/default/sqlconf.php
/var/www/html/contrib/util/docker/setup_automation.sh http://localhost

#sleep infinity
bash
# ^ better, you can poke things server-side with an interactive shell
