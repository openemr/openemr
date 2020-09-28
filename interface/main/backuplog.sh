#/bin/bash
# $1 - mysql user $2 mysql password   $3 mysql Database $4 Log backup directory

# Create temp tables as that of Eventlog and log_comment_encrypt and api_log
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "create table if not exists log_comment_encrypt_new like log_comment_encrypt"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "create table if not exists log_new like log"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "create table if not exists api_log_new like api_log"
# Rename the existing  tables to backup & New tables to Event tables
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table log_comment_encrypt to log_comment_encrypt_backup,log_comment_encrypt_new to log_comment_encrypt"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table log to log_backup,log_new to log"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table api_log to api_log_backup,api_log_new to api_log"
# Dump the Backup tables
mysqldump -u $1 -p$2 -h $5 --port=$6 --opt --quote-names -r $4 $3 --tables log_comment_encrypt_backup log_backup api_log_backup
if [ $? -eq 0 ]
then
# After Successful dumping, drop the Backup tables
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "drop table if exists log_comment_encrypt_backup"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "drop table if exists log_backup"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "drop table if exists api_log_backup"
else
# If dumping fails, then restore the previous state
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e  "drop table if exists log_comment_encrypt"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table log_comment_encrypt_backup to log_comment_encrypt"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e  "drop table if exists log"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table log_backup to log"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e  "drop table if exists api_log"
mysql -u $1 -p$2 -h $5 --port=$6 -D $3 -e "rename table api_log_backup to api_log"
fi
