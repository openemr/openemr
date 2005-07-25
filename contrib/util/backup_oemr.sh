#! /bin/sh

# backups an openemr system and burns it a cd
# this script needs to have the right permission set 
# and requires mkisofs and cdrecord applications
# it might be a good idea to cron it


#vars, replace with your values.

db='openemr1'
dbuser='backuper'
dbpass='backuper'

#make sure these dir are right for your sys
workdir='/var/tmp/backups/'
tempdir='/var/tmp/isos/'
installdir='/var/www/openemr'


# a name for the file that is unique of that day
file=`date +%F`

#get the content from the mysql database
mysqldump -u ${dbuser} -p${dbpass} --databases ${db} > ${workdir}${file}.sql.bak

# compress the file
bzip2 ${workdir}${file}.sql.bak

# get all the files on the site
tar -zcf ${workdir}backupofopenemr.tar.gz ${installdir}

# create a disk image
label=`date +%y%m%d`
mkisofs -J -R -V ${label} -o ${tempdir}image.iso ${workdir}

#burn it
cdrecord -dao -v -data -eject ${tempdir}image.iso

#delete the original files
rm -rf ${workdir}*


exit 0
