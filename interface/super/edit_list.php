<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");

$lists = array(
  'language'   => xl('Language'),
  'marital'    => xl('Marital Status'),
  'pricelevel' => xl('Price Level'),
  'ethrace'    => xl('Race/Ethnicity'),
  'sex'        => xl('Sex'),
  'titles'     => xl('Titles'),
  'yesno'      => xl('Yes/No'),
  'userlist1'  => xl('User Defined List 1'),
  'userlist2'  => xl('User Defined List 2'),
);

$list_id = empty($_REQUEST['list_id']) ? 'language' : $_REQUEST['list_id'];

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die("Not authorized.");

$opt_line_no = 0;

// Write one option line to the form.
//
function writeOptionLine($option_id, $title, $seq, $default) {
  global $opt_line_no;
  ++$opt_line_no;
  $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");
  $checked = $default ? " checked" : "";

  echo " <tr bgcolor='$bgcolor'>\n";
  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][id]' value='" .
       htmlspecialchars($option_id, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";
  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][title]' value='" .
       htmlspecialchars($title, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";
  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][seq]' value='" .
       htmlspecialchars($seq, ENT_QUOTES) . "' size='4' maxlength='10' class='optin' />";
  echo "</td>\n";
  echo "  <td align='center' class='optcell'>";
  echo "<input type='checkbox' name='opt[$opt_line_no][default]' value='1'$checked class='optin' />";
  echo "</td>\n";
  echo " </tr>\n";
}
?>
<html>

<head>

<link rel=stylesheet href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('List Editor','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
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
if ($_POST['form_save'] && $list_id) {
  $opt = $_POST['opt'];
  sqlStatement("DELETE FROM list_options WHERE list_id = '$list_id'");
  for ($lino = 1; isset($opt["$lino"]['id']); ++$lino) {
    $iter = $opt["$lino"];
    if (trim($iter['id'])) {
      sqlInsert("INSERT INTO list_options ( " .
      "list_id, option_id, title, seq, is_default " .
      ") VALUES ( " .
      "'$list_id', "                       .
      "'" . trim($iter['id'])      . "', " .
      "'" . trim($iter['title'])   . "', " .
      "'" . trim($iter['seq'])     . "', " .
      "'" . trim($iter['default']) . "' "  .
      ")");
    }
  }
}

// Get the selected list's elements.
if ($list_id) {
  $res = sqlStatement("SELECT * FROM list_options WHERE " .
    "list_id = '$list_id' ORDER BY seq");
}
?>

<form method='post' name='theform' action='edit_list.php'>

<p><b>Edit list:</b>&nbsp;
<select name ='list_id' onchange='form.submit()'>
<?php
foreach ($lists as $key => $value) {
  echo "<option value='$key'";
  if ($key == $list_id) echo " selected";
  echo ">$value</option>\n";
}
?>
</select></p>

<center>

<table cellpadding='2' cellspacing='0'>
 <tr class='head'>
  <td title='Click to edit'><b><?php  xl('ID','e'); ?></b></td>
  <td><b><?php  xl('Title','e'); ?></b></td>
  <td><b><?php  xl('Order','e'); ?></b></td>
  <td><b><?php  xl('Default','e'); ?></b></td>
 </tr>

<?php 
while ($row = sqlFetchArray($res)) {
  writeOptionLine($row['option_id'], $row['title'], $row['seq'],
    $row['is_default']);
}

for ($i = 0; $i < 3; ++$i) {
  writeOptionLine('', '', '', '');
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
