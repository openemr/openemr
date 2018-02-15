#!/usr/bin/env bash

set -x

sudo cp /var/www/html/sites/*/sqlconf.php /home/*/openemrbackup/sites/*/sqlconf.php

sudo cp /var/www/html/sites/*/config.php /home/*/openemrbackup/sites/*/config.php

sudo cp \
/var/www/html/sites/*/documents /home/*/openemrbackup/sites/*/documents \
/var/www/html/sites/*/era /home/*/openemrbackup/sites/*/era \
/var/www/html/sites/*/edi /home/*/openemrbackup/sites/*/edi \
/var/www/html/sites/*/letter_templates /home/*/openemrbackup/sites/*/letter_templates 
