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
$version_table_exist = sqlQuery("SHOW TABLES LIKE 'version'");
if ($version_table_exist) {
    $version_info = sqlQuery("SELECT * FROM version");
    $version_from  = $version_info['v_major'] . '.' . $version_info['v_minor'] . '.' . $version_info['v_patch'];
    $dev_version = $version_info['v_tag'];
    }
if (($dev_version == '-dev') && ($version_table_exist)) {
    $versions_dev = array_keys($versions);  //copy to an index array
    ksort($versions_dev);
    $array_lenght = count($versions_dev);
    $version_top_dev_flag = TRUE;  //most updated version
    for ($x=0;$x<$array_lenght;$x++)  {
      if  ($versions_dev[$x] == $version_from) {
        $version_dev_index = $x;
        $version_top_dev_flag = FALSE;
      }
    }
    $version_from = $versions_dev[$version_dev_index - 1]; //version below
    if ($version_top_dev_flag)  {
      $version_from = $versions_dev[$array_lenght - 1];
    }
}    
if (!empty($_POST['form_submit'])) {
  $form_old_version = $_POST['form_old_version'];
  foreach ($versions as $version => $filename) {
    if (strcmp($version, $version_from) < 0) continue;
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

  echo "<font color='green'>Updating Access Controls...</font><br />\n";
  require("acl_upgrade.php");
  echo "<br />\n";

  echo "<font color='green'>Updating version indicators...</font><br />\n";
  sqlStatement("UPDATE version SET v_major = '$v_major', v_minor = '$v_minor', " .
    "v_patch = '$v_patch', v_tag = '$v_tag', v_database = '$v_database'");

  echo "<p><font color='green'>Database and Access Control upgrade finished.</font></p>\n";
  echo "</body></html>\n";
  exit();
}

?>
<center>
<form method='post' action='sql_upgrade.php'>
<?php
if  (!$version_table_exist)  {
?>
     <select name='form_old_version'>
<?php
     echo "Please select the prior release you are converting from: ";
     foreach  ($versions as $version => $filename) {
       echo " <option value='$version'";
       if  ($version ==='4.1.2') echo " selected";
         echo ">$version</option>\n";
     }
     
} else
{
  echo "Openemr prior release has being selected from the version table: ";
  echo $version_from;
}
?>
</select>

</p>
<p>Click to continuo upgrade.</p>
<p><input type='submit' name='form_submit' value='Upgrade Database' /></p>
</form>
</center>
</body>
</html>
