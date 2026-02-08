#!/bin/bash

user="$1"
password="$2"
database="$3"
log_backup_dir="$4"
host="$5"
port="$6"

mysql() {
    command mysql "--user=${user}" \
                  "--password=${password}" \
                  "--host=${host}" \
                  "--port=${port}" \
                  "--database=${database}" \
                  "$@"
}

# Create temp tables as that of Eventlog and log_comment_encrypt and api_log
mysql -e 'create table if not exists log_comment_encrypt_new like log_comment_encrypt'
mysql -e 'create table if not exists log_new like log'
mysql -e 'create table if not exists api_log_new like api_log'

# Rename the existing  tables to backup & New tables to Event tables
mysql -e 'rename table log_comment_encrypt to log_comment_encrypt_backup,log_comment_encrypt_new to log_comment_encrypt'
mysql -e 'rename table log to log_backup,log_new to log'
mysql -e 'rename table api_log to api_log_backup,api_log_new to api_log'

# Dump the Backup tables
if mysqldump "--user=${user}" \
             "--password=${password}" \
             "--host=${host}" \
             "--port=${port}" \
             --opt \
             --quote-names \
             "--result-file=${log_backup_dir}" \
             "$3" \
             --tables log_comment_encrypt_backup log_backup api_log_backup; then

    # After Successful dumping, drop the Backup tables
    mysql -e 'drop table if exists log_comment_encrypt_backup'
    mysql -e 'drop table if exists log_backup'
    mysql -e 'drop table if exists api_log_backup'
else

    # If dumping fails, then restore the previous state
    mysql -e 'drop table if exists log_comment_encrypt'
    mysql -e 'rename table log_comment_encrypt_backup to log_comment_encrypt'
    mysql -e 'drop table if exists log'
    mysql -e 'rename table log_backup to log'
    mysql -e 'drop table if exists api_log'
    mysql -e 'rename table api_log_backup to api_log'
fi
