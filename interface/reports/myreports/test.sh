#!/bin/sh
set TST="false"
for f in ./*
do
	if [ -e "$f" ]
	then
		TST="true"
		echo "There are files here\n"
	else
		echo "There are No files here\n"
	fi
	break
done

echo $TST
echo "\n"
 

