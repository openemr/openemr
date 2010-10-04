<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This script is for automatic installation and ocnfiguration
//   of OpenEMR.
// 
// This script is meant to be run as php command line (php-cli),
//  and needs to be first activated by removing the 'exit' line
//  at top (via sed command).
//
// Two modes include:
//
//   1) Generic (no parameters); uses default configuration settings.
//     php -f InstallerAuto.php
//
//   2) Custom; can send configuration setting(s) via command line. Note 
//      that the ordering and number of custom settings that can be sent
//      is flexible.
//     php -f iuser=[iuser] iuname=[iuname] igroup=[igroup] server=[server]
//       loginhost=[loginhost] port=[port] root=[root] rootpass=[rootpass]
//       login=[login] pass=[pass] dbname=[dbname] collate=[collate] site=[site]
//
//   Examples:
//     php -f InstallerAuto.php
//     php -f InstallerAuto.php rootpass=howdy pass=hey
//
//   Description of settings (default value in parenthesis):
//     iuser      -> initial user login name (admin)
//     iuname     -> initial user last name (Administrator)
//     igroup     -> practice group name (Default)
//     server     -> mysql server (localhost)
//     loginhost  -> php/apache server (localhost)
//     port       -> MySQL port (3306)
//     root       -> MySQL server root username (root)
//     rootpass   -> MySQL server root password ()
//     login      -> username to MySQL openemr database (openemr)
//     pass       -> password to MySQL openemr database (openemr)
//     dbname     -> MySQL openemr database name (openemr)
//     collate    -> collation for mysql (utf8_general_ci)
//     site       -> location of this instance in sites/ (default)
//

// This exit is to avoid malicious use of this script.
exit;

require_once(dirname(__FILE__).'/../../../library/classes/Installer.class.php');

// Set up default configuration settings
$installSettings = array();
$installSettings['iuser'] = 'admin';
$installSettings['iuname'] = 'Administrator';
$installSettings['igroup'] = 'Default';
$installSettings['server'] = 'localhost'; // mysql server
$installSettings['loginhost'] = 'localhost'; // php/apache server
$installSettings['port'] = '3306';
$installSettings['root'] = 'root';
$installSettings['rootpass'] = 'BLANK';
$installSettings['login'] = 'openemr';
$installSettings['pass'] = 'openemr';
$installSettings['dbname'] = 'openemr';
$installSettings['collate'] = 'utf8_general_ci';
$installSettings['site'] = 'default';

// Collect parameters(if exist) for installation configuration settings
for ($i=1;$i < count($argv); $i++) {
  $indexandvalue = explode("=",$argv[$i]);
  $index = $indexandvalue[0];
  $value = $indexandvalue[1];
  $installSettings[$index] = $value;
}

// Process rootpass and collate setting (convert BLANK to empty)
if ($installSettings['rootpass'] == "BLANK") $installSettings['rootpass'] = '';
if ($installSettings['collate'] == "BLANK") $installSettings['collate'] = '';

class InstallerAuto
{
  protected $installer;
  protected $post_variables;

  function __construct()
  {
    global $installSettings;
    $this->post_variables = array( 'iuser'       => $installSettings['iuser'],
                                   'iuname'      => $installSettings['iuname'],
                                   'igroup'      => $installSettings['igroup'],
                                   'server'      => $installSettings['server'],
                                   'loginhost'   => $installSettings['loginhost'],
                                   'port'        => $installSettings['port'],
                                   'root'        => $installSettings['root'],
                                   'rootpass'    => $installSettings['rootpass'],
                                   'login'       => $installSettings['login'],
                                   'pass'        => $installSettings['pass'],
                                   'dbname'      => $installSettings['dbname'],
                                   'collate'     => $installSettings['collate'],
                                   'site'        => $installSettings['site']
                                 );
    $this->installer = new Installer( $this->post_variables );
    if ( ! $this->installer->quick_install() ) {
      echo "ERROR: " . $this->installer->error_message . "\n";
    }
    echo $this->installer->debug_message . "\n";
  }
}

// Run Installer
new InstallerAuto();

?>
