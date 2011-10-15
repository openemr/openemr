<?php
// Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This may be run after an upgraded OpenEMR has been installed.
// Its purpose is to upgrade the MySQL OpenEMR database as needed
// for the new release.

// Disable PHP timeout.  This will not work in safe mode.
ini_set('max_execution_time', '0');

$ignoreAuth = true; // no login required

require_once('interface/globals.php');
require_once('library/sql.inc');
require_once('library/sql_upgrade_fx.php');
require_once('version.php');

// Force logging off
$GLOBALS["enable_auditlog"]=0;

?>

<html>
<head>
<title>OpenEMR Database Patch</title>
<link rel='STYLESHEET' href='interface/themes/style_blue.css'>
</head>
<body>
<center>
<span class='title'>OpenEMR Database Patch</span>
<br>
</center>

<?php
upgradeFromSqlFile('patch.sql');
flush();

echo "<font color='green'>Updating global configuration defaults...</font><br />\n";
require_once("library/globals.inc.php");
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
  foreach ($grparr as $fldid => $fldarr) {
    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
    if (substr($fldtype, 0, 2) !== 'm_') {
      $row = sqlQuery("SELECT count(*) AS count FROM globals WHERE gl_name = '$fldid'");
      if (empty($row['count'])) {
        sqlStatement("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
          "VALUES ( '$fldid', '0', '$flddef' )");
      }
    }
  }
}

echo "<font color='green'>Updating version indicators...</font><br />\n";
sqlStatement("UPDATE version SET v_major = '$v_major', v_minor = '$v_minor', " .
  "v_patch = '$v_patch', v_realpatch = '$v_realpatch', v_tag = '$v_tag', v_database = '$v_database'");

echo "<p><font color='green'>Database patch finished.</font></p>\n";
echo "</body></html>\n";
exit();

?>

</body>
</html>
