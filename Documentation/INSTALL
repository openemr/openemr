Installation Instructions
-------------------------------------

OSI Certified Open Source Software

-------------------------------------

I...................Overview of Directories
II..................Unpacking
III.................Setup
IV..................Setting up Access Control
V...................Upgrading

-------------------------------------

I. Overview of Directories

NOTE: Most recent documentation can be found on the online documentation at
http://www.open-emr.org .

accounting    - Contains information and scripts to support SQL-Ledger
contrib       - Contains many user-contributed encounter forms and utilities
custom        - Contains scripts and other text files commonly customized
Documentation - Contains useful documentation
interface     - Contains User Interface scripts and configuration
library       - Contains scripts commonly included in other scripts
sql           - Contains initial database images
gacl          - Contains embedded php-GACL (access controls)

II. Unpacking

NOTE: Most recent documentation can be found on the online documention at
http://www.open-emr.org .

The OpenEMR release archive should be named as follows:

openemr-<version>.tar.gz   -or-   openemr-<version>.zip

To extract the archive, use either of the following commands from the command line:

bash# tar -pxvzf openemr-<version>-release.tar.gz
bash# unzip openemr-<version>-release.tar.gz

Be sure to use the -p flag when using tar, as certain permissions must be preserved.

OpenEMR will be extracted into a directory named openemr.

Alternatively a Debian package may be available as a file named
openemr-<version>.deb.  This may work with some other Debian-like
Linux distributions such as Ubuntu.

III. Setup

NOTE: Most recent documentation can be found on the online documentation at
http://www.open-emr.org .

To run OpenEMR, MySQL and Apache or another PHP-capable webserver must be configured.
To download Apache, visit www.apache.org
For information on how to install MySQL, visit www.mysql.com
PHP may be downloaded from www.php.net

OpenEMR requires a number of webserver and PHP features which may not be
enabled on your system.  These include:

- PHP Index support (ensure that index.php is in your Index path in httpd.conf)
- Session variables
- PHP libcurl support (optional for operation, mandatory for billing)

Copy the OpenEMR folder into the root folder of the webserver. On Mandrake
Linux, for example, use the command:

  bash# mv openemr /var/www/html/

Make sure the webserver is running, and point a web-browser to setup.php located
within the openemr web folder.  If you installed OpenEMR in the root web
directory, the URL would read: http://localhost/openemr/setup.php.
The setup script will step you through the configuration of the OpenEMR.

The first screen of the setup script will ensure that the webserver user
(in linux, often is "apache", "www-data", or "nobody") has write privileges on
certain file and directories. The file is openemr/sites/default/sqlconf.php.
In linux, this can be set by "chmod a+w filename"
command to grant global write permissions to the file. The directory is
openemr/sites/default/documents. In
linux, if the webserver user name is "apache", then the command
"chown -R apache:apache directory_name" will grant global write permissions
to the directories, and we recommend making these changes permanent.
Should the page display errors related to file or directory writing priviledges you
may click the 'Check Again' button to try again (after fixing permissions).

In step 1, you need to tell setup whether it needs to create the database on
its own, or if you have already created the database.  MySQL root privileges will
be required to create a database.

In step 2, you will be presented with a number of fields which specify the MySQL
server details and the openemr directory paths.

The "Server Host" field specifies the location of the MySQL server.  If you
run MySQL and Apache/PHP on the same server, then leave this as 'localhost'.
If MySQL and Apache/PHP are on separate servers, then enter the IP address
(or host name) of the server running MySQL.

The "Server Port" field specifies the port to use when connecting to the MySQL
server over TCP/IP.  This should be left as 3306 unless you changed it in your
MySQL configuration.

The "Database Name" field is the database where OpenEMR will reside.  If you
selected to have the database created for you, this database will be created,
along with the user specified in "Login Name".  If this database exists, setup
will not be able to create it, and will return an error.  If you selected that
you have already created the database, then setup will use the information you
provide to connect to the MySQL server.  Note that setup will not accept a
password that is not at least one (1) character in length.

The "Login Name" field is the MySQL user that will access the OpenEMR database.
If you selected to have the database created for you, this user will be
created.  If you selected that you have already created the database,
then setup will use the information you provide to connect to the MySQL server.

The "Password" field is the password of the user entered into the above
"Login Name" field.  If you selected to have the database created for you,
this user and password  will be created.  If you selected that you have already
created the database, then setup will use the information you provide to connect
to the MySQL server.

The "Name for Root Account" field will only appear if setup is creating the
database.  It is the name of the MySQL root account. For localhost, it is
usually ok to leave it 'root'.

The "Root Pass" field will likewise only appear if setup is creating the
database.  It is the password of your existing root user, and is used to acquire
the privileges to create the new database and user.

The "User Hostname" field will also only appear if setup is creating the
database.  It is the hostname of the Apache/PHP server from which the user
("Login Name") is permitted to connect to the MySQL database.  If you are setting
up MySQL and Apache/PHP on the same computer, then you can use 'localhost'.

The "UTF-8 Collation" field is the collation setting for mysql. If the language
you are planning to use in OpenEMR is in the menu, then you can select it.
Otherwise, just select 'General'. Choosing 'None' is not recommended and
will force latin1 encoding.

The "Initial User" is the username of the first user, which is what they will
use to login.  Limit this to one word only.

The "Initial User Password" is the password of the user entered into the above
"Initial User" field.

The "Initial User's First Name" is the value to be used as their first name.  This
information may be changed in the user administration page.

The "Initial User's Last Name" is the value to be used as their last name.  This
information may be changed in the user administration page.

The "Initial Group" is the first group, basically name of the practice, that
will be created.  A user may belong to multiple groups, which again, can be
altered on the user administration page. It is suggested that no more than
one group per office be used.

Step 3 is where setup will configure OpenEMR.  It will first create the database
and connect to it to create the initial tables.  It will then write the mysql
database configuration to the openemr/sites/default/sqlconf.php file.
Should anything fail during step 3, you may have to remove the existing database or
tables before you can try again. If no errors occur, you will see a "Continue"
button at the bottom.

Step 4 will install and configure the embedded phpGACL access controls.  It
will first write configuration settings to files.  It will then configure the
database.  It will then give the "Initial User" administrator access.
Should anything fail during step 4, you may have to remove the existing database
or tables before you can try again. If no errors occur, you will see a
"Continue" button at the bottom.

Step 5 gives instructions on configuring the PHP.  We suggest you print these
instructions for future reference.  Instructions are given to edit the php.ini
configuration file.  If possible, the location of your php.ini file
will be displayed in green.  If your php.ini file location is not displayed,
then you will need to search for it.  The location of the php.ini file is dependent
on the operating system.  In linux, php.ini is generally found in the /etc/
directory.  In windows, the XAMPP 1.7.0 package places the php.ini file in
the xampp\apache\bin\ directory.  To ensure proper functioning of OpenEMR
you must make sure that settings in the php.ini file include
"short_open_tag = Off", "display_errors = Off", "log_errors = On"
"max_execution_time" set to at least 60, "max_input_time = -1",
"max_input_vars" set to at least 3000, "post_max_size" set to at least 30M,
"upload_max_filesize" set to at least 30M, "memory_limit" set to at least 512M,
and "file_uploads = On".  In order to take full advantage
of the patient documents capability you must make sure that "upload_tmp_dir"
is set to a correct value that will work on your system.

Step 6 gives instructions on configuring the Apache web server.  We suggest
you print these instructions for future reference. Instructions are given to
secure the "openemrwebroot/sites/*/documents"
directory, which contain patient information. This can
be done be either placing pertinent .htaccess files in this directory
or by editing the apache configuration file.  The location of the apache
configuration file is dependent on the operating system.  In linux, you can
type 'httpd -V' on the commandline; the location to the configuration file
will be the HTTPD_ROOT variable plus the SERVER_CONFIG_FILE variable.
In windows, the XAMPP 1.7.0 package places the configuration file at
xampp\apache\conf\httpd.conf. To configure Zend and to secure the /documents,
directory you can paste following to the end of the apache
configuration file (ensure you put full path to directories):
<Directory "openemrwebroot">
AllowOverride FileInfo
</Directory>
<Directory "openemrwebroot/sites">
AllowOverride None
</Directory>
<Directory "openemrwebroot/sites/*/documents">
order deny,allow
Deny from all
</Directory>

The final screen includes some additional instructions and important
information. We suggest you print these instructions for future reference.

Once the system has been configured properly, you may login.  Connect to the
webserver where the files are stored with your web browser.  Login to the system
using the username that you picked (default 'admin' without quotes), and the
password.  From there, select the "Administration"
option, and customize the system to your needs.  Add users and groups as is
needed. For information on using OpenEMR, consult the User Documentation located
in the Documentation folder, the documentation at http://www.open-emr.org, and
the forums at https://community.open-emr.org/.

Reading openemr/sites/default/config.php is a good idea. This file contains some
options to choose from including themes.

To create custom encounter forms, see the files

  openemr/Documentation/3rd Party Form API.txt
  openemr/interface/forms/OpenEMR_form_example-rev2.tar.gz

and read the included documentation and online documentation at www.openemr.org.
Many forms exist in contrib/forms as well as in interface/forms and may be used
as examples.

Other configuration settings are stored under includes/config.php.
Everything should work out of the installation without touching those, but if
you want fax integration you will need to adjust some parameters in that file.

General-purpose fax support requires customization within OpenEMR at
Administration->Globals and custom/faxcover.txt; it also requires
the following utilities:

* faxstat and sendfax from the HylaFAX client package
* mogrify from the ImageMagick package
* tiff2pdf, tiffcp and tiffsplit from the libtiff-tools package
* enscript


IV. Setting Up Access Control

Since OpenEMR version 2.9.0.3, phpGACL access controls are installed and
configured automatically during OpenEMR setup.  It can be administered
within OpenEMR in the admin->acl menu.  This is very powerful
access control software.  To learn more about phpGACL
(see http://phpgacl.sourceforge.net/), recommend reading the phpGACL manual,
the /openemr/Documentation/README.phpgacl file, and the online documentation at
http://www.open-emr.org . Also recommend reading the comments at top of
src/Common/Acl/AclMain.php .

V. Upgrading

NOTE: Most recent documentation can be found on the online documentation at
http://www.open-emr.org .

Be sure to back up your OpenEMR installation and database before upgrading!

Upgrading OpenEMR is currently done by replacing the old openemr directory with
a newer version. And, ensure you copy your settings from the following old openemr
files to the new configuration files (we do not recommend simply
copying the entire files):

  openemr/sites/default/sqlconf.php
    --Also in this sqlconf.php file, set the $config variable (found near bottom
      of file within bunch of slashes) to 1 ($config = 1;)
  openemr/sites/default/config.php

The following directories should be copied from the old version to the
new version:

  openemr/sites/default/documents
  openemr/sites/default/era
  openemr/sites/default/edi
  openemr/sites/default/letter_templates

If there are other files that you have customized, then you will also need
to treat those carefully.

To upgrade the database, run the sql_upgrade.php script from your web
browser (for example http://openemr.location/sql_upgrade.php).  It will
prompt you to select the old release number, and will display the SQL
commands issued as the upgrade occurs.
