#/bin/bash
# $1 - mysql user $2 mysql password   $3 mysql Database $4 Log backup directory 

# Create a temp table as that of Eventlog
mysql -u $1 -p$2 -D $3 -e "create table if not exists log_new like log"
# Rename the existing Event table to Event_backup & New Event table to Event table
mysql -u $1 -p$2 -D $3 -e "rename table log to log_backup,log_new to log"
# Dump the Backup table
mysqldump -u $1 -p$2 --opt --quote-names -r $4 $3 --tables log_backup
if [ $? -eq 0 ]
then
# After Successful dumping, drop the Backup table
mysql -u $1 -p$2 -D $3 -e "drop table if exists log_backup"
else
# If dumping fails, then restore the previous state
mysql -u $1 -p$2 -D $3 -e  "drop table if exists log"
mysql -u $1 -p$2 -D $3 -e "rename table log_backup to log"
fi