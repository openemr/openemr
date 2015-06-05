<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//  OpenEMR
//  MySQL Config
//  Needed by sql.inc

// Database parameters are now site-specific.
// $GLOBALS['OE_SITE_DIR'] is set in interface/globals.php.
if (empty($GLOBALS['OE_SITE_DIR'])) {
  // This happens if called via user invocation of gacl/setup.php.
  $GLOBALS['OE_SITES_BASE'] = dirname(__FILE__) . "/../sites";
  $tmp = empty($_GET['site']) ? 'default' : $_GET['site'];
  $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . '/' . $tmp;
}
require_once $GLOBALS['OE_SITE_DIR'] . "/sqlconf.php";
?>
