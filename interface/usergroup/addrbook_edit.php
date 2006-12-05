<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
 include_once("$srcdir/acl.inc");

 $userid = $_REQUEST['userid'];

 $info_msg = "";

 function QuotedOrNull($fld) {
  $fld = trim($fld);
  if (!get_magic_quotes_gpc()) $fld = addslashes($fld);
  if ($fld) return "'$fld'";
  return "NULL";
 }

 function invalue($name) {
  $fld = trim($_POST[$name]);
  if (!get_magic_quotes_gpc()) $fld = addslashes($fld);
  return "'$fld'";
 }

 function rbinput($name, $value, $desc, $colname) {
  global $row;
  $ret  = "<input type='radio' name='$name' value='$value'";
  if ($row[$colname] == $value) $ret .= " checked";
  $ret .= " />$desc";
  return $ret;
 }

 function rbvalue($rbname) {
  $tmp = $_POST[$rbname];
  if (! $tmp) $tmp = '0';
  return "'$tmp'";
 }

?>
<html>
<head>
<title><? echo $userid ? "Edit" : "Add New" ?> Person</title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">
</script>

</head>

<body <?echo $top_bg_line;?>>
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {

  if ($userid) {

   $query = "UPDATE users SET " .
    "fname = "        . invalue('form_fname')        . ", " .
    "lname = "        . invalue('form_lname')        . ", " .
    "mname = "        . invalue('form_mname')        . ", " .
    "specialty = "    . invalue('form_specialty')    . ", " .
    "federaltaxid = " . invalue('form_federaltaxid') . ", " .
    "upin = "         . invalue('form_upin')         . ", " .
    "npi = "          . invalue('form_npi')          . ", " .
    "email = "        . invalue('form_email')        . ", " .
    "url = "          . invalue('form_url')          . ", " .
    "street = "       . invalue('form_street')       . ", " .
    "city = "         . invalue('form_city')         . ", " .
    "state = "        . invalue('form_state')        . ", " .
    "zip = "          . invalue('form_zip')          . ", " .
    "phone = "        . invalue('form_phone')        . ", " .
    "fax = "          . invalue('form_fax')          . ", " .
    "info = "         . invalue('form_info')         . " "  .
    "WHERE id = '$userid'";
    sqlStatement($query);

  } else {

   $userid = sqlInsert("INSERT INTO users ( " .
    "username, password, authorized, info, source, fname, lname, mname,  " .
    "federaltaxid, federaldrugid, upin, facility, see_auth, active, " .
    "npi, specialty, billname, email, url, street, city, " .
    "state, zip, phone, fax "            .
    ") VALUES ( "                        .
    "'', "                               . // username
    "'', "                               . // password
    "0, "                                . // authorized
    invalue('form_info')          . ", " .
    "NULL, "                             . // source
    invalue('form_fname')         . ", " .
    invalue('form_lname')         . ", " .
    invalue('form_mname')         . ", " .
    invalue('form_federaltaxid')  . ", " .
    "'', "                               . // federaldrugid
    invalue('form_upin')          . ", " .
    "'', "                               . // facility
    "0, "                                . // see_auth
    "1, "                                . // active
    invalue('form_npi')           . ", " .
    invalue('form_specialty')     . ", " .
    "'', "                               . // billname
    invalue('form_email')         . ", " .
    invalue('form_url')           . ", " .
    invalue('form_street')        . ", " .
    invalue('form_city')          . ", " .
    invalue('form_state')         . ", " .
    invalue('form_zip')           . ", " .
    invalue('form_phone')         . ", " .
    invalue('form_fax')           . " "  .
   ")");

  }
 }

 else  if ($_POST['form_delete']) {

  if ($userid) {
   // Be careful not to delete internal users.
   sqlStatement("DELETE FROM users WHERE id = '$userid' AND username = ''");
  }

 }

 if ($_POST['form_save'] || $_POST['form_delete']) {
  // Close this window and redisplay the updated list.
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 if ($userid) {
  $row = sqlQuery("SELECT * FROM users WHERE id = '$userid'");
 }
?>
<form method='post' name='theform' action='addrbook_edit.php?userid=<? echo $userid ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td width='1%' nowrap><b><?php xl('Name','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_lname' value='<? echo $row['lname'] ?>' />&nbsp;
   First: <input type='text' size='10' name='form_fname' value='<? echo $row['fname'] ?>' />&nbsp;
   Middle: <input type='text' size='4' name='form_mname' value='<? echo $row['mname'] ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('Specialty','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_specialty' value='<? echo $row['specialty'] ?>'
    style='width:100%' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('Phone','e'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phone' value='<? echo $row['phone'] ?>' />&nbsp;
   Fax: <input type='text' size='11' name='form_fax' value='<? echo $row['fax'] ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('Email','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_email' value='<? echo $row['email'] ?>'
    style='width:100%' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('Street','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street' value='<? echo $row['street'] ?>'
    style='width:100%' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('City','e'); ?>:</b></td>
  <td>
   <input type='text' size='15' name='form_city' value='<? echo $row['city'] ?>' />&nbsp;
   State: <input type='text' size='10' name='form_state' value='<? echo $row['state'] ?>' />&nbsp;
   Postal: <input type='text' size='10' name='form_zip' value='<? echo $row['zip'] ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><? xl('UPIN','e'); ?>:</b></td>
  <td>
   <input type='text' size='6' name='form_upin' value='<? echo $row['upin'] ?>' />&nbsp;
   NPI: <input type='text' size='10' name='form_npi' value='<? echo $row['npi'] ?>' />&nbsp;
   TIN: <input type='text' size='11' name='form_federaltaxid' value='<? echo $row['federaltaxid'] ?>' />
  </td>
 </tr>

</table>

<br />

<input type='submit' name='form_save' value='Save' />

<?php if ($userid && !$row['username']) { ?>
&nbsp;
<input type='submit' name='form_delete' value='Delete' style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value='Cancel' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 newtype(<? echo $type_index ?>);
</script>
</body>
</html>
