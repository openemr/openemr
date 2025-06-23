<?php

/**
 * This script is for automatic installation and configuration
 *   of OpenEMR.
 *
 * This script is meant to be run as php command line (php-cli),
 *   and needs to be first activated by removing the 'exit' line
 *   at top (via sed command).
 *
 * To activate script, need to comment out the exit command at top
 *   of script.
 *
 * Command ( Note that the ordering and number of custom settings
 *           that can be sent is flexible ):
 *     php -f iuser=[iuser] iuname=[iuname] iuserpass=[iuserpass] igroup=[igroup]
 *       server=[server] loginhost=[loginhost] port=[port] root=[root] rootpass=[rootpass]
 *       login=[login] pass=[pass] dbname=[dbname] collate=[collate] site=[site]
 *       source_site_id=[source_site_id] clone_database=[clone_database]
 *
 *   Description of settings (default value in parenthesis):
 *     iuser      -> initial user login name (admin)
 *     iuname     -> initial user last name (Administrator)
 *     iuserpass  -> initial user password (pass)
 *     igroup     -> practice group name (Default)
 *     server     -> mysql server (localhost)
 *     loginhost  -> php/apache server (localhost)
 *     port       -> MySQL port (3306)
 *     root       -> MySQL server root username (root)
 *     rootpass   -> MySQL server root password ()
 *     login      -> username to MySQL openemr database (openemr)
 *     pass       -> password to MySQL openemr database (openemr)
 *     dbname     -> MySQL openemr database name (openemr)
 *     collate    -> collation for mysql (utf8_general_ci)
 *     site       -> location of this instance in sites/ (default)
 *     source_site_id -> location of instance to clone and mirror ()
 *                         Advanced option of multi site module to allow cloning/mirroring of another local site.
 *     clone_database -> if set to anything, then will clone database from source_site_id ()
 *                         Advanced option of multi site module to allow cloning/mirroring of another local database.
 *     no_root_db_access -> if set to anything, will use pre-created and pre-configured login/pass/dbname and
 *                             will disable cloning / migration since that generally requires root access to the db
 *     development_translations -> If set to anything, will then download and use the development set (updated daily)
 *                                   of translations (indirectly) from the github repository.
 *
 *     Examples of use:
 *     1) Install using default configuration settings
 *          php -f InstallerAuto.php
 *     2) Provide root sql user password for installation
 *        (otherwise use default configuration settings)
 *          php -f InstallerAuto.php rootpass=howdy
 *     3) Provide root sql user password and openemr sql user password
 *        (otherwise use default configuration settings)
 *          php -f InstallerAuto.php rootpass=howdy pass=hey
 *     4) Provide sql user settings and openemr user settings
 *        (otherwise use default configuration settings)
 *          php -f InstallerAuto.php rootpass=howdy login=openemr2 pass=hey dbname=openemr2 iuser=tom iuname=Miller iuserpass=heynow
 *     5) Create mutli-site (note this is very advanced usage)
 *          a. First create first installation
 *            php -f InstallerAuto.php
 *          b. Can create an installation that duplicates 'default' site but not the database
 *            php -f InstallerAuto.php login=openemr2 pass=openemr2 dbname=openemr2 site=default2 source_site_id=default
 *          c. Or can create an installation that duplicates 'default' site and database
 *             php -f InstallerAuto.php login=openemr2 pass=openemr2 dbname=openemr2 site=default2 source_site_id=default clone_database=yes
 *          d. Can continue installing new instances as needed ...
 *             php -f InstallerAuto.php login=openemr3 pass=openemr3 dbname=openemr3 site=default3 source_site_id=default clone_database=yes
 *     6) Provide pre-created database and restricted privilege user access credentials - example from Planettel.com.sg Proxmox OpenVZ Template
 *        (otherwise use default configuration settings - do not use for cloning / migration)
 *          php -f /var/www/openemr/contrib/util/installScripts/InstallerAuto.php no_root_db_access=1 iuserpass=oemr123 login=oemrusr pass=${UPASSWD} > /dev/null 2>&1
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2010-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This exit is to avoid malicious use of this script.
exit;

// Include standard libraries/classes
require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

// Set up default configuration settings
$installSettings = array();
$installSettings['iuser']                    = 'admin';
$installSettings['iuname']                   = 'Administrator';
$installSettings['iuserpass']                = 'pass';
$installSettings['igroup']                   = 'Default';
$installSettings['server']                   = 'localhost'; // mysql server
$installSettings['loginhost']                = 'localhost'; // php/apache server
$installSettings['port']                     = '3306';
$installSettings['root']                     = 'root';
$installSettings['rootpass']                 = 'BLANK';
$installSettings['login']                    = 'openemr';
$installSettings['pass']                     = 'openemr';
$installSettings['dbname']                   = 'openemr';
$installSettings['collate']                  = 'utf8mb4_general_ci';
$installSettings['site']                     = 'default';
$installSettings['source_site_id']           = 'BLANK';
$installSettings['clone_database']           = 'BLANK';
$installSettings['no_root_db_access']        = 'BLANK';
$installSettings['development_translations'] = 'BLANK';

// Collect parameters(if exist) for installation configuration settings
for ($i = 1; $i < count($argv); $i++) {
    $indexandvalue = explode("=", $argv[$i]);
    $index = $indexandvalue[0];
    $value = $indexandvalue[1];
    $installSettings[$index] = $value;
}

// Convert BLANK settings to empty
$tempInstallSettings = array();
foreach ($installSettings as $setting => $value) {
    if ($value == "BLANK") {
        $value = '';
    }

    $tempInstallSettings[$setting] = $value;
}

$installSettings = $tempInstallSettings;


// Install and configure OpenEMR using the Installer class
$installer = new Installer($installSettings);
if (! $installer->quick_install()) {
  // Failed, report error
    echo "ERROR: " . $installer->error_message . "\n";
} else {
  // Successful
    echo $installer->debug_message . "\n";
}
