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

if ($_GET['mode'] != "user") {
  // Check authorization.
  $thisauth = acl_check('admin', 'super');
  if (!$thisauth) die(xl('Not authorized'));
}

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
          if (isset($_POST["form_$i"])) {
            $fldvalue = trim(strip_escape_custom($_POST["form_$i"]));
            setUserSetting($label,$fldvalue,$_SESSION['authId'],FALSE);
          }
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
  echo "parent.RTop.location.href='edit_globals.php?mode=user';";
  echo "parent.RBot.location.reload();";
  echo "}else{";
  echo "parent.RBot.location.href='edit_globals.php?mode=user';";
  echo "parent.RTop.location.reload();";
  echo "}</script>";
}

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
  echo "<script type='text/javascript'>";
  echo "parent.left_nav.location.reload();";
  echo "parent.Title.location.reload();";
  echo "if(self.name=='RTop'){";
  echo "parent.RTop.location.href='edit_globals.php';";
  echo "parent.RBot.location.reload();";
  echo "}else{";
  echo "parent.RBot.location.href='edit_globals.php';";
  echo "parent.RTop.location.reload();";
  echo "}</script>";
}
?>
<html>

<head>
<?php html_header_show();?>

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
      if (!empty($userSetting) || $userSetting === "0" ) {
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
          if (!preg_match("/^style_.*\.css$/", $tfname) || $tfname == 'style_blue.css') { continue; }
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
  }
  echo " </table>\n";
  echo " </div>\n";
 }
}
?>
</div>

<p>
 <input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />
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

