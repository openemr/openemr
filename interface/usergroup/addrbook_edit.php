<?php
 // Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
 include_once("$srcdir/acl.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/formdata.inc.php");

 // Collect user id if editing entry
 $userid = $_REQUEST['userid'];
 
 // Collect type if creating a new entry
 $type = $_REQUEST['type'];

 $info_msg = "";

 function QuotedOrNull($fld) {
  $fld = formDataCore($fld,true);
  if ($fld) return "'$fld'";
  return "NULL";
 }

 function invalue($name) {
  $fld = formData($name,"P",true);
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
<title><?php echo $userid ? xl('Edit') : xl('Add New') ?> <?php xl('Person','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

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

 var type_options_js = Array();
 <?php
  // Collect the type options. Possible values are:
  // 1 = Unassigned (default to patient centric)
  // 2 = Person Centric
  // 3 = Company Centric (Basically, do not show the Name fields)
  $sql = sqlStatement("SELECT option_id, option_value FROM list_options WHERE " .
   "list_id = 'abook_type'");
  while ($row_query = sqlFetchArray($sql)) {
   echo "type_options_js"."['" . htmlspecialchars($row_query['option_id'],ENT_QUOTES) . "']=" . htmlspecialchars($row_query['option_value'],ENT_QUOTES) . ";\n";
  }
 ?>

 // Process to customize the form by type
 function typeSelect(a) {
  if (type_options_js[a] == 3) {
   // Company centric, so hide the Name entries
   document.getElementById("nameRow").style.display = "none";
  }
  else {
   // show the name row
   document.getElementById("nameRow").style.display = "";
  }
 }
</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {

  if ($userid) {

   $query = "UPDATE users SET " .
    "abook_type = "   . invalue('form_abook_type')   . ", " .
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
    "taxonomy = "     . invalue('form_taxonomy')     . ", " .
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
    "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, " .
    "specialty, organization, valedictory, assistant, billname, email, url, " .
    "street, streetb, city, state, zip, " .
    "street2, streetb2, city2, state2, zip2, " .
    "phone, phonew1, phonew2, phonecell, fax, notes, abook_type "            .
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
    invalue('form_taxonomy')      . ", " .
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
    invalue('form_notes')         . ", " .
    invalue('form_abook_type')    . " "  .
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

 if ($type) { // note this only happens when its new
  // Set up type
  $row['abook_type'] = strip_escape_custom($type);
 }

?>

<script language="JavaScript">
 $(document).ready(function() {
  // customize the form via the type options
  typeSelect("<?php echo $row['abook_type']; ?>");
 });
</script>

<form method='post' name='theform' action='addrbook_edit.php?userid=<?php echo $userid ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td width='1%' nowrap><b><?php xl('Type','e'); ?>:</b></td>
  <td>
<?php
 echo generate_select_list('form_abook_type', 'abook_type', $row['abook_type'], '', 'Unassigned', '', 'typeSelect(this.value)');
?>
  </td>
 </tr>

 <tr id="nameRow">
  <td width='1%' nowrap><b><?php xl('Name','e'); ?>:</b></td>
  <td>
<?php
 generate_form_field(array('data_type'=>1,'field_id'=>'title','list_id'=>'titles','empty_title'=>' '), $row['title']);
?>
   <b><?php xl('Last','e'); ?>:</b><input type='text' size='10' name='form_lname' class='inputtext'
     maxlength='50' value='<?php echo htmlspecialchars($row['lname'], ENT_QUOTES); ?>'/>&nbsp;
   <b><?php xl('First','e'); ?>:</b> <input type='text' size='10' name='form_fname' class='inputtext'
     maxlength='50' value='<?php echo htmlspecialchars($row['fname'], ENT_QUOTES); ?>' />&nbsp;
   <b><?php xl('Middle','e'); ?>:</b> <input type='text' size='4' name='form_mname' class='inputtext'
     maxlength='50' value='<?php echo htmlspecialchars($row['mname'], ENT_QUOTES); ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Specialty','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_specialty' maxlength='250'
    value='<?php echo htmlspecialchars($row['specialty'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Organization','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_organization' maxlength='250'
    value='<?php echo htmlspecialchars($row['organization'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Valedictory','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_valedictory' maxlength='250'
    value='<?php echo htmlspecialchars($row['valedictory'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Home Phone','e'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phone' value='<?php echo htmlspecialchars($row['phone'], ENT_QUOTES); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php xl('Mobile','e'); ?>:</b><input type='text' size='11' name='form_phonecell'
    maxlength='30' value='<?php echo htmlspecialchars($row['phonecell'], ENT_QUOTES); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Work Phone','e'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phonew1' value='<?php echo htmlspecialchars($row['phonew1'], ENT_QUOTES); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php xl('2nd','e'); ?>:</b><input type='text' size='11' name='form_phonew2' value='<?php echo htmlspecialchars($row['phonew2'], ENT_QUOTES); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php xl('Fax','e'); ?>:</b> <input type='text' size='11' name='form_fax' value='<?php echo htmlspecialchars($row['fax'], ENT_QUOTES); ?>'
    maxlength='30' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Assistant','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_assistant' maxlength='250'
    value='<?php echo htmlspecialchars($row['assistant'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Email','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_email' maxlength='250'
    value='<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Website','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_url' maxlength='250'
    value='<?php echo htmlspecialchars($row['url'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Main Address','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street' maxlength='60'
    value='<?php echo htmlspecialchars($row['street'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb' maxlength='60'
    value='<?php echo htmlspecialchars($row['streetb'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('City','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city' maxlength='30'
    value='<?php echo htmlspecialchars($row['city'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php echo xl('State')."/".xl('county'); ?>:</b> <input type='text' size='10' name='form_state' maxlength='30'
    value='<?php echo htmlspecialchars($row['state'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php xl('Postal code','e'); ?>:</b> <input type='text' size='10' name='form_zip' maxlength='20'
    value='<?php echo htmlspecialchars($row['zip'], ENT_QUOTES); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('Alt Address','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street2' maxlength='60'
    value='<?php echo htmlspecialchars($row['street2'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb2' maxlength='60'
    value='<?php echo htmlspecialchars($row['streetb2'], ENT_QUOTES); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('City','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city2' maxlength='30'
    value='<?php echo htmlspecialchars($row['city2'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php echo xl('State')."/".xl('county'); ?>:</b> <input type='text' size='10' name='form_state2' maxlength='30'
    value='<?php echo htmlspecialchars($row['state2'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php xl('Postal code','e'); ?>:</b> <input type='text' size='10' name='form_zip2' maxlength='20'
    value='<?php echo htmlspecialchars($row['zip2'], ENT_QUOTES); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php xl('UPIN','e'); ?>:</b></td>
  <td>
   <input type='text' size='6' name='form_upin' maxlength='6'
    value='<?php echo htmlspecialchars($row['upin'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php xl('NPI','e'); ?>:</b> <input type='text' size='10' name='form_npi' maxlength='10'
    value='<?php echo htmlspecialchars($row['npi'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php xl('TIN','e'); ?>:</b> <input type='text' size='10' name='form_federaltaxid' maxlength='10'
    value='<?php echo htmlspecialchars($row['federaltaxid'], ENT_QUOTES); ?>' class='inputtext' />&nbsp;
   <b><?php xl('Taxonomy','e'); ?>:</b> <input type='text' size='10' name='form_taxonomy' maxlength='10'
    value='<?php echo htmlspecialchars($row['taxonomy'], ENT_QUOTES); ?>' class='inputtext' />
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

<input type='submit' name='form_save' value=<?php xl('Save','e','\'','\''); ?> />

<?php if ($userid && !$row['username']) { ?>
&nbsp;
<input type='submit' name='form_delete' value=<?php xl('Delete','e','\'','\''); ?> style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value=<?php xl('Cancel','e','\'','\''); ?> onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
