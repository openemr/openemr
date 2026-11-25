<?php

// ----------------------------------------------------------------------
// Database & System Config
//
//      dbtype:     type of database, currently only mysql
//      system:     0 for Unix/Linux, 1 for Windows
//      encoded:    0 for MySQL information unenccoded
//                  1 for encoded
// ----------------------------------------------------------------------
//

// collect the adodb libraries to support mysqli_mod that is needed for mysql ssl support
require_once(__DIR__ . "/../../../vendor/adodb/adodb-php/adodb.inc.php");
require_once(__DIR__ . "/../../../vendor/adodb/adodb-php/drivers/adodb-mysqli.inc.php");

// ---------------------------------------

$pnconfig['modname'] = "PostCalendar";
$pnconfig['startpage'] = "PostCalendar";
$pnconfig['dbtype'] = 'mysqli';
$pnconfig['dbtabletype'] = 'MyISAM';
$pnconfig['system'] = '0';
$pnconfig['prefix'] = 'openemr';

// ----------------------------------------------------------------------
// Make config file backwards compatible (deprecated)
// ----------------------------------------------------------------------
extract($pnconfig, EXTR_OVERWRITE);
