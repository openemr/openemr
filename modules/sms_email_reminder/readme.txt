
 To install backend notification processes you need to add
cron_email_notification.php and cron_sms_notification.php in system crontab to run
every hour.

 To set the sms/email engine use the frontend Miscellaneous>Batch Communication Tool>Sms/email
notifications settings and adjust Admin>Globals>Notifications accordingly.

 Also need to comment out the exit command from the beginning of the scripts.

* It only works with emails