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

$EMRversion = trim(preg_replace('/\s*\([^)]*\)/', '', $GLOBALS['openemr_version']));
?>   


<html>
<head>
<title>OpenEMR <?php echo attr($EMRversion) ?> <?php echo xlt('Database Patch'); ?></title>
<link rel='STYLESHEET' href='interface/themes/style_blue.css'>
</head>
<body style="color:green;">
<div style="box-shadow: 3px 3px 5px 6px #ccc; border-radius: 20px; padding: 10px 40px;background-color:#EFEFEF; width:500px; margin:40px auto"> 
  
  <p style="font-weight:bold; font-size:1.8em; text-align:center">OpenEMR <?php echo text($EMRversion),' ',xlt('Database Patch'),' ',text($v_realpatch) ?></p>      
  <p style="font-weight:bold; text-align:center;"><?php echo xlt('Applying Patch to site'),' : ',text($_SESSION['site_id']) ?></p>
   
  
  <?php
  upgradeFromSqlFile('patch.sql');
  flush();
  
  echo '<p style="font-weight:bold; text-align:left; color:green">',xlt('Updating global configuration defaults'),'...</p>';
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
                                
  echo '<p style="font-weight:bold; text-align:left;">',xlt('Updating version indicators'),'...</p>'; 
  sqlStatement("UPDATE version SET v_realpatch = '$v_realpatch'");
   
                  
  echo '<p style="text-align:center; font-size:1.8em;">',xlt('Database Patch'),' ',text($v_realpatch),' ',xlt('finished'),'.</p>'; 
  
  echo '<p style="text-align:center; font-size:1.8em;">OpenEMR ',xlt('Version'),' = ',text($EMRversion.'('.$v_realpatch.')'),'.</p>';  
   
  echo '<p><a style="border-radius: 10px; padding:5px; width:200px; margin:0 auto; background-color:green; color:white; font-weight:bold; display:block; text-align:center;" href="index.php?site=',attr($_SESSION['site_id']).'">',xlt('Log in'),'</a></p>';  
  
  if(isset($_SERVER['HTTP_REFERER'])) {
      $split = preg_split('/\//',$_SERVER['HTTP_REFERER']);
      if($split[count($split) - 1] == 'admin.php')
        echo '<p><a style="border-radius: 10px; padding:5px; width:200px; margin:0 auto; background-color:green; color:white; font-weight:bold; display:block; text-align:center;" href="admin.php">',xlt('Back to Admin Page'),'</a></p>';
  }  
  
  ?>
</div>
</body>
</html>
