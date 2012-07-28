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

// Force logging off
$GLOBALS["enable_auditlog"]=0;

$versions = array();
$sqldir = "$webserver_root/sql";
$dh = opendir($sqldir);
if (! $dh) die("Cannot read $sqldir");
while (false !== ($sfname = readdir($dh))) {
  if (substr($sfname, 0, 1) == '.') continue;
  if (preg_match('/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade.sql$/', $sfname, $matches)) {
    $version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
    $versions[$version] = $sfname;
  }
}
closedir($dh);
ksort($versions);
?>
<html>
<head>
<title>OpenEMR Database Upgrade</title>
<link rel='STYLESHEET' href='interface/themes/style_blue.css'>
</head>
<body>
<center>
<span class='title'>OpenEMR Database Upgrade</span>
<br>
</center>
<?php
if (!empty($_POST['form_submit'])) {
  $form_old_version = $_POST['form_old_version'];

  foreach ($versions as $version => $filename) {
    if (strcmp($version, $form_old_version) < 0) continue;
    upgradeFromSqlFile($filename);
  }

  if (!empty($GLOBALS['ippf_specific'])) {
    // Upgrade custom stuff for IPPF.
    upgradeFromSqlFile('ippf_upgrade.sql');
  }

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
    "v_patch = '$v_patch', v_tag = '$v_tag', v_database = '$v_database'");

  echo "<p><font color='green'>Database upgrade finished.</font></p>\n";
  echo "</body></html>\n";
  exit();
}

?>
<center>
<form method='post' action='sql_upgrade.php'>
<p>Please select the prior release you are converting from:
<select name='form_old_version'>
<?php
foreach ($versions as $version => $filename) {
  echo " <option value='$version'";
  // Defaulting to most recent version, which is now 4.1.1.
  if ($version === '4.1.1') echo " selected";
  echo ">$version</option>\n";
}
?>
</select>
</p>
<p>If you are unsure or were using a development version between two
releases, then choose the older of possible releases.</p>
<p><input type='submit' name='form_submit' value='Upgrade Database' /></p>
</form>
</center>
</body>
</html>
