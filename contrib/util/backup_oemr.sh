#!/bin/bash
# backups an openemr system and burns a cd

#vars

db='openemr'
dbuser='backuper'
dbpass='bakuper'
pguser='sql-ledger'
workdir='/var/tmp/backups/'
tempdir='/var/tmp/isos/'
installdir='/var/www/openemr'

# a name for the file that is unique of that day
file=`date +%F`

#get the content from the mysql database
mysqldump -u ${dbuser} -p${dbpass} --databases ${db} > ${workdir}${file}.sql.bak

# compress the file
bzip2 ${workdir}${file}.sql.bak

#get the content from the postgres database
pg_dumpall -U ${pguser} > ${workdir}${file}postgres.bak

# compress the file
bzip2 ${workdir}${file}postgres.bak

# get all the files on the site
tar -zcf ${workdir}${file}openemr.tar.gz ${installdir}

# create a disk image
label=`date +%y%m%d`
mkisofs -J -R -V ${label} -o ${tempdir}image.iso ${workdir}

#burn it
cdrecord -dao -v -data -eject ${tempdir}image.iso

#delete the original files
rm -rf ${workdir}*

exit 0
