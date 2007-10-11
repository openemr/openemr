<?php
 // Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
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
<title><?php echo $userid ? "Edit" : "Add New" ?> Person</title>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }

.inputtext {
 /*
 font-family:monospace;
 font-size:10pt;
 font-weight:normal;
 border-style:solid;
 border-width:1px;
 border-color: #000000;
 background-color:transparent;
 */
 padding-left:2px;
 padding-right:2px;
}

.button {
 font-family:sans-serif;
 font-size:9pt;
 font-weight:bold;
}
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
    "title = "        . invalue('form_title')        . ", " .
    "fname = "        . invalue('form_fname')        . ", " .
    "lname = "        . invalue('form_lname')        . ", " .
    "mname = "        . invalue('form_mname')        . ", " .
    "specialty = "    . invalue('form_specialty')    . ", " .
    "organization = " . invalue('form_organization') . ", " .
    "valedictory = "  . invalue('form_valedictory')  . ", " .
    "assistant = "    . invalue('form_assistant')    . ", " .
    "federaltaxid = " . invalue('form_federaltaxid') . ", " .
    "upin = "         . invalue('form_upin')         . ", " .
    "npi = "          . invalue('form_npi')          . ", " .
    "email = "        . invalue('form_email')        . ", " .
    "url = "          . invalue('form_url')          . ", " .
    "street = "       . invalue('form_street')       . ", " .
    "streetb = "      . invalue('form_streetb')      . ", " .
    "city = "         . invalue('form_city')         . ", " .
    "state = "        . invalue('form_state')        . ", " .
    "zip = "          . invalue('form_zip')          . ", " .
    "street2 = "      . invalue('form_street2')      . ", " .
    "streetb2 = "     . invalue('form_streetb2')     . ", " .
    "city2 = "        . invalue('form_city2')        . ", " .
    "state2 = "       . invalue('form_state2')       . ", " .
    "zip2 = "         . invalue('form_zip2')         . ", " .
    "phone = "        . invalue('form_phone')        . ", " .
    "phonew1 = "      . invalue('form_phonew1')      . ", " .
    "phonew2 = "      . invalue('form_phonew2')      . ", " .
    "phonecell = "    . invalue('form_phonecell')    . ", " .
    "fax = "          . invalue('form_fax')          . ", " .
    "notes = "        . invalue('form_notes')        . " "  .
    "WHERE id = '$userid'";
    sqlStatement($query);

  } else {

   $userid = sqlInsert("INSERT INTO users ( " .
    "username, password, authorized, info, source, " .
    "title, fname, lname, mname,  " .
    "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, " .
    "specialty, organization, valedictory, assistant, billname, email, url, " .
    "street, streetb, city, state, zip, " .
    "street2, streetb2, city2, state2, zip2, " .
    "phone, phonew1, phonew2, phonecell, fax, notes "            .
    ") VALUES ( "                        .
    "'', "                               . // username
    "'', "                               . // password
    "0, "                                . // authorized
    "'', "                               . // info
    "NULL, "                             . // source
    invalue('form_title')         . ", " .
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
    invalue('form_organization')  . ", " .
    invalue('form_valedictory')   . ", " .
    invalue('form_assistant')     . ", " .
    "'', "                               . // billname
    invalue('form_email')         . ", " .
    invalue('form_url')           . ", " .
    invalue('form_street')        . ", " .
    invalue('form_streetb')       . ", " .
    invalue('form_city')          . ", " .
    invalue('form_state')         . ", " .
    invalue('form_zip')           . ", " .
    invalue('form_street2')       . ", " .
    invalue('form_streetb2')      . ", " .
    invalue('form_city2')         . ", " .
    invalue('form_state2')        . ", " .
    invalue('form_zip2')          . ", " .
    invalue('form_phone')         . ", " .
    invalue('form_phonew1')       . ", " .
    invalue('form_phonew2')       . ", " .
    invalue('form_phonecell')     . ", " .
    invalue('form_fax')           . ", " .
    invalue('form_notes')         . " "  .
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
<form method='post' name='theform' action='addrbook_edit.php?userid=<?php echo $userid ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td width='1%' nowrap><b><?php xl('Name','e'); ?>:</b></td>
  <td>
   <select name='form_title'>
    <option value=''></option>
<?php
 foreach (array('Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.') as $value) {
  echo "    <option value='$value'";
  if ($value == $row['title']) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
   <b>Last:</b><input type='text' size='10' name='form_lname' class='inputtext'
     maxlength='50' value='<?php echo $row['lname'] ?>'/>&nbsp;
   <b>First:</b> <input type='text' size='10' name='form_fname' class='inputtext'
     maxlength='50' value='<?php echo $row['fname'] ?>' />&nbsp;
   <b>Middle:</b> <input type='text' size='4' name='form_mname' class='inputtext'
     maxlength='50' value='<?php echo $row['mname'] ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Specialty','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_specialty' maxlength='250'
    value='<?php echo $row['specialty'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Organization','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_organization' maxlength='250'
    value='<?php echo $row['organization'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Valedictory','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_valedictory' maxlength='250'
    value='<?php echo $row['valedictory'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Home Phone','e'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phone' value='<?php echo $row['phone'] ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b>Mobile:</b><input type='text' size='11' name='form_phonecell'
    maxlength='30' value='<?php echo $row['phonecell'] ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Work Phone','e'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phonew1' value='<?php echo $row['phonew1'] ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b>2nd:</b><input type='text' size='11' name='form_phonew2' value='<?php echo $row['phonew2'] ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b>Fax:</b> <input type='text' size='11' name='form_fax' value='<?php echo $row['fax'] ?>'
    maxlength='30' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Assistant','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_assistant' maxlength='250'
    value='<?php echo $row['assistant'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Email','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_email' maxlength='250'
    value='<?php echo $row['email'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Website','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_url' maxlength='250'
    value='<?php echo $row['url'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Main Address','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street' maxlength='60'
    value='<?php echo $row['street'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb' maxlength='60'
    value='<?php echo $row['streetb'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('City','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city' maxlength='30'
    value='<?php echo $row['city'] ?>' class='inputtext' />&nbsp;
   <b>State/county:</b> <input type='text' size='10' name='form_state' maxlength='30'
    value='<?php echo $row['state'] ?>' class='inputtext' />&nbsp;
   <b>Postal code:</b> <input type='text' size='10' name='form_zip' maxlength='20'
    value='<?php echo $row['zip'] ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Alt Address','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street2' maxlength='60'
    value='<?php echo $row['street2'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb2' maxlength='60'
    value='<?php echo $row['streetb2'] ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('City','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city2' maxlength='30'
    value='<?php echo $row['city2'] ?>' class='inputtext' />&nbsp;
   <b>State/county:</b> <input type='text' size='10' name='form_state2' maxlength='30'
    value='<?php echo $row['state2'] ?>' class='inputtext' />&nbsp;
   <b>Postal code:</b> <input type='text' size='10' name='form_zip2' maxlength='20'
    value='<?php echo $row['zip2'] ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('UPIN','e'); ?>:</b></td>
  <td>
   <input type='text' size='6' name='form_upin' maxlength='6'
    value='<?php echo $row['upin'] ?>' class='inputtext' />&nbsp;
   <b>NPI:</b> <input type='text' size='10' name='form_npi' maxlength='10'
    value='<?php echo $row['npi'] ?>' class='inputtext' />&nbsp;
   <b>TIN:</b> <input type='text' size='10' name='form_federaltaxid' maxlength='10'
    value='<?php echo $row['federaltaxid'] ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Notes','e'); ?>:</b></td>
  <td>
   <textarea rows='3' cols='40' name='form_notes' style='width:100%'
    wrap='virtual' class='inputtext' /><?php echo $row['notes'] ?></textarea>
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
</body>
</html>
