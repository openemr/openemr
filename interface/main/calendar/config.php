<?php

// ----------------------------------------------------------------------
// Database & System Config
//
//      dbtype:     type of database, currently only mysql
//      dbhost:     MySQL Database Hostname
//      dbuname:    MySQL Username
//      dbpass:     MySQL Password
//      dbname:     MySQL Database Name
//      system:     0 for Unix/Linux, 1 for Windows
//      encoded:    0 for MySQL information unenccoded
//                  1 for encoded
// ----------------------------------------------------------------------
//

// to collect sql database login info and the utf8 flag
// also collect the adodb libraries to support mysqli_mod that is needed for mysql ssl support
require_once(dirname(__FILE__) . "/../../../library/sqlconf.php");
require_once(dirname(__FILE__) . "/../../../vendor/adodb/adodb-php/adodb.inc.php");
require_once(dirname(__FILE__) . "/../../../vendor/adodb/adodb-php/drivers/adodb-mysqli.inc.php");
require_once(dirname(__FILE__) . "/../../../library/ADODB_mysqli_mod.php");

// Modified 5/2009 by BM for UTF-8 project
global $host,$port,$login,$pass,$dbase,$db_encoding,$disable_utf8_flag;
if (!$disable_utf8_flag) {
    if (!empty($db_encoding) && ($db_encoding == "utf8mb4")) {
        $pnconfig['db_encoding'] = "utf8mb4";
    } else {
        $pnconfig['db_encoding'] = "utf8";
    }
} else {
    $pnconfig['db_encoding'] = "";
}

// ---------------------------------------

$pnconfig['modname'] = "PostCalendar";
$pnconfig['startpage'] = "PostCalendar";
$pnconfig['dbtype'] = 'mysqli_mod';
$pnconfig['dbtabletype'] = 'MyISAM';
$pnconfig['dbhost'] = $host;
$pnconfig['dbport'] = $port;
$pnconfig['dbuname'] = $login;
$pnconfig['dbpass'] = $pass;
$pnconfig['dbname'] = $dbase;
$pnconfig['system'] = '0';
$pnconfig['prefix'] = 'openemr';

// ----------------------------------------------------------------------
// Make config file backwards compatible (deprecated)
// ----------------------------------------------------------------------
extract($pnconfig, EXTR_OVERWRITE);
