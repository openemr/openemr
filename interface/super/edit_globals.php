<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/globals.inc.php");
require_once("$srcdir/user.inc");
require_once("$srcdir/classes/CouchDB.class.php");
require_once(dirname(__FILE__)."/../../myportal/soap_service/portal_connectivity.php");

if ($_GET['mode'] != "user") {
  // Check authorization.
  $thisauth = acl_check('admin', 'super');
  if (!$thisauth) die(xl('Not authorized'));
}

function checkCreateCDB(){
  $globalsres = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN 
  ('couchdb_host','couchdb_user','couchdb_pass','couchdb_port','couchdb_dbase','document_storage_method')");
    $options = array();
    while($globalsrow = sqlFetchArray($globalsres)){
      $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
    }
    $directory_created = false;
  if($GLOBALS['document_storage_method'] != 0){
    // /documents/temp/ folder is required for CouchDB
    if(!is_dir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/')){
      $directory_created = mkdir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/',0777,true);      
      if(!$directory_created){
	echo htmlspecialchars( xl("Failed to create temporary folder. CouchDB will not work."),ENT_NOQUOTES);
      }
    }
        $couch = new CouchDB();
    if(!$couch->check_connection()) {
      echo "<script type='text/javascript'>alert('".addslashes(xl("CouchDB Connection Failed."))."');</script>";
      return;
    }
    if($GLOBALS['couchdb_host'] || $GLOBALS['couchdb_port'] || $GLOBALS['couchdb_dbase']){
      $couch->createDB($GLOBALS['couchdb_dbase']);
      $couch->createView($GLOBALS['couchdb_dbase']);
    }
  }
  return true;
}

/**
 * Update background_services table for a specific service following globals save.
 * @author EMR Direct
 */
function updateBackgroundService($name,$active,$interval) {
   //order important here: next_run change dependent on _old_ value of execute_interval so it comes first
   $sql = 'UPDATE background_services SET active=?, '
	. 'next_run = next_run + INTERVAL (? - execute_interval) MINUTE, execute_interval=? WHERE name=?';
   return sqlStatement($sql,array($active,$interval,$interval,$name));
}

/**
 * Make any necessary changes to background_services table when globals are saved.
 * To prevent an unexpected service call during startup or shutdown, follow these rules:
 * 1. Any "startup" operations should occur _before_ the updateBackgroundService() call.
 * 2. Any "shutdown" operations should occur _after_ the updateBackgroundService() call. If these operations
 * would cause errors in a running service call, it would be best to make the shutdown function itself
 * a background service that is activated here, does nothing if active=1 or running=1 for the
 * parent service, then deactivates itself by setting active=0 when it is done shutting the parent service
 * down. This will prevent nonresponsiveness to the user by waiting for a service to finish.
 * 3. If any "previous" values for globals are required for startup/shutdown logic, they need to be
 * copied to a temp variable before the while($globalsrow...) loop.
 * @author EMR Direct
 */
function checkBackgroundServices(){
  //load up any necessary globals
  $bgservices = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN
  ('phimail_enable','phimail_interval')");
    while($globalsrow = sqlFetchArray($bgservices)){
      $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
    }

   //Set up phimail service
   $phimail_active = $GLOBALS['phimail_enable'] ? '1' : '0';
   $phimail_interval = max(0,(int)$GLOBALS['phimail_interval']);
   updateBackgroundService('phimail',$phimail_active,$phimail_interval);
}
?>

<html>

<head>
<?php

html_header_show();

// If we are saving user_specific globals.
//
if ($_POST['form_save'] && $_GET['mode'] == "user") {
  $i = 0;
  foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    if (in_array($grpname, $USER_SPECIFIC_TABS)) {
      foreach ($grparr as $fldid => $fldarr) {
        if (in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
          list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
          $label = "global:".$fldid;
          $fldvalue = trim(strip_escape_custom($_POST["form_$i"]));
          setUserSetting($label,$fldvalue,$_SESSION['authId'],FALSE);
          if ( $_POST["toggle_$i"] == "YES" ) {
            removeUserSetting($label);
          }
          ++$i;
        }
      }
    }
  }
  echo "<script type='text/javascript'>";
  echo "parent.left_nav.location.reload();";
  echo "parent.Title.location.reload();";
  echo "if(self.name=='RTop'){";
  echo "parent.RBot.location.reload();";
  echo "}else{";
  echo "parent.RTop.location.reload();";
  echo "}";
  echo "self.location.href='edit_globals.php?mode=user&unique=yes';";
  echo "</script>";
}

if ($_POST['form_download']) {  
  $client = portal_connection();  
  try {
    $response = $client->getPortalConnectionFiles($credentials);
  }
  catch(SoapFault $e){
    error_log('SoapFault Error');
    error_log(var_dump(get_object_vars($e)));
  }
  catch(Exception $e){
    error_log('Exception Error');
    error_log(var_dump(get_object_vars($e)));
  }
  if($response['status'] == "1") {//WEBSERVICE RETURNED VALUE SUCCESSFULLY    
    $tmpfilename	= realpath(sys_get_temp_dir())."/".date('YmdHis').".zip";  
    $fp			= fopen($tmpfilename,"wb");
    fwrite($fp,base64_decode($response['value']));
    fclose($fp);
    $practice_filename	= $response['file_name'];//practicename.zip    
    ob_clean();    
    // Set headers
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=".$practice_filename);
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: binary");   
    // Read the file from disk
    readfile($tmpfilename);   
    unlink($tmpfilename);    
    exit;
  }
  else{//WEBSERVICE CALL FAILED AND RETURNED AN ERROR MESSAGE
    ob_end_clean();
    ?>
    <script type="text/javascript">
      alert('<?php echo xlt('Offsite Portal web Service Failed').":\\n".text($response['value']);?>');
    </script>
    <?php    
  }
}
?>
<html>
<head>
<?php

// If we are saving main globals.
//
if ($_POST['form_save'] && $_GET['mode'] != "user") {

  $i = 0;
  foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    foreach ($grparr as $fldid => $fldarr) {
      list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
	  if($fldtype == 'pwd'){
	  $pass = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = '$fldid'");
	  $fldvalueold = $pass['gl_value'];
	  }
      sqlStatement("DELETE FROM globals WHERE gl_name = '$fldid'");

      if (substr($fldtype, 0, 2) == 'm_') {
        if (isset($_POST["form_$i"])) {
          $fldindex = 0;
          foreach ($_POST["form_$i"] as $fldvalue) {
            $fldvalue = formDataCore($fldvalue, true);
            sqlStatement("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
              "VALUES ( '$fldid', '$fldindex', '$fldvalue' )");
            ++$fldindex;
          }
        }
      }
      else {
        if (isset($_POST["form_$i"])) {
          $fldvalue = formData("form_$i", "P", true);
        }
        else {
          $fldvalue = "";
        }
        if($fldtype=='pwd')
          $fldvalue = $fldvalue ? SHA1($fldvalue) : $fldvalueold;
		  if(fldvalue){
		  sqlStatement("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
          "VALUES ( '$fldid', '0', '$fldvalue' )");
		  }
      }

      ++$i;
    }
  }
  checkCreateCDB();
  checkBackgroundServices();
  echo "<script type='text/javascript'>";
  echo "parent.left_nav.location.reload();";
  echo "parent.Title.location.reload();";
  echo "if(self.name=='RTop'){";
  echo "parent.RBot.location.reload();";
  echo "}else{";
  echo "parent.RTop.location.reload();";
  echo "}";
  echo "self.location.href='edit_globals.php?unique=yes';";
  echo "</script>";
}
?>

<!-- supporting javascript code -->
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<?php if ($_GET['mode'] == "user") { ?>
  <title><?php  xl('User Settings','e'); ?></title>
<?php } else { ?>
  <title><?php  xl('Global Settings','e'); ?></title>
<?php } ?>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
</style>
<script type="text/javascript">
  function validate_file(){
    $.ajax({
      type: "POST",
      url: "<?php echo $GLOBALS['webroot']?>/library/ajax/offsite_portal_ajax.php",
      data: {
	action: 'check_file',      
      },
      cache: false,
      success: function( message )
      {	
	if(message == 'OK'){
	  document.getElementById('form_download').value = 1;
	  document.getElementById('file_error_message').innerHTML = '';
	  document.forms[0].submit();
	}
	else{
	  document.getElementById('form_download').value = 0;
	  document.getElementById('file_error_message').innerHTML = message;
	  return false;	  
	}
      }
    });
  }
</script>
</head>

<body class="body_top">

<?php if ($_GET['mode'] == "user") { ?>
  <form method='post' name='theform' id='theform' action='edit_globals.php?mode=user' onsubmit='return top.restoreSession()'>
<?php } else { ?>
  <form method='post' name='theform' id='theform' action='edit_globals.php' onsubmit='return top.restoreSession()'>
<?php } ?>

<?php if ($_GET['mode'] == "user") { ?>
  <p><b><?php xl('Edit User Settings','e'); ?></b>
<?php } else { ?>
  <p><b><?php xl('Edit Global Settings','e'); ?></b>
<?php } ?>

<ul class="tabNav">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
  if ( $_GET['mode'] != "user" || ($_GET['mode'] == "user" && in_array($grpname, $USER_SPECIFIC_TABS)) ) {
    echo " <li" . ($i ? "" : " class='current'") .
      "><a href='/play/javascript-tabbed-navigation/'>" .
      xl($grpname) . "</a></li>\n";
    ++$i;
  }
}
?>
</ul>

<div class="tabContainer">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
 if ( $_GET['mode'] != "user" || ($_GET['mode'] == "user" && in_array($grpname, $USER_SPECIFIC_TABS)) ) {
  echo " <div class='tab" . ($i ? "" : " current") .
    "' style='height:auto;width:97%;'>\n";

  echo " <table>";

  if ($_GET['mode'] == "user") {
   echo "<tr>";
   echo "<th>&nbsp</th>";
   echo "<th>" . htmlspecialchars( xl('User Specific Setting'), ENT_NOQUOTES) . "</th>";
   echo "<th>" . htmlspecialchars( xl('Default Setting'), ENT_NOQUOTES) . "</th>";
   echo "<th>&nbsp</th>";
   echo "<th>" . htmlspecialchars( xl('Set to Default'), ENT_NOQUOTES) . "</th>";
   echo "</tr>";
  }

  foreach ($grparr as $fldid => $fldarr) {
   if ( $_GET['mode'] != "user" || ($_GET['mode'] == "user" && in_array($fldid, $USER_SPECIFIC_GLOBALS)) ) {
    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;

    // Most parameters will have a single value, but some will be arrays.
    // Here we cater to both possibilities.
    $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE " .
      "gl_name = '$fldid' ORDER BY gl_index");
    $glarr = array();
    while ($glrow = sqlFetchArray($glres)) $glarr[] = $glrow;

    // $fldvalue is meaningful only for the single-value cases.
    $fldvalue = count($glarr) ? $glarr[0]['gl_value'] : $flddef;

    // Collect user specific setting if mode set to user
    $userSetting = "";
    $settingDefault = "checked='checked'";
    if ($_GET['mode'] == "user") {
      $userSettingArray = sqlQuery("SELECT * FROM user_settings WHERE setting_user=? AND setting_label=?",array($_SESSION['authId'],"global:".$fldid));
      $userSetting = $userSettingArray['setting_value'];
      $globalValue = $fldvalue;
      if (!empty($userSettingArray)) {
        $fldvalue = $userSetting;
        $settingDefault = "";
      }
    }

    echo " <tr title='$flddesc'><td valign='top'><b>$fldname </b></td><td valign='top'>\n";

    if (is_array($fldtype)) {
      echo "  <select name='form_$i' id='form_$i'>\n";
      foreach ($fldtype as $key => $value) {
        if ($_GET['mode'] == "user") {
          if ($globalValue == $key) $globalTitle = $value;
        }
        echo "   <option value='$key'";
        if ($key == $fldvalue) echo " selected";
        echo ">";
        echo $value;
        echo "</option>\n";
      }
      echo "  </select>\n";
    }

    else if ($fldtype == 'bool') {
      if ($_GET['mode'] == "user") {
        if ($globalValue == 1) {
          $globalTitle = htmlspecialchars( xl('Checked'), ENT_NOQUOTES);
        }
        else {
          $globalTitle = htmlspecialchars( xl('Not Checked'), ENT_NOQUOTES);
        }
      }
      echo "  <input type='checkbox' name='form_$i' id='form_$i' value='1'";
      if ($fldvalue) echo " checked";
      echo " />\n";
    }

    else if ($fldtype == 'num') {
      if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      echo "  <input type='text' name='form_$i' id='form_$i' " .
        "size='6' maxlength='15' value='$fldvalue' />\n";
    }

    else if ($fldtype == 'text') {
      if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      echo "  <input type='text' name='form_$i' id='form_$i' " .
        "size='50' maxlength='255' value='$fldvalue' />\n";
    }
    else if ($fldtype == 'pwd') {
	  if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      echo "  <input type='password' name='form_$i' " .
        "size='50' maxlength='255' value='' />\n";
    }

    else if ($fldtype == 'pass') {
	  if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      echo "  <input type='password' name='form_$i' " .
        "size='50' maxlength='255' value='$fldvalue' />\n";
    }

    else if ($fldtype == 'lang') {
      $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
      echo "  <select name='form_$i' id='form_$i'>\n";
      while ($row = sqlFetchArray($res)) {
        echo "   <option value='" . $row['lang_description'] . "'";
        if ($row['lang_description'] == $fldvalue) echo " selected";
        echo ">";
        echo xl($row['lang_description']);
        echo "</option>\n";
      }
      echo "  </select>\n";
    }

    else if ($fldtype == 'm_lang') {
      $res = sqlStatement("SELECT * FROM lang_languages  ORDER BY lang_description");
      echo "  <select multiple name='form_{$i}[]' id='form_{$i}[]' size='3'>\n";
      while ($row = sqlFetchArray($res)) {
        echo "   <option value='" . $row['lang_description'] . "'";
        foreach ($glarr as $glrow) {
          if ($glrow['gl_value'] == $row['lang_description']) {
            echo " selected";
            break;
          }
        }
        echo ">";
        echo xl($row['lang_description']);
        echo "</option>\n";
      }
      echo "  </select>\n";
    }

    else if ($fldtype == 'css') {
      if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      $themedir = "$webserver_root/interface/themes";
      $dh = opendir($themedir);
      if ($dh) {
        echo "  <select name='form_$i' id='form_$i'>\n";
        while (false !== ($tfname = readdir($dh))) {
          // Only show files that contain style_ as options
          //  Skip style_blue.css since this is used for
          //  lone scripts such as setup.php
          //  Also skip style_pdf.css which is for PDFs and not screen output
          if (!preg_match("/^style_.*\.css$/", $tfname) ||
            $tfname == 'style_blue.css' || $tfname == 'style_pdf.css')
            continue;
          echo "<option value='$tfname'";
          if ($tfname == $fldvalue) echo " selected";
          echo ">";
          echo $tfname;
          echo "</option>\n";
        }
        closedir($dh);
        echo "  </select>\n";
      }
    }

    else if ($fldtype == 'hour') {
      if ($_GET['mode'] == "user") {
        $globalTitle = $globalValue;
      }
      echo "  <select name='form_$i' id='form_$i'>\n";
      for ($h = 0; $h < 24; ++$h) {
        echo "<option value='$h'";
        if ($h == $fldvalue) echo " selected";
        echo ">";
        if      ($h ==  0) echo "12 AM";
        else if ($h <  12) echo "$h AM";
        else if ($h == 12) echo "12 PM";
        else echo ($h - 12) . " PM";
        echo "</option>\n";
      }
      echo "  </select>\n";
    }
    if ($_GET['mode'] == "user") {
      echo " </td>\n";
      echo "<td align='center' style='color:red;'>" . $globalTitle . "</td>\n";
      echo "<td>&nbsp</td>";
      echo "<td align='center'><input type='checkbox' value='YES' name='toggle_" . $i . "' id='toggle_" . $i . "' " . $settingDefault . "/></td>\n";
      echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . $globalValue . "'>\n";
      echo "</tr>\n";
    }
    else {
      echo " </td></tr>\n";
    }
    ++$i;
   }
    if(trim(strtolower($fldid)) == 'portal_offsite_address_patient_link' && $GLOBALS['portal_offsite_enable'] && $GLOBALS['portal_offsite_providerid']){
      echo "<input type='hidden' name='form_download' id='form_download'>";
      echo "<tr><td><input onclick=\"return validate_file()\" type='button' value='".xla('Download Offsite Portal Connection Files')."' /></td><td id='file_error_message' style='color:red'></td></tr>";
    }
  }  
  echo " </table>\n";  
  echo " </div>\n";
 }
}
?>
</div>

<p>
 <input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
</p>
</center>

</form>

</body>

<script language="JavaScript">

$(document).ready(function(){
  tabbify();
  enable_modals();

  // Use the counter ($i) to make the form user friendly for user-specific globals use
  <?php if ($_GET['mode'] == "user") { ?>
    <?php for ($j = 0; $j <= $i; $j++) { ?>
      $("#form_<?php echo $j ?>").change(function() {
        $("#toggle_<?php echo $j ?>").attr('checked',false);
      });
      $("#toggle_<?php echo $j ?>").change(function() {
        if ($('#toggle_<?php echo $j ?>').attr('checked')) {
          var defaultGlobal = $("#globaldefault_<?php echo $j ?>").val();
          $("#form_<?php echo $j ?>").val(defaultGlobal);
        }
      });
    <?php } ?>
  <?php } ?>

});

</script>

</html>

