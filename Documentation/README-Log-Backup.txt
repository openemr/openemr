-- Created By ViCarePlus, Visolve (vicareplus_engg@visolve.com)
-------------------------------------------------------------------

Create an entry in the CRON tab for taking weekly backup of the OpenEMR Log table

1) This Cron job will take weekly backup on Every Sunday, 0th hour,0th min
2) Arguments are mandatory.  For successful execution of the script,
     a) you must know your webserver_root folder i.e. /var/www/openemr
     b) backup_log_dir value must match with the value in the Globals setting.

Use the following in the crontab:
-------------------------------
0 0 * * 0 php [webserver_root]/interface/main/backuplog.php webserver_root backup_log_dir

**** Eg. 0 0 * * 0 php /home/openemr/interface/main/backuplog.php /home/openemr /opt

