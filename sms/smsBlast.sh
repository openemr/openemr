#!/bin/bash
ps -ef | grep blaster.php | grep -v grep
if [ $? = "0" ] 
then
  echo "Blaster is Active"
else
  echo "Blaster Needs Restarted"
  cd ../interface
  php ../sms/blaster.php > /var/log/smsBlaster.log 2>&1 & 
fi

