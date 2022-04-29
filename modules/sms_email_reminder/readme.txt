
 To install backend notification processes you need to add
cron_email_notification.php in system crontab to run every hour.

 To set the sms/email engine use the frontend Miscellaneous>Batch Communication Tool>Sms/email
notifications settings and adjust Admin>Globals>Notifications accordingly.

 You should create a folder called logs in in ./openemr/modules/sms_email_reminder/.

 Also need to comment out the exit command from the beginning of the scripts.

* It only works with emails