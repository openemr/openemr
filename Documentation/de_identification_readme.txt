Instructions to test De Identification:

1.Make sure that /interface/de_identification_forms/de_identification_procedure.sh and
/interface/de_identification_forms/re_identification_procedure.sh have execute permission for apache user

2.Set 'include_de_identification' to 1 in globals.php (currently de-identification works fine only with linux machines)

3.After successful login to openemr, create few patients and add issues, prescriptions, immunizations to the patients

4.Set de_identification_config variable to 1 ([OPENEMR]/contrib/util/de_identification_upgrade.php)
and run de_identification_upgrade.php to create procedures, functions, tables needed for de-identification
(administration -> De identification -> click here to run de_identification_upgrade.php - for first time) 
or http://HOSTNAME:PORT_NUMBER/contrib/util/de_identification_upgrade.php
(eg: http://vicareplus.com:3000/contrib/util/de_identification_upgrade.php). 
Mysql root user and password is required for successful execution of the upgrade script

5.Please restart the apache server before playing with de-identification and set de_identification_config variable back to zero

6.Once de_identification_upgrade is done and apache server is restarted, Start a new de-identification process
(administration -> De identification) by providing the inputs to de-identification process

7.Visit de-identification screen after some time, click download button to download the de-identified data
(De-identification files will be saved in '\tmp' location of the openemr machine and may contain sensitive data, 
so it is recommended to manually delete the files after its use)

8.For re-identification, provide re-identification code as input and click download button to download the re-identified data
(Re-identification files will be saved in '\tmp' location of the openemr machine and may contain sensitive data, 
so it is recommended to manually delete the files after its use)

9.When current de-identification process got hang in between, run following query in backend 
"update de_identification_status set status = 0;" , so as to start new de-identification process. 
For case of re-identification, run the following query "update re_identification_status set status = 0;"
