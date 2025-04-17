// Utility developed by Jason Brooks for Medical Information Integration, www.mi-squared.com
// 

This procedure will restore all records deleted on a given date/time) that are in the log table  

First, you must look into the admin/logs screen and determine the date that the delete occured on. 

Adjust the time accordingly 

use the mysql monitor:
========================================
mysql <database>

tee <outfile>;
select distinct comments from log where event like 'delete' and  date >='<date> 00:00:00' and date <= '<date> 23:59:59';
========================================
exit mysql monitor

Verify data is correct

perl convert_logcomments.pl < outfile  > restore.sql

verify insert statements

import data:

mysql <database> < restore.sql


