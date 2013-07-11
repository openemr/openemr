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
include_once("../../../library/sqlconf.php");
// Modified 5/2009 by BM for UTF-8 project 
global $host,$port,$login,$pass,$dbase,$disable_utf8_flag;
if (!$disable_utf8_flag) {
 $pnconfig['utf8Flag'] = true;
}
else {
 $pnconfig['utf8Flag'] = false;
}
// ---------------------------------------

$pnconfig['modname'] = "PostCalendar";
$pnconfig['startpage'] = "PostCalendar";
$pnconfig['language'] = "eng";
$pnconfig['dbtype'] = 'mysql';
$pnconfig['dbtabletype'] = 'MyISAM';
$pnconfig['dbhost'] = $host.":".$port;
$pnconfig['dbuname'] = $login;
$pnconfig['dbpass'] = $pass;
$pnconfig['dbname'] = $dbase;
$pnconfig['system'] = '0';
$pnconfig['prefix'] = 'openemr';
$pnconfig['encoded'] = '0';


$pntable = array();
$session_info = $prefix . '_session_info';
$pntable['session_info'] = $session_info;
$pntable['session_info_column'] = array (
		'sessid'    => $session_info . 
		'.pn_sessid', 'ipaddr'    => $session_info . 
		'.pn_ipaddr','firstused' => $session_info . 
		'.pn_firstused','lastused'  => $session_info . 
		'.pn_lastused','uid'       => $session_info . 
		'.pn_uid','vars'      => $session_info . 
		'.pn_vars');
// ----------------------------------------------------------------------
// For debugging (Pablo Roca)
//
// $debug - debugger windows active
//          0 = No
//          1 = Yes
//
// $debug_sql - show SQL in lens debug
//          0 = No
//          1 = Yes
// ----------------------------------------------------------------------
GLOBAL $pndebug;
$pndebug['debug']          = 0;
$pndebug['debug_sql']      = 0;

// ----------------------------------------------------------------------
// You have finished configuring the database. Now you can start to
// change your site settings in the Administration Section.
//
// Thanks for choosing PostNuke.
// ----------------------------------------------------------------------

// ----------------------------------------------------------------------
// if there is a personal_config.php in the folder where is config.php
// we add it. (This HAS to be at the end, after all initialization.)
// ----------------------------------------------------------------------
if (@file_exists("personal_config.php"))
{ include("personal_config.php"); }
// ----------------------------------------------------------------------
// Make config file backwards compatible (deprecated)
// ----------------------------------------------------------------------
extract($pnconfig, EXTR_OVERWRITE);
?>
