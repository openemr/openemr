# Windows 2003 server

This document describes how to obtain and install OpenEMR on Windows platforms.

## Installation using XAMPP

* Choose which distribution to install.

    We recommend installing a stable production release. For our project, it was the XAMPP 1.4.11 version, which is free of charge.

* Download the distribution you want to install.

    We downloaded the XAMPP 1.4.11 installer, xampp-win32-1.4.11-installer.exe, from the Apache Friends organization's Web site, http://www.apachefriends.org, because it's easy and safe.

* Install the distribution.

    Installation of XAMPP is very easy. Just unpack the package on the partition of your choice. We chose the default, creating the main directory in C:\apachefriends\xampp.

* Perform post-installation setup.

    To start XAMPP, choose the "xampp basic start" program from the start menu (start menu->apachefieends->xampp).

    Although MySQL starts without a password for "root," you can add a root password, if you wish. To do so, use "mysqladmin" under the console or use the "phpMyAdmin" graphics program found on the XAMPP tools menu.

    Change the XAMPP switch from PHP 5 to the PHP 4.3.10 version by using the "php switch" program from the start menu (startmenu->apachefriends->php switch).

## Installation of OpenEMR

* Choose which distribution to install.

    We recommend installing a stable production release. For our project with Windows, it was the OpenEMR 2.6.5 version, which is also free of charge.

* Download the distribution you want to install.

    We downloaded the openemr-ea-2.5.3PF.tgz version from the OpenEMR Web site, http://openemr.net.

* Install the distribution.

    For installation, we used the following Pennington Firm "OpenEMR Windows installation" document:

    1. As an administrator, extract using WinZip the openemr archive copy and move the openemr folder into:

            C:\apachefriends\xampp\htdocs

    2. Use WordPad to open globals.php file, which is in:

            C:\apachefriends\xampp\htdocs\openemr\interface

    3. Change

            $webserver_root ="/var/www/openemr";

        to

            $webserver_root ="C: /apachefriends/xampp/htdocs/openemr".
    4. Navigate to [http://localhost/openemr/setup.php](http://localhost/openemr/setup.php)
    5. Click "Continue" button.
    6. For the first step, choose "Have setup create the databases" and click "Continue."
    7. For the next step, choose the following entries:

        1. mysql root password -- for password that you choose early;
        2. openemr password -- use something simple like "openemr1";
        3. database name -- for example, use "openemr";
        4. change default clinic name to the name of your clinic -- for example, use "clinic"
        5. Click “Continue” and soon you will be see the message,

                "Congratuations..."

Perform post-installation setup.
