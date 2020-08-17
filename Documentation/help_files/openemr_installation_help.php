<?php

/**
 * OpenEMR Installation Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel=stylesheet href="../../public/themes/style_light.css">
        <link rel="stylesheet" href="../../public/assets/jquery-ui/jquery-ui.css">
        <script src="../../public/assets/jquery/dist/jquery.min.js"></script>
        <script src="../../public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../../public/assets/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="shortcut icon" href="../../public/images/favicon.ico" />
        <script src="../../public/assets/jquery-ui/jquery-ui.js"></script>
    <title><?php echo ("OpenEMR Installation Help");?></title>
    <style>
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        .oe-text-green {
            color: var(--success);
        }
    </style>
    </head>
   <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo ("OpenEMR Installation Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo ("Welcome to OpenEMR");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("If you would like to directly proceed to the Multi Site Installation help section");?>.
                        <a href="#section12"><?php echo ("Click here");?></a>
                    </p>

                    <p><?php echo ("OpenEMR is a Free and Open Source electronic health records and medical practice management application");?>.</p>

                    <p><?php echo ("It is ONC Certified and it features a fully integrated electronic health records, practice management, scheduling, electronic billing application and a whole lot more");?>.</p>

                    <p><?php echo ("It can run on many platforms including Windows, Linux, FreeBSD and macOS");?>.</p>

                    <p><?php echo ("You are about to install a fully functional version of this application");?>.</p>

                    <p><?php echo ("Before you proceed with the installation you need to understand how the application is structured");?>.</p>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo ("To do so read the introduction section first and start the installation. You can click and jump to the help section of any particular step at any time");?>.</p>

                    <p><?php echo ("Being an open source application you have full access to the source code and the ability to modify it as you wish");?>.</p>

                    <p><?php echo ("OpenEMR is a three-tier web database application, the day to day user interacts with it via a web browser");?>.</p>

                    <p><?php echo ("The three tiers are");?>:</p>
                        <ul>
                            <li><strong><?php echo ("user tier");?></strong> - <?php echo ("presents data to and collects data from the user, usually via a web browser."); ?></li>
                            <li><strong><?php echo ("data tier");?></strong> - <?php echo ("stores and retrieves the data mostly from a database, though some data exists in directories outside of the database"); ?></li>
                            <li><strong><?php echo ("middle tier");?></strong> - <?php echo ("processes input from the user and queries the database, reads and writes data to the database and other directories, processes the returned results and presents it to the user. The majority of the application logic resides in this tier. It drives the structure and content of the data displayed to the user"); ?></li>
                        </ul>

                    <p><?php echo ("The middle tier requires a web server (Apache), a web scripting language (PHP), and the scripting language engine (Zend Engine)");?>.</p>

                    <p><?php echo ("This web server may be on an intranet or on a server on the internet. It may even be deployed in the cloud");?>.</p>

                    <p><?php echo ("Technically the web server could even be on a single computer, i.e. that of the user");?>.</p>

                    <p><?php echo ("There are two parts to the OpenEMR application");?>:</p>

                        <ul>
                            <li><?php echo ("The scripts that the web server uses to interact with the data and display results on a web page. These scripts need to be placed in the web server document root directory, e.g. /var/www/html/openemr in Ubuntu/Debian"); ?></li>
                            <li><?php echo ("The data - both application specific and clinical, which exists mainly in a database, though some data exists in directories outside of the database"); ?></li>
                        </ul>

                    <p><?php echo ("These scripts are mainly written in PHP, which stands for PHP: Hypertext Preprocessor, a recursive acronym, the original name being Personal Home Page Tools. It is a server-side scripting language used extensively in web based applications");?>.</p>

                    <p><?php echo ("The data is mainly stored in a database. OpenEMR supports many types of databases, the majority of installations use MySQL or MariaDB");?>.</p>

                    <p><?php echo ("Thus for the OpenEMR application to function the following software needs to be installed in the system hosting the application"); ?>.</p>
                        <ul>
                            <li><?php echo ("Web Server"); ?></li>
                            <li><?php echo ("PHP scripting language and the scripting language engine (Zend Engine)"); ?></li>
                            <li><?php echo ("Database - MySQL or MariaDB"); ?></li>
                        </ul>

                    <p><?php echo ("As they are frequently needed together to run web based applications they along with the operating system they run on are referred together as a stack");?>.</p>

                    <p><?php echo ("The common stacks are LAMP, XAMPP, WAMP, MAMP and LEMP");?>.</p>

                        <ul>
                            <li><strong><?php echo ("LAMP");?></strong> - <?php echo ("Linux OS, Apache Webserver, MySQL database and PHP scripting language"); ?></li>
                            <li><strong><?php echo ("XAMPP");?></strong> - <?php echo ("X OS - Cross platform, Apache Webserver, MariaDB database, PHP scripting language and Perl"); ?></li>
                            <li><strong><?php echo ("WAMP");?></strong> - <?php echo ("Windows OS, Apache Webserver, MySQL database and PHP scripting language"); ?></li>
                            <li><strong><?php echo ("MAMP");?></strong> - <?php echo ("macOS, Apache Webserver, MySQL database and PHP scripting language"); ?></li>
                            <li><strong><?php echo ("LEMP");?></strong> - <?php echo ("Linux OS, nginx (pronounced engine x) Webserver, MySQL database and PHP scripting language"); ?></li>
                        </ul>

                    <p id="main-list"><?php echo ("The setup process is broken into the following steps. (Click link to jump to any step)");?>:</p>
                    <ul>
                        <li><a href="#section1"><?php echo ("Pre Install - Checking File and Directory Permissions");?></a></li>
                        <li><a href="#section2"><?php echo ("Step 1 Select Database Setup");?></a></li>
                        <li><a href="#section3"><?php echo ("Step 2 Database and OpenEMR Initial User Setup Details");?></a></li>
                        <li><a href="#section4"><?php echo ("Step 3 Creating Database and First User");?></a></li>
                        <li><a href="#section5"><?php echo ("Step 4 Creating and Configuring Access Control List");?></a></li>
                        <li><a href="#section6"><?php echo ("Step 5 Configure PHP");?></a></li>
                        <li><a href="#section7"><?php echo ("Step 6 Configure Apache Web Server");?></a></li>
                        <li><a href="#section8"><?php echo ("Step 7 Select a Theme");?></a></li>
                        <li><a href="#section9"><?php echo ("Final step - Success");?>!</a></li>
                    </ul>

                    <p><?php echo ("Additional information");?>:</p>
                    <ul>
                        <li><a href="#section10"><?php echo ("Life cycle of the OpenEMR application");?></a></li>
                        <li><a href="#section11"><?php echo ("Upgrading");?></a></li>
                    </ul>

                    <p><?php echo ("Multi Site Installation");?>:</p>
                    <ul>
                        <li><a href="#section12"><?php echo ("Multi Site Installation");?></a></li>
                    </ul>

                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Pre Install - Checking File and Directory Permissions"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("If you are accessing this help file from the setup.php script it means that the web server (Apache) is running and that the minimum version of PHP required for OpenEMR scripts to work is present");?>.</p>

                    <p><?php echo ("It also means that you have copied the OpenEMR script files to the web server document root directory");?>.</p>

                    <p><?php echo ("A dynamic application like OpenEMR has data constantly being added and modified");?>.</p>

                    <p><?php echo ("Data is written not only to the database but also to other directories that exist in the web server document root directory");?>.</p>

                    <p><?php echo ("In addition during the installation process certain application files are modified");?>.</p>

                    <p><?php echo ("These files need to have their permissions set so that they could be modified during the installation process");?>.</p>

                    <p><?php echo ("This file should be writable by everybody"); ?>.</p>
                        <ul>
                            <li><strong><?php echo ("openemr/sites/default/sqlconf.php"); ?></strong></li>
                        </ul>
                    <p><?php echo ("In Linux use the following command to set the proper permissions");?>.</p>

                    <p><code>sudo chmod 666 filename</code></p>

                    <p><?php echo ("This will allow all users to read and write to the file but not execute it.");?></p>

                    <p><?php echo ("In the day to day working of the application certain files are created by the application and stored in the web server document root directory. Files like faxes, images etc. are constantly being uploaded to the application");?>.</p>

                    <p><?php echo ("To facilitate this process the following directory should be writable by the web server (Apache)"); ?>:</p>
                        <ul>
                            <li><strong>/var/www/html/openemr/sites/default/documents</strong> - <?php echo ("where all files etc are stored"); ?></li>
                        </ul>

                    <p><?php echo ("These files need to have owner and user set to the Apache user. In Linux the Apache user and owner are 'www-data' in Ubuntu and Debian, 'apache' in Redhat, Fedora and CentOS , 'nobody' is a generic user and group name that is used by other applications as well");?>.</p>

                    <p><?php echo ("If you are not sure as to what User and Group is used by Apache in your distribution run the following command to find out");?>.</p>

                    <p><code>sudo apachectl -S </code></p>

                    <p><?php echo ("The ownership of directories and all their sub-directories and files can have the Group:User changed to 'www-data' by using the following command");?>:</p>

                    <p><code>sudo chown -R www-data:www-data directory_name</code></p>

                    <p><?php echo ("Should the page display errors related to file or directory writing privileges, fix the permissions and click the 'Check Again' button to continue");?>.</p>

                    <p><?php echo ("Only after the satisfactory completion of this step can the installation proceed");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("REMEMBER: If a feature that is enabled after installation fails to work, especially if it involves uploading files like images etc. to the application, inadequate directory permissions might be the cause for failure. Giving the websever (Apache) the ability to write to those directories might solve the problem");?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo ("CAUTION: If you are upgrading from a previous version of OpenEMR, DO NOT use this script. This script should be used only for a fresh install");?>.</strong></p>

                    <p><?php echo ("More information of what an upgrade is is found here");?>.
                        <a href="#section11"><?php echo ("Click here");?></a>
                    </p>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 1 Select Database Setup"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Data pertaining to the OpenEMR application as well as clinical data is mainly stored in the database");?>.</p>

                    <p><?php echo ("MySQL is generally the database that is used in OpenEMR. MariaDB can be used instead");?>.</p>

                    <p><?php echo ("For all practical purposes, MariaDB is a binary drop in replacement of the same MySQL version (for example MySQL 5.1 -> MariaDB 5.1, MariaDB 5.2 & MariaDB 5.3 are compatible. MySQL 5.5 is compatible with MariaDB 5.5 and also in practice with MariaDB 10.0, MySQL 5.6 is compatible with MariaDB 10.0 and MySQL 5.7 is compatible with MariaDB 10.2)");?>.</p>

                    <p><?php echo ("More information about the differences and similarities between MySQL and MariaDB can be found here");?>.
                        <a href="https://mariadb.com/kb/en/library/mariadb-vs-mysql-compatibility/" rel="noopener"  target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title="MariaDB vs MySQL"></i></a>
                    </p>

                    <p><?php echo ("In Step 1 you are given the choice to either let the setup script create the database and populate it with the necessary tables or you create a blank database and let the setup script write the OpenEMR specific tables");?>.</p>

                    <p><?php echo ("If you have root privileges to the MySQL server then select 'Have setup Create the Database' option and click on 'Proceed to Step 2'");?>.</p>

                    <p><?php echo ("If you chose the 'Have setup Create the Database' option you then have the opportunity to select the collation for any other language listed in the 'UTF-8 Collation' drop-down box in the next step");?>.</p>

                    <p><?php echo ("Collation refers to a set of rules that determine how data is sorted and compared in a database");?>.
                        <a href="https://en.wikipedia.org/wiki/Collation" rel="noopener"  target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title="Collation - Wikipedia"></i></a>&nbsp;
                        <a href="https://database.guide/what-is-collation-in-databases/" rel="noopener"  target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title="Collation in Databases"></i></a>
                    </p>

                    <p><?php echo ("If you are not sure select 'General' i.e. utf8_general_ci");?>.</p>

                    <p><?php echo ("If you have created a blank database, select the 'I Have already Created the Database' option and click Proceed to Step 2");?>.</p>

                    <p><?php echo ("If you are creating a blank database be sure to at least set the collation as utf8_general_ci");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("NOTE: It is vitally important that the proper collation is used when the database is created, if not the setup will fail to create and populate the necessary tables");?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo ("CAUTION: Clicking on 'Proceed to Step 2' may delete or cause damage to existing data on your system. Before you continue please backup your data");?>.</strong></p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 2 Database and OpenEMR Initial User Setup Details"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Lets you fill in the details needed for the setup script to start creating the database and the tables. It will also create the initial user for the OpenEMR application");?>.</p>

                    <p><?php echo ("It is divided into three sections"); ?>:</p>
                        <ul>
                            <li><?php echo ("MySQL Server Details"); ?></li>
                            <li><?php echo ("OpenEMR Initial User Details"); ?></li>
                            <li><?php echo ("Enable 2 Factor Authentication for Initial User"); ?></li>
                        </ul>
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("It is important to understand that two separate users will be created - a user that PHP will use to read and write to the database and an Initial User that will be accessing the OpenEMR application for the first time. By default the OpenEMR Initial User will have full administrator privileges over the entire OpenEMR application");?>.</p>

                    <p><strong><?php echo ("MYSQL SERVER DETAILS"); ?> :</strong></p>

                    <p><?php echo ("These are details required to create the database that will hold the data for OpenEMR");?>.</p>

                    <p><?php echo ("To help you with filling out the various boxes additional help is available by hovering over the labels of each box and clicking on the dark blue question mark icon");?>.</p>

                    <p><?php echo ("The 'Server Host' field specifies the location of the MySQL server. If you run MySQL and Apache/PHP on the same server, then leave this as 'localhost'");?>.</p>

                    <p><?php echo ("If MySQL and Apache/PHP are on separate servers, then enter the IP address (or host name) of the server running MySQL");?>.</p>

                    <p><?php echo ("The 'Server Port' field specifies the port to use when connecting to the MySQL server over TCP/IP.  This should be left as 3306 unless you changed it in your MySQL configuration");?>.</p>

                    <p><?php echo ("The 'Database Name' field is the database where OpenEMR data will reside");?>.</p>

                    <p><?php echo ("If you had previously selected the 'Have setup Create the Database' option  a database with the name in the database field will be created, along with the user specified in 'Login Name'");?>.</p>

                    <p><?php echo ("The default value is 'openemr' with a default login name of 'openemr', you must supply a password for this user usually it is 'openemr' for the sake of simplicity");?>.</p>

                    <p><?php echo ("You can change any or all three values to those of your choice");?>.</p>

                    <p><?php echo ("Note that setup will not accept a password that is not at least one (12) characters in length");?>.</p>

                    <p><?php echo ("Please remember that this user is for PHP to read and write data to the database that will be created. It is not the user that will login to the OpenEMR application");?>.</p>

                    <p><?php echo ("The 'Name for Root Account' field will only appear if setup is creating the database.  It is the name of the MySQL root account. For localhost, it is usually ok to leave it as 'root'");?>.</p>

                    <p><?php echo ("The 'Root Password' field will likewise only appear if setup is creating the database.  It is the password of your existing root user, and is used to acquire the privileges to create the new database and user. By default the root password for MySQL is set empty. However if you have set a root password when MySQL was installed in your system then it needs to be entered here");?>.</p>

                    <p><?php echo ("For increased security a password for the MySQL server root account should be set");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("NOTE: You cannot set the root password by entering it here. If an incorrect root password is entered here the installation will stop as the database to hold the OpenEMR data cannot be created by the 'root' user");?>.</p>

                    <p><?php echo ("The 'UTF-8 Collation' field is the collation setting for the MySQL database");?>.</p>

                    <p><?php echo ("If the language you are planning to use in OpenEMR is in the menu, then you can select it");?>.</p>

                    <p><?php echo ("Otherwise, just select 'General' i.e. utf8_general_ci");?>.</p>

                    <p><?php echo ("Choosing 'None' will cause the installation to fail");?>.</p>

                    <p><?php echo ("'User Host Name' - If you run Apache/PHP and MySQL on the same computer/server, then leave this as 'localhost'");?>.</p>

                    <p><?php echo ("If they are on separate computers, then enter the IP address of the computer running Apache/PHP");?>.</p>

                    <p><strong><?php echo ("OPENEMR INITIAL USER DETAILS"); ?> :</strong></p>

                    <p><?php echo ("The details entered in this section will help in the creation of the first OpenEMR user");?>.</p>

                    <p><?php echo ("This user will be able to interact with the OpenEMR application just like every other user thereafter");?>.</p>

                    <p><?php echo ("The 'Initial User Login Name' is the user name of the first user, which is what they will use to login.  Limit this to one word only");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("NOTE: A random 12 letter user name will be displayed, keep it if you like it, otherwise enter a 12 letter username containing letters and numbers");?>.</p>

                    <p><?php echo ("The 'Initial User Password' will be the password for this user");?>.</p>

                    <p><?php echo ("The 'Initial User's First and Last Name' as appropriate");?>.</p>

                    <p><?php echo ("The 'Initial Group' is the first group, basically name of the practice, that will be created");?>.</p>

                    <p><?php echo ("A user may belong to multiple groups, which again, can be altered on the user administration page");?>.</p>

                    <p><?php echo ("It is suggested that no more than one group per office be used");?>.</p>

                    <p><strong><?php echo ("ENABLE 2 FACTOR AUTHENTICATION FOR INITIAL USER"); ?> :</strong></p>

                    <p><strong><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("It is easy to setup and FREE to use");?>.</strong></p>

                    <p><?php echo ("This is an optional feature that improves security by reducing the chance of unauthorized login");?>.</p>

                    <p><?php echo ("2FA requires the user to authenticate themselves to the application using two forms of authentication - something they know - i.e. password and something they have i.e. an app or key which will generate a unique code");?>.</p>

                    <p><?php echo ("By requiring a second form of authentication that only the user possesses the chance of a third party being able to masquerade as the user is greatly reduced");?>.</p>

                    <p><?php echo ("It is strongly recommended if the instance of OpenEMR is accessible from the internet");?>.</p>

                    <p><?php echo ("OpenEMR uses two methods of 2 Factor Authentication - 2FA");?>.</p>
                         <ul>
                            <li><?php echo ("TOTP - Time-Based One-Time Password"); ?></li>
                            <li><?php echo ("U2F - Universal 2nd Factor"); ?></li>
                        </ul>

                    <p><?php echo ("In the initial setup the only available option is TOTP");?>.</p>

                    <p><?php echo ("For TOTP to work a unique secret key must be shared between OpenEMR and the user");?>.</p>

                    <p><?php echo ("This key is generated by the setup script and is presented to the user in the form of a QR code");?>.</p>

                    <p><?php echo ("The QR code also contains the user name and needs to be captured by the user on to their mobile device using an authenticator app");?>.</p>

                    <p><?php echo ("Once this is done the shared secret key that is unique for each user should only exist in OpenEMR and on the user's authenticator app");?>.</p>

                    <p><?php echo ("Authenticator apps are available for both ios and android devices at their respective app stores and is free to use");?>.</p>

                    <p><?php echo ("The basic purpose of the authenticator app is to generate a 20 bytes (160 bits) code encoded in base32 using a secure hash function, SHA-1, and is called HMAC-SHA1 (Hash-based Message Authentication Code)");?>.</p>

                    <p><?php echo ("It does so by using an algorithm to combine the current UNIX time with the secret key to generate an ever changing unique key");?>.</p>

                    <p><?php echo ("For ease of use it is rendered as a unique 6 digit number");?>.</p>

                    <p><?php echo ("All TOTP authenticator apps use the same algorithm and secure hash function - HMAC-SHA1 to generate the unique key");?>.</p>

                    <p><?php echo ("An important concept to grasp is that once the secret key delivered via the QR code is captured by the authenticator app there is no further communication between the app and OpenEMR, each will use the current UNIX time and their copy of the user-specific shared secret key to generate the same unique 6 digit number");?>.</p>

                    <p><?php echo ("These numbers need to match to successfully authenticate the user");?>.</p>

                    <p><?php echo ("Once this feature is enabled you will be required to type in the 6 digit key at each login");?>.</p>

                    <p><?php echo ("After login you can choose to clear the key by deleting it under Miscellaneous > MFA Authentication");?>.</p>

                    <p><?php echo ("Those with administrator privileges can also go to Administration > Users and check Clear 2FA checkbox and Click Save");?>.</p>

                    <p><?php echo ("After login you can choose to add U2F key authentication or change to using U2F key authentication exclusively");?>.</p>

                    <p><?php echo ("A more detailed explanation is available in the help file for Miscellaneous > MFA Authentication");?>.</p>

                    <p><?php echo ("Click on 'Create DB and User' button to continue");?>.</p>

                    <p><?php echo ("Wait patiently as the script has to create the database, write the tables, create and authenticate the initial user");?>.</p>

                    <p><?php echo ("When complete it will automatically take you to the next step");?>.</p>

                </div>
            </div>
            <div class= "row" id="section4">
                 <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 3 Creating Database and First User"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Upon completion the setup script would have successfully connected to the MySQL server, created the database that will hold data from OpenEMR, created and authenticated the 'user' that PHP will use to read and write to the database");?>.</p>

                    <p><?php echo ("It will then create the tables in the database to hold the data and complete additional tasks needed to progress with the installation");?>.</p>

                    <p><?php echo ("It will end by adding the 'Initial User' or the first user who will able to login to OpenEMR");?>.</p>

                    <p><?php echo ("Click on 'Proceed to Step 4' to continue");?>.</p>

                    <p><?php echo ("The script will continue to execute and upon successful completion will automatically take you to Step 4");?>.</p>

                </div>
            </div>
            <div class= "row" id="section5">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 4 Creating and Configuring Access Control List"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Upon clicking 'Proceed to Step 4' in the previous step the script will start to create and configure the tables needed for php General Access Control List (phpGACL) to function");?>.</p>

                    <p><?php echo ("In OpenEMR access to various parts of the program are granted to users on a need to know basis using Access Control Lists");?>.</p>

                    <p><?php echo ("These lists are used to determine who can access what in OpenEMR");?>.</p>

                    <p><?php echo ("Upon successful completion several tables with the prefix of gacl_ will be added to the OpenEMR database");?>.</p>

                    <p><?php echo ("The default installation will give selective access to parts of the application to users belonging to particular groups like  Accounting, Administrators, Clinicians, Emergency Login, Front Office and Physicians");?>.</p>

                    <p><?php echo ("Click on the 'Proceed to Step 5' button");?>.</p>
                </div>
            </div>
            <div class= "row" id="section6">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 5 Configure PHP"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Clicking on 'Proceed to Step 5' in the previous step will cause the script to look at the the current system settings for PHP and will display a table with the 'Current Value' and the 'Required Value' to make OpenEMR function smoothly");?>.</p>

                    <p><?php echo ("These values are in a file called php.ini");?>.</p>

                    <p><?php echo ("The php.ini file is the default configuration file for running applications that require PHP");?>.</p>

                    <p><?php echo ("It is used to control variables such as upload sizes, file timeouts, and resource limits etc");?>.</p>

                    <p><?php echo ("The location of the php.ini file will be displayed in this step");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("NOTE: You will have to manually edit the php.ini file to enact these changes");?>.</p>

                    <p><?php echo ("It is strongly suggested that you print these instructions and edit the php.ini file at the end of the installation process");?>.</p>

                    <p><?php echo ("Setup will continue if you click on the 'Proceed to Step 6' button");?>.</p>
                </div>
            </div>
            <div class= "row" id="section7">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 6 Configure Apache Web Server"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("NOTE: This step also has to be manually carried out");?>.</p>

                    <p><?php echo ("It will ask you to secure three directories containing patient information");?>.</p>

                    <p><?php echo ("They are"); ?>:</p>
                        <ul>
                            <li><strong>/var/www/html/openemr/sites/default/documents</strong> - <?php echo ("where all files etc are stored"); ?></li>
                        </ul>
                    <p><?php echo ("It will also ask you to add some data to the Apache configuration file to make the Zend Framework work better");?>.</p>

                    <p><?php echo ("The Zend Framework is a collection of PHP packages used in OpenEMR to make the application work");?>.</p>

                    <p><?php echo ("The location of the Apache configuration file is dependent on the operating system");?>.</p>

                    <p><?php echo ("In windows, the XAMPP 1.7.0 package places the configuration file at xampp\apache\conf\httpd.conf");?>.</p>

                    <p><?php echo ("On most Linux systems if you installed Apache with a package manager, or it came pre-installed, the Apache configuration file is located in one of these locations");?>:</p>
                        <ul>
                            <li><?php echo ("/etc/apache2/httpd.conf"); ?></li>
                            <li><?php echo ("/etc/apache2/apache2.conf"); ?></li>
                            <li><?php echo ("/etc/httpd/httpd.conf"); ?></li>
                            <li><?php echo ("/etc/httpd/conf/httpd.conf"); ?></li>
                        </ul>

                    <p><?php echo ("On macOS Server >= 10.8 Mountain Lion, the location of the Apache configuration file varies");?>.</p>

                    <p><?php echo ("Not using websites /webservices	- default (/etc/apache2/httpd.conf)");?>.</p>

                    <p><?php echo ("macOS Server 4 - webservices - /Library/Server/Web/Config/apache2/httpd_server_app.conf");?>.</p>

                    <p><?php echo ("macOS Server 5 websites -	/Library/Server/Web/Config/apache2/server-httpd.conf");?>.</p>

                    <p><?php echo ("To configure Zend and to secure the /documents directory you can paste following to the end of the apache configuration file");?>:</p>

                    <pre>
                    &lt;Directory &quot;openemrwebroot&quot;&gt;
                    &nbsp;&nbsp;AllowOverride FileInfo
                    &nbsp;&nbsp;Require all granted
                    &lt;/Directory&gt;
                    &lt;Directory &quot;openemrwebroot/sites&quot;&gt;
                    &nbsp;&nbsp;AllowOverride None
                    &lt;/Directory&gt;
                    &lt;Directory &quot;openemrwebroot/sites/*/documents&quot;&gt;
                    &nbsp;&nbsp;Require all denied
                    &lt;/Directory&gt;
                    </pre>

                    <p><?php echo ("The 'openemrwebroot' is the directory in which the OpenEMR scripts exist on the web server");?>.</p>

                    <p><?php echo ("The script will automatically list the 'openemrwebroot' upon reaching Step 6 Configure Apache Web Server");?>.</p>

                    <p><?php echo ("You can cut and paste this code block into the Apache configuration file");?>.</p>

                    <p><?php echo ("Click on 'Proceed to Select a Theme' to continue");?>.</p>
                </div>
            </div>
            <div class= "row" id="section8">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Step 7 Select a Theme"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("This step will let you select the theme for OpenEMR");?>.</p>

                    <p><?php echo ("An image of the OpenEMR login page using the theme that will be installed will be shown under the 'Current Theme' section");?>.</p>

                    <p><?php echo ("Beneath it will be two check-boxes - 'Show More Themes' or 'Keep Current'");?>.</p>

                    <p><?php echo ("To proceed further you will have to select one of these options");?>.</p>

                    <p><?php echo ("Checking 'Show More Themes' will display scaled down images of the login screen of all available themes");?>.</p>

                    <p><?php echo ("Select any theme you want and click OK to apply the selection");?>.</p>

                    <p><?php echo ("Check 'Keep Current' if you want to keep the current default theme");?>.</p>

                    <p><?php echo ("Click on 'Proceed to Final Step' to complete the installation");?>.</p>
                </div>
            </div>
            <div class= "row" id="section9">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Final step - Success!"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Congratulations! OpenEMR is now installed");?>.</p>

                    <p><?php echo ("The 'Initial User' name and password will be displayed");?>.</p>

                    <p><?php echo ("The selected theme that was installed will also be displayed");?>.</p>

                    <p><?php echo ("These settings can be subsequently changed by logging into OpenEMR and going to "); ?>:</p>
                        <ul>
                            <li><?php echo (" Administration > Users"); ?></li>
                            <li><?php echo ("Administration > Globals > Appearance > General Theme"); ?></li>
                        </ul>

                    <p><?php echo ("If you edited the PHP or Apache configuration files during this installation process, restart your Apache server before following below OpenEMR link");?></p>

                    <p><?php echo ("In Linux use the following command");?>:</p>

                    <p><?php echo "<code>sudo apachectl -k restart</code>";?></p>

                    <p><?php echo ("'Click here to start using OpenEMR' will take you to the login page");?>.</p>

                    <p><?php echo ("Take a moment to note the URL of the OpenEMR application in the address bar of the browser");?>.</p>

                    <p><?php echo ("A 'OpenEMR Product Registration' window will popup");?>.</p>

                    <p><?php echo ("Register your installation with OEMR to receive important notifications, such as security fixes and new release announcements");?>.</p>

                    <p><?php echo ("Before you start using the application for clinical work you need to do the following");?>:</p>
                        <ul>
                            <li><?php echo ("Customize your settings under Administration > Globals"); ?></li>
                            <li><?php echo ("Setup the clinic"); ?> <a href="https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#Installation_Manuals" rel="noopener"  target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a></li>
                            <li><?php echo ("Secure and harden the application"); ?> <a href="https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#Security_.2F_Hardening" rel="noopener"  target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a></li>
                            <li><?php echo ("Implement a backup and recovery strategy"); ?></li>
                        </ul>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("It is vitally important to have a backup and recovery strategy in place");?>.</p>

                    <p><?php echo ("It is not just a safeguard against the the possibility of application failure but being prepared for the time when it will");?>.</p>

                    <p><?php echo ("To be successful the backup should be automated, periodic or continuous and be able to successfully restore data within an appropriate time frame");?>.</p>

                    <p><?php echo ("A robust backup and disaster recovery policy and solution is essential to safeguard against catastrophic data loss and the attendant medical, financial and legal ramifications of such loss");?>.</p>

                </div>
            </div>
            <div class= "row" id="section10">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Life cycle of the OpenEMR application"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                     <p><?php echo ("There are three stages in the life cycle of the application"); ?>:</p>
                        <ul>
                            <li><?php echo ("Initial installation which is accomplished by the setup script where a new OpenEMR application with a clean database is created"); ?></li>
                            <li><?php echo ("Patching the system - As bug and security issues are noted and corrected the application will need to be periodically updated by applying patches"); ?></li>
                            <li><?php echo ("Upgrade - When a major new version is released then an application upgrade is needed"); ?></li>
                        </ul>
                </div>
            </div>
            <div class= "row" id="section11">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Upgrading"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("The upgrade process differs from a fresh installation in a crucial aspect, unlike a fresh install the data, especially the clinical data is retained");?>.</p>

                    <p><?php echo ("Upgrading is a five step process");?>.</p>

                    <p><strong><?php echo ("Step 1");?></strong> - <?php echo ("Back up the old OpenEMR files as well as the data before you embark on this endeavor");?>.</p>

                    <p><strong><?php echo ("Step 2");?></strong> - <?php echo ("Replace the old openemr directory with the new version in the web server document root directory");?>.</p>

                    <p><strong><?php echo ("Step 3");?></strong> - <?php echo ("Copy the settings from the following old OpenEMR files to the new configuration files");?>.</p>

                    <p><?php echo ("Do not simply copy and replace entire files");?>.</p>

                    <p><?php echo ("The files whose data needs to be updated are"); ?>:</p>
                        <ul>
                            <li><?php echo ("openemr/sites/default/sqlconf.php"); ?></li>
                            <li><?php echo ("openemr/sites/default/config.php"); ?></li>
                        </ul>

                    <p><?php echo ("Set the \$config variable in sqlconf.php (found near bottom of file within bunch of slashes) to 1 (\$config = 1;)");?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <?php echo ("IMPORTANT:  If you do not do this the setup script for a fresh install will be executed when you log in to OpenEMR with the potential for massive data loss");?>.</p>

                    <p><strong><?php echo ("Step 4");?></strong> - <?php echo ("The following directory contains patient information should however be copied from the old version to the new version"); ?>:</p>
                        <ul>
                            <li><?php echo ("openemr/sites/default/documents - files, documents, images etc"); ?></li>
                        </ul>

                    <p><?php echo ("If there are other files that you have customized, then you will also need to customize those files in the current version");?>.</p>

                    <p><?php echo ("If you have customized files that are unique to your installation copy then and make sure that they will work with the new upgraded version of OpenEMR");?>.</p>

                    <p><strong><?php echo ("Step 5");?></strong> - <?php echo ("The final step is to upgrade the database");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("Upgrading the database will retain all the old data and add new tables, if any, to the existing database and new fields to existing tables so that the upgraded version can function seamlessly with the old data");?>.</p>

                    <p><?php echo ("Run the sql_upgrade.php script from your web browser (for example http://openemr.location/sql_upgrade.php)");?>.</p>

                    <p><?php echo ("It will prompt you to select the old release number, and will display the SQL commands issued as the upgrade occurs");?>.</p>

                    <p><?php echo ("The importance of backing up the data and the OpenEMR script files will be apparent if your production version does not work properly after you run the sql_upgrade.php script");?>.</p>

                    <p><?php echo ("Simply replacing the new OpenEMR script files with the old ones might not be enough as the sql_upgrade.php script would have modified the database");?>.</p>

                    <p><?php echo ("To revert to the previous state you will have to replace the upgraded database with the old database in addition to replacing the new OpenEMR script files with the old ones");?>.</p>
                </div>
            </div>
            <div class= "row" id="section12">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo ("Multi Site Installation"); ?><a href="#main-list"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo ("Allows you to install and run multiple sites from the same webserver"); ?>.</p>

                    <p><?php echo ("A brief recap some pertinent facts about OpenEMR"); ?>:</p>

                        <ul>
                            <li><?php echo ("It is a three-tier web database application, user tier, data tier and middle tier"); ?></li>
                            <li><?php echo ("The middle tier contains the PHP scripts that the webserver (Apache) uses to interact with the data tier and the user tier. It has to be in the webserver's openemr document root directory"); ?></li>
                            <li><?php echo ("The data tier stores most of the data in a database, most commonly MySQL or MariaDB"); ?></li>
                            <li><?php echo ("Some data (images, electronic remittance advice files, transmitted electronic batches for various kinds etc.) are stored in directories in the openemr document root folder of the webserver"); ?></li>
                            <li><?php echo ("For PHP to interact with the database a virtual user is needed and created by the setup script"); ?></li>
                            <li><?php echo ("For Apache/PHP to be able to read and write data to the directories containing patient information in the webserver openemr document root directory, the webserver needs permission to read and write to those directories"); ?></li>
                            <li><?php echo ("The setup script also creates an initial user with full administrative privileges to the OpenEMR application at the time of initial setup"); ?></li>
                        </ul>

                    <p><?php echo ("The multisite setup will share the middle tier scripts between all instances and have a unique data tier for each instance"); ?>.</p>

                    <p><?php echo ("This means creating a new database for each site as well as creating a place in the webserver openemr document root directory to hold the site-specific non-database data"); ?>.</p>

                    <p><?php echo ("The place where the non-database data for each site is stored is in a directory called 'sites' in the OpenEMR document root directory of the initial single site installation"); ?>.</p>

                    <p><?php echo ("To initiate the multisite process you need to first have a single site or 'default' installation setup"); ?>.</p>

                    <p><?php echo ("The OpenEMR scripts from this installation will be shared with all subsequent sites created using the multisite module"); ?>.</p>

                    <p><?php echo ("The non-database data for each individual site will reside in the 'sites' directory under a sub-directory bearing the name of the site, i.e if your first multiuser site was called 'multi1' then the setup script will create a directory by the name of 'multi1' in the 'sites' directory"); ?>.</p>

                    <p><?php echo ("This directory will contain a directory that will hold unique site-specific patient information, 'documents', as well as the site-specific files 'config.php' and 'sqlconf.php'"); ?>.</p>

                    <p><?php echo ("In addition it will contain other directories - 'images' and 'LBF', and files - faxtitle.eps , referral_template.html, clickoptions.txt, faxcover.txt and statement.inc.php which will not contain patient information but can be customized as per the site requirement"); ?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo ("In order to successfully copy these directories and files the 'sites' directory in the initial installation must have the user:group set to that of the webserver"); ?>.</p>

                    <p><strong><?php echo ("PRE INSTALLTION"); ?> :</strong></p>

                    <p><?php echo ("To initiate the multisite process you need to install a single site or 'default' site"); ?>.</p>

                    <p><?php echo ("Edit the setup.php file of the default installation"); ?>.</p>

                    <p><?php echo ("Change the false in <code>\$allow_multisite_setup = false;</code> to <code>true;</code>"); ?></p>

                    <p><?php echo ("Change the false in <code>\$allow_cloning_setup = false;</code> to <code>true;</code>"); ?></p>

                    <p><?php echo ("Change the user and group of the 'sites' directory in the initial OpenEMR installation to that of the webserver"); ?>.</p>

                    <p><?php echo ("In Ubuntu/Debian <code>cd</code> into the openemr directory in webserver document root directory and execute the following command:"); ?>.</p>

                    <p><code>sudo chown -R www-data:www-data sites</code></p>

                    <p><strong><?php echo ("INSTALLTION"); ?> :</strong></p>

                    <p><?php echo ("If you are accessing the OpenEMR application from the computer/server  that it has been installed in type <code>http://localhost/openemr/admin.php</code> in the address bar of a browser. NOTE: This assumes that the OpenEMR files are in a directory called 'openemr', if not change the name to reflect the OpenEMR root directory. If you are accessing it from another computer substitute the localhost name with the IP address or domain name of the OpenEMR installation ");?>.</p>

                    <p><?php echo ("This will bring up the the 'OpenEMR Site Administration' page"); ?>.</p>

                    <p><?php echo ("This page will list in a tabular form all the sites that have been installed"); ?>.</p>

                    <p><?php echo ("Initially it will have only the 'default' site installed"); ?>.</p>

                    <p><?php echo ("The following details are displayed "); ?>:</p>

                        <ul>
                            <li><?php echo ("Site ID  - unique ID of the site, should be one word, preferably lower case"); ?></li>
                            <li><?php echo ("DB Name - the name of the database for that site"); ?></li>
                            <li><?php echo ("Site Name - by default it will be OpenEMR, once the site is setup this can be changed for that instance by going to Administration > Appearance > Application Title"); ?></li>
                            <li><?php echo ("Version - the version of the current installation"); ?></li>
                            <li><?php echo ("Is Current - database, access control list version and patch status is up to date"); ?></li>
                            <li><?php echo ("Log In - That will let you login to the particular site"); ?></li>
                        </ul>

                    <p><?php echo ("Click on the 'Add New Site' button to proceed"); ?>.</p>

                    <p><?php echo ("It will take you to the 'Optional Site ID Selection' page"); ?>.</p>

                    <p><?php echo ("Read the instructions and enter a new site ID and click on 'Continue'"); ?>.</p>

                    <p><?php echo ("It will take you to the 'Pre Install - Checking File and Directory Permissions' page"); ?>.</p>

                    <p><?php echo ("As with the initial single site installation it will check for proper permissions"); ?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <?php echo ("IMPORTANT: Make sure that the webserver has read/write permissions to the entire 'sites' directory"); ?>.</p>

                    <p><?php echo ("Clicking on 'Proceed to Step 1' will take you to the 'Step 1 - Select Database Setup' page"); ?>.</p>

                    <p><?php echo ("As with the single site installation you can either choose 'Have setup create the database' or 'I have already created the database' option and click on 'Proceed to Step 2'"); ?>.</p>

                    <p><?php echo ("In the 'Step 2 - Database and OpenEMR Initial User Setup Details' you will have to fill in the required details for the MySQL server and the OpenEMR Initial User"); ?>.</p>

                    <p><?php echo ("You will notice two additional fields on this page, 'Source Site' and 'Clone Source Database'"); ?>.</p>

                    <p><?php echo ("The 'Source Site' drop-down box will list all the sites that have been installed using the multisite module, if only the initial single site was installed it will have a single value of 'default'"); ?>.</p>

                    <p><?php echo ("Select this or any value in the drop-down box to select a source site"); ?>.</p>

                    <p><?php echo ("If you check the 'Clone Source Database' check-box then the database of the source site selected in the 'Source Site' step will be cloned to create the the database of this new site"); ?>.</p>

                    <p><?php echo ("Selecting the 'Clone Source Database' check-box will also hide the 'OpenEMR Initial User Details' section as the details of the initial user created when the source site already installed will be in the cloned database"); ?>.</p>

                    <p><?php echo ("If you do not check the 'Clone Source Database' check-box a new database with a new initial user will be created"); ?>.</p>

                    <p><?php echo ("The files in the 'sites' directory for the value selected in the 'Source Site' will be copied to the sub-directory in the 'sites' directory for the new site being created"); ?>.</p>

                    <p><?php echo ("Click on 'Create DB and User' button to continue"); ?>.</p>

                    <p><?php echo ("Upon successful completion it will take you to the 'Step 3 Creating Database and First User' page"); ?>.</p>

                    <p><?php echo ("Click on 'Proceed to Step 4' to continue to 'Step 4 Creating and Configuring Access Control List'"); ?>.</p>

                    <p><?php echo ("The script will start to create and configure the tables needed for php General Access Control List (phpGACL) to function"); ?>.</p>

                    <p><?php echo ("Click on the 'Proceed to Step 5' button to open  'Step 5 Configure PHP' page"); ?>.</p>

                    <p><?php echo ("If you have already configured the php.ini file during the setup of the 'default' installation you do not need to do anything further"); ?>.</p>

                    <p><?php echo ("Click on the 'Proceed to Step 6' button to go to 'Step 6 Configure Apache Web Server' page"); ?>.</p>

                    <p><?php echo ("The setup script will automatically generate a code block"); ?>.</p>

                    <p><?php echo ("It needs to be cut and pasted into the Apache configuration file for the webserver being used if you did not do it during the intial 'defalt' site installation"); ?>.</p>

                    <p><?php echo ("Click on 'Proceed to Select a Theme' to go to 'Step 7 Select a Theme' page"); ?>.</p>

                    <p><?php echo ("This will let you select a theme for this installation"); ?>.</p>

                    <p><?php echo ("Click on 'Proceed to Final Step' to complete the installation"); ?>.</p>

                    <p><?php echo ("To access this site you will have to append <code>?siteid=site ID</code> to the openemr url"); ?>.</p>

                    <p><?php echo ("Advantages of the multisite module"); ?>.</p>

                    <p><?php echo ("To have multiple unique sites that will not share data between sites"); ?>.</p>

                    <p><?php echo ("Having a common middle tier would mean that scripts need to be updated only once during patching/upgrading"); ?>.</p>

                    <p><?php echo ("The database of each instance will need to be updated/upgraded individually"); ?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <?php echo ("IMPORTANT: When using the multisite module to create multiple sites the non-database data located in the 'sites' directory is copied into the newly created sub-directory for that site in the 'sites' directory"); ?>.</p>

                    <p><?php echo ("Its relevance will be apparent in the following scenario - A 'default' site is created and two additional sites are created using the 'default' as the source site. All three sites are functional. Say after two months you want to add another site, if you choose the 'default' or any of the other two sites as the source site you will in effect be copying the non-database patient data from that instance located in the 'sites' directory to the sub-directory of the new instance"); ?>.</p>

                    <p><?php echo ("Similar care needs to be taken when cloning the database. It is of benefit to first do a 'default' install, log in to OpenEMR, activate features that will be common to future sites, like ICD10, CPT codes, setting up the fax services etc, and then clone the database. This will save time in having to customize everything for each installation"); ?>.</p>

                    <p><?php echo ("One thing you need to be aware of is that the initial user and password will be the same for all instances if you clone the database, you will need to change the initial user password for each instance to prevent cross-site data accessibility"); ?>.</p>

                    <p><?php echo ("If you want to retain the ability to add more sites at a later time using the multisite module it is suggested to create the 'default' site and keep it without patient data and always use it as the source site to allow you can to create multiple sites without compromising patient data"); ?>.</p>

                    <p><?php echo ("Great care needs to be exercised when using the multisite module to prevent inadvertent cross-site transfer of patient data"); ?>.</p>

                    <p><?php echo ("To prevent inadverent use of the multisite module re-edit the setup.php file of the default installation"); ?>.</p>

                    <p><?php echo ("Change the true in <code>\$allow_multisite_setup = true;</code> to <code>false;</code>"); ?></p>

                    <p><?php echo ("Change the true in <code>\$allow_cloning_setup = true;</code> to <code>false;</code>"); ?></p>

                </div>
            </div>

        </div><!--end of container div-->
        <script>
        // better script for tackling nested divs
           $('.show_hide').click(function() {
                var elementTitle = $(this).prop('title');
                var hideTitle = '<?php echo ('Click to Hide'); ?>';
                var showTitle = '<?php echo ('Click to Show'); ?>';
                //$('.hideaway').toggle('1000');
                $(this).parent().parent().closest('div').children('.hideaway').toggle('1000');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                    $(this).toggleClass('fa-eye-slash fa-eye');
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                    $(this).toggleClass('fa-eye fa-eye-slash');
                }
                $(this).prop('title', elementTitle);
            });
        </script>
    </body>
</html>
