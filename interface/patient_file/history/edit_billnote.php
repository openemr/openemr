<?php
 // Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../../globals.php");
 include_once("$srcdir/log.inc");
 include_once("$srcdir/acl.inc");

 $feid = $_GET['feid'] + 0; // id from form_encounter table

 $info_msg = "";

 $thisauth = (acl_check('acct', 'bill') == 'write');
 if (! $thisauth) die("Not authorized!");
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<style>
</style>

</head>

<body>
<?php
if ($_POST['form_submit'] || $_POST['form_cancel']) {
  $fenote = trim($_POST['form_note']); // TBD: might need addslashes
  if ($_POST['form_submit']) {
    sqlStatement("UPDATE form_encounter " .
      "SET billing_note = '$fenote' WHERE id = '$feid'");
  }
  else {
    $tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
      " WHERE id = '$feid'");
    $fenote = addslashes($tmp['billing_note']);
  }
  $fenote = str_replace("\r\n", "<br />", $fenote);
  $fenote = str_replace("\n"  , "<br />", $fenote);
  if (! $fenote) $fenote = '[Add]';
  echo "<script language='JavaScript'>\n";
  echo " parent.closeNote($feid, '$fenote')\n";
  echo "</script></body></html>\n";
  exit();
}

$tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
  " WHERE id = '$feid'");
$fenote = $tmp['billing_note'];
?>

<form method='post' action='edit_billnote.php?feid=<?php echo $feid ?>'>

<center>
<textarea name='form_note' style='width:100%'><?php echo $fenote ?></textarea>
<p>
<input type='submit' name='form_submit' value='Save' />
&nbsp;&nbsp;
<input type='submit' name='form_cancel' value='Cancel' />
</center>
</form>
</body>
</html>
