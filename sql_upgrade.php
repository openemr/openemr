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
$your_version = $version_from;
$versions_index_array = array_keys($versions);
$versions_index_lenght = count($versions_index_array);
ksort($versions_index_array);
$most_recent_version = $versions_index_array[$versions_index_lenght - 1];
if (($dev_version != '-dev') && ($version_table_exist)) {
    $version_top_nodev_flag = TRUE;
      for ($x=0;$x<$versions_index_lenght;$x++) {
         if ($versions_index_array[$x] == $version_from) {
            $version_top_nodev_flag = FALSE;
       }
   }
}
if (($dev_version == '-dev') && ($version_table_exist)) {
    $versions_dev = array_keys($versions);   //copy to an index array
    ksort($versions_dev);
    $array_lenght = count($versions_dev);
    $version_top_dev_flag = TRUE;       //most updated version
    for ($x=0;$x<$versions_index_lenght;$x++) {
       if ($versions_index_array[$x] == $version_from) {
         $version_dev_index = $x;
         $version_top_dev_flag = FALSE;
        }
     }
      $version_from = $versions_index_array[$version_dev_index - 1];
      if ($version_top_dev_flag) {
         $version_from = $versions_index_array[$versions_index_lenght - 1];
         }
}

if (!empty($_POST['form_submit'])) {
  if (!empty($_POST['form_old_version'])) {
    $version_from = $_POST['form_old_version'];
   }
 if (!empty($_POST['manual_override'])) {
    $version_from = $_POST['manual_override'];
   }

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
  require("acl_upgrade.phsql_upgrade_xdebug.phpp");
  echo "<br />\n";

  echo "<font color='green'>Updating version indicators...</font><br />\n";
  sqlStatement("UPDATE version SET v_major = '$v_major', v_minor = '$v_minor', " .
    "v_patch = '$v_patch', v_tag = '$v_tag', v_database = '$v_database'");

  echo "<p><font color='greesql_upgrade_xdebug.phpn'>Database and Access Control upgrade finished.</font></p>\n";
  echo "</body></html>\n";
  exit();
}  

?>
<center>
<form method='post' action='sql_upgrade_commit6.php'>
<?php
if (!$version_table_exist) {
   echo "It appers no version table exist<br><br>";
   echo "Please select the prior release you are converting from: <br><br>";
?>
   <select name='form_old_version'>
<?php
   foreach  ($versions as $version => $filename) {
      echo " <option value='$version'";
      //Defaulting to most recent version, wich is now 4.1.2.
      //if ($version === '4.1.2') echo " selected";
        if ($version === $most_recent_version) echo " selected";
      echo ">$version</option>\n";
   }
} 
?>
</select>
<?php
if (($dev_version != '-dev') && ($version_table_exist)) {
?>
    <p>Your Openemr database version is : <?php echo $version_from;?></p>
    <p>v_tag  in version table is : <?php echo $dev_version;?></p>
<?php
    if ($version_top_nodev_flag) {
      echo "It appears the version is up to date<br><br>"; 
     }
    echo "Please select the prior release you are converting from: <br><br>";
?>
         <select name='form_old_version'>
<?php    
         foreach  ($versions as $version => $filename) {
               echo " <option value='$version'";
               //if ($version === '4.1.2') echo " selected";
                 if ($version === $most_recent_version) echo " selected";
                  echo ">$version</option>\n";
                 }
       } // end of if ($dev_version != '-dev')
?>
</select>
<?php
    if (($dev_version == '-dev') && ($version_table_exist))   {
        echo "Your Openemr database version is : $your_version <br>";
        echo "v_tag in version table is : $dev_version <br>";
        echo "Openemr prior release has being selected from the version table: ";
        echo $version_from; echo"<br><br>";
        echo "Or if you prefer to select the release you are converting from: <br>";
?>
        <select name='form_old_version'>
<?php
        foreach  ($versions as $version => $filename) {
            echo " <option value='$version'";
            //if ($version === $most_recent_version) echo " selected";
            if ($version === $version_from) echo " selected";
             echo ">$version</option>\n";
         }
       }
?>
</select>
<br><br>
If you are unsure or were using a development version between two<br>
releases, them choose the older of possible releases.
<br>
Or Type here if you need to manual override : <input type="text" name="manual_override" size="1"/>

</p>
<p>Click to continuo upgrade.</p>
<p><input type='submit' name='form_submit' value='Upgrade Database' /></p>
</form>
</center>
</body>
</html>
