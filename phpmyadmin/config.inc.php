<?php
/*
 * Customized for OpenEMR.
 *
 */

// Access control is dealt with by the ACL check
$ignoreAuth = true;
require_once("../interface/globals.php");
require_once("../library/acl.inc");
if ($GLOBALS['disable_phpmyadmin_link']) {
  echo "You do not have access to this resource<br>";
  exit;
}
if (! acl_check('admin', 'database')) {
  echo "You do not have access to this resource<br>";
  exit;
}

/* Servers configuration */
$i = 0;

/* Server localhost (config:openemr) [1] */
$i++;

/* For standard OpenEMR database access */
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['host'] = $sqlconf['host'];
$cfg['Servers'][$i]['port'] = $sqlconf['port'];
$cfg['Servers'][$i]['user'] = $sqlconf['login'];
$cfg['Servers'][$i]['password'] = $sqlconf['pass'];
$cfg['Servers'][$i]['only_db'] = $sqlconf['dbase'];

/* Other mods for OpenEMR */
$cfg['ShowCreateDb'] = false;
$cfg['ShowPhpInfo'] = TRUE;
?>
