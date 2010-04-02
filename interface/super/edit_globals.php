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

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die(xl('Not authorized'));

// If we are saving, then save.
//
if ($_POST['form_save']) {

  $i = 0;
  foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    foreach ($grparr as $fldid => $fldarr) {
      list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
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
        sqlStatement("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
          "VALUES ( '$fldid', '0', '$fldvalue' )");
      }

      ++$i;
    }
  }

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
<title><?php  xl('Global Settings','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
</style>

<script language="JavaScript">

$(document).ready(function(){
  tabbify();
  enable_modals();
});

</script>

</head>

<body class="body_top">

<form method='post' name='theform' id='theform' action='edit_globals.php' onsubmit='return top.restoreSession()'>

<p><b><?php xl('Edit Global Settings','e'); ?></b>

<ul class="tabNav">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
  echo " <li" . ($i ? "" : " class='current'") .
    "><a href='/play/javascript-tabbed-navigation/'>$grpname</a></li>\n";
  ++$i;
}
?>
</ul>

<div class="tabContainer">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
  echo " <div class='tab" . ($i ? "" : " current") .
    "' style='height:auto;width:97%;'>\n";

  echo " <table>";

  foreach ($grparr as $fldid => $fldarr) {
    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;

    // Most parameters will have a single value, but some will be arrays.
    // Here we cater to both possibilities.
    $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE " .
      "gl_name = '$fldid' ORDER BY gl_index");
    $glarr = array();
    while ($glrow = sqlFetchArray($glres)) $glarr[] = $glrow;

    // $fldvalue is meaningful only for the single-value cases.
    $fldvalue = count($glarr) ? $glarr[0]['gl_value'] : $flddef;

    echo " <tr title='$flddesc'><td valign='top'><b>$fldname </b></td><td valign='top'>\n";

    if (is_array($fldtype)) {
      echo "  <select name='form_$i'>\n";
      foreach ($fldtype as $key => $value) {
        echo "   <option value='$key'";
        if ($key == $fldvalue) echo " selected";
        echo ">";
        echo $value;
        echo "</option>\n";
      }
      echo "  </select>\n";
    }

    else if ($fldtype == 'bool') {
      echo "  <input type='checkbox' name='form_$i' value='1'";
      if ($fldvalue) echo " checked";
      echo " />\n";
    }

    else if ($fldtype == 'num') {
      echo "  <input type='text' name='form_$i' " .
        "size='6' maxlength='15' value='$fldvalue' />\n";
    }

    else if ($fldtype == 'text') {
      echo "  <input type='text' name='form_$i' " .
        "size='50' maxlength='255' value='$fldvalue' />\n";
    }

    else if ($fldtype == 'lang') {
      $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
      echo "  <select name='form_$i'>\n";
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
      echo "  <select multiple name='form_{$i}[]' size='3'>\n";
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
      $themedir = "$webserver_root/interface/themes";
      $dh = opendir($themedir);
      if ($dh) {
        echo "  <select name='form_$i'>\n";
        while (false !== ($tfname = readdir($dh))) {
          if (!preg_match("/^style.*\.css$/", $tfname)) { continue; }
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
      echo "  <select name='form_$i'>\n";
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

    echo " </td></tr>\n";
    ++$i;
  }
  echo " </table>\n";
  echo " </div>\n";
}
?>
</div>

<p>
 <input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />
</p>
</center>

</form>

</body>

</html>

