#!/bin/sh
# Small Bash Script to help you import the sql files
# into openemr database, in order to use DBC Dutch System
#
# 2008 Cristian Navalici ncristian @lemonsoftware . eu 


DIRSQLDBC='/var/www/html/openemrdbc/sql/DBC_sql_statements'
USER='user for import'
PASS='password'
DATABASE='openemr db'

cd $DIRSQLDBC

for i in *.sql.gz ;
do 
	echo "Unpacking $i";
	gunzip "$i";
done;

for j in *.sql;
do
	SIZE=`du -h $j`;
	echo "Importing $j (SIZE: $SIZE)";
	mysql -u$USER -p$PASS $DATABASE < $j;
done;

