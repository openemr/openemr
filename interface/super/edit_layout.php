<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");

$layouts = array(
  'DEM' => xl('Demographics'),
  'REF' => xl('Referrals'),
);

$layout_id = empty($_REQUEST['layout_id']) ? 'DEM' : $_REQUEST['layout_id'];

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die("Not authorized.");

$fld_line_no = 0;

// Write one option line to the form.
//
function writeFieldLine($field_id, $group, $title, $seq, $uor, $length,
  $titlecols, $datacols, $default, $desc, $data_type)
{
  global $fld_line_no;
  ++$fld_line_no;
  $bgcolor = "#" . (($fld_line_no & 1) ? "ddddff" : "ffdddd");
  $checked = $default ? " checked" : "";

  echo " <tr bgcolor='$bgcolor'>\n";

  echo "  <td align='left' class='optcell'>";
  echo $field_id;
  echo "<input type='hidden' name='fld[$fld_line_no][id]' value='" .
       htmlspecialchars($field_id, ENT_QUOTES) . "' size='20' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][title]' value='" .
       htmlspecialchars($title, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][group]' value='" .
       htmlspecialchars($group, ENT_QUOTES) . "' size='10' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][seq]' value='" .
       htmlspecialchars($seq, ENT_QUOTES) . "' size='4' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<select name='fld[$fld_line_no][uor]' class='optin'>";
  foreach (array(0 =>xl('Unused'), 1 =>xl('Optional'), 2 =>xl('Required')) as $key => $value) {
    echo "<option value='$key'";
    if ($key == $uor) echo " selected";
    echo ">$value</option>\n";
  }
  echo "</select>";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][length]' value='" .
       htmlspecialchars($length, ENT_QUOTES) . "' size='3' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][titlecols]' value='" .
       htmlspecialchars($titlecols, ENT_QUOTES) . "' size='3' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][datacols]' value='" .
       htmlspecialchars($datacols, ENT_QUOTES) . "' size='3' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  if ($data_type == 2) {
    echo "<input type='text' name='fld[$fld_line_no][default]' value='" .
         htmlspecialchars($default, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  } else {
    echo "&nbsp;";
  }
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='fld[$fld_line_no][desc]' value='" .
       htmlspecialchars($desc, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo " </tr>\n";
}
?>
<html>

<head>
<? html_header_show();?>

<link rel=stylesheet href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('Layout Editor','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
.optcell  { }
.optin    { background-color:transparent; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">
</script>

</head>

<body <?php echo $top_bg_line;?>>

<?php
// If we are saving, then save.
//
if ($_POST['form_save'] && $layout_id) {
  $fld = $_POST['fld'];
  for ($lino = 1; isset($fld["$lino"]['id']); ++$lino) {
    $iter = $fld["$lino"];
    $field_id = trim($iter['id']);
    if ($field_id) {
      sqlStatement("UPDATE layout_options SET " .
        "title = '"         . trim($iter['title'])     . "', " .
        "group_name = '"    . trim($iter['group'])     . "', " .
        "seq = '"           . trim($iter['seq'])       . "', " .
        "uor = '"           . trim($iter['uor'])       . "', " .
        "fld_length = '"    . trim($iter['length'])    . "', " .
        "titlecols = '"     . trim($iter['titlecols']) . "', " .
        "datacols = '"      . trim($iter['datacols'])  . "', " .
        "default_value = '" . trim($iter['default'])   . "', " .
        "description = '"   . trim($iter['desc'])      . "' " .
        "WHERE form_id = '$layout_id' AND field_id = '$field_id'");
    }
  }
}

// Get the selected list's elements.
if ($layout_id) {
  $res = sqlStatement("SELECT * FROM layout_options WHERE " .
    "form_id = '$layout_id' ORDER BY group_name, seq");
}
?>

<form method='post' name='theform' action='edit_layout.php'>

<p><b>Edit layout:</b>&nbsp;
<select name ='layout_id' onchange='form.submit()'>
<?php
foreach ($layouts as $key => $value) {
  echo "<option value='$key'";
  if ($key == $layout_id) echo " selected";
  echo ">$value</option>\n";
}
?>
</select></p>

<center>

<table cellpadding='2' cellspacing='0'>
 <tr class='head'>
  <td align='left'><b><?php   xl('ID','e'); ?></b></td>
  <td align='center'><b><?php xl('Label','e'); ?></b></td>
  <td align='center'><b><?php xl('Group','e'); ?></b></td>
  <td align='center'><b><?php xl('Order','e'); ?></b></td>
  <td align='center'><b><?php xl('UOR','e'); ?></b></td>
  <td align='center'><b><?php xl('Size','e'); ?></b></td>
  <td align='center'><b><?php xl('Label Columns','e'); ?></b></td>
  <td align='center'><b><?php xl('Data Columns','e'); ?></b></td>
  <td align='center'><b><?php xl('Default Value','e'); ?></b></td>
  <td align='center'><b><?php xl('Description','e'); ?></b></td>
 </tr>

<?php 
while ($row = sqlFetchArray($res)) {
  writeFieldLine($row['field_id'], $row['group_name'], $row['title'],
    $row['seq'], $row['uor'], $row['fld_length'], $row['titlecols'],
    $row['datacols'], $row['default_value'], $row['description'],
    $row['data_type']);
}
?>

</table>

<p>
 <input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />
</p>

</center>

</form>
</body>
</html>
