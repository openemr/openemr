<?php
 // Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 //SANITIZE ALL ESCAPES
 $sanitize_all_escapes=true;
 //

 //STOP FAKE REGISTER GLOBALS
 $fake_register_globals=false;
 //

 include_once("../globals.php");
 include_once("$srcdir/acl.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/formdata.inc.php");
 require_once("$srcdir/htmlspecialchars.inc.php");

 // Collect user id if editing entry
 $userid = $_REQUEST['userid'];
 
 // Collect type if creating a new entry
 $type = $_REQUEST['type'];

 $info_msg = "";

 function invalue($name) {
  $fld = add_escape_custom(trim($_POST[$name]));
  return "'$fld'";
 }

?>
<html>
<head>
<title><?php echo $userid ? xlt('Edit') : xlt('Add New') ?> <?php echo xlt('Person'); ?></title>
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<style>
td { font-size:10pt; }

.inputtext {
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
  // 1 = Unassigned (default to person centric)
  // 2 = Person Centric
  // 3 = Company Centric
  $sql = sqlStatement("SELECT option_id, option_value FROM list_options WHERE " .
   "list_id = 'abook_type'");
  while ($row_query = sqlFetchArray($sql)) {
   echo "type_options_js"."['" . attr($row_query['option_id']) . "']=" . attr($row_query['option_value']) . ";\n";
  }
 ?>

 // Process to customize the form by type
 function typeSelect(a) {
   if(a=='ord_lab'){
      $('#cpoe_span').css('display','inline');
  } else {
       $('#cpoe_span').css('display','none');
       $('#form_cpoe').removeAttr('checked');
  }
  if (type_options_js[a] == 3) {
   // Company centric:
   //   1) Hide the person Name entries
   //   2) Hide the Specialty entry
   //   3) Show the director Name entries
   document.getElementById("nameRow").style.display = "none";
   document.getElementById("specialtyRow").style.display = "none";
   document.getElementById("nameDirectorRow").style.display = "";
  }
  else {
   // Person centric:
   //   1) Hide the director Name entries
   //   2) Show the person Name entries
   //   3) Show the Specialty entry
   document.getElementById("nameDirectorRow").style.display = "none";
   document.getElementById("nameRow").style.display = "";
   document.getElementById("specialtyRow").style.display = "";
  }
 }
</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {

 // Collect the form_abook_type option value
 //  (ie. patient vs company centric)
 $type_sql_row = sqlQuery("SELECT `option_value` FROM `list_options` WHERE `list_id` = 'abook_type' AND `option_id` = ?", array(trim($_POST['form_abook_type'])));
 $option_abook_type = $type_sql_row['option_value'];
 // Set up any abook_type specific settings
 if ($option_abook_type == 3) {
  // Company centric
  $form_title = invalue('form_director_title');
  $form_fname = invalue('form_director_fname');
  $form_lname = invalue('form_director_lname');
  $form_mname = invalue('form_director_mname');
 }
 else {
  // Person centric
  $form_title = invalue('form_title');
  $form_fname = invalue('form_fname');
  $form_lname = invalue('form_lname');
  $form_mname = invalue('form_mname');
 }

  if ($userid) {

   $query = "UPDATE users SET " .
    "abook_type = "   . invalue('form_abook_type')   . ", " .
    "title = "        . $form_title                  . ", " .
    "fname = "        . $form_fname                  . ", " .
    "lname = "        . $form_lname                  . ", " .
    "mname = "        . $form_mname                  . ", " .
    "specialty = "    . invalue('form_specialty')    . ", " .
    "organization = " . invalue('form_organization') . ", " .
    "valedictory = "  . invalue('form_valedictory')  . ", " .
    "assistant = "    . invalue('form_assistant')    . ", " .
    "federaltaxid = " . invalue('form_federaltaxid') . ", " .
    "upin = "         . invalue('form_upin')         . ", " .
    "npi = "          . invalue('form_npi')          . ", " .
    "taxonomy = "     . invalue('form_taxonomy')     . ", " .
	"cpoe = "         . invalue('form_cpoe')         . ", " .    
    "email = "        . invalue('form_email')        . ", " .
    "email_direct = " . invalue('form_email_direct') . ", " .
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
    "WHERE id = '" . add_escape_custom($userid) . "'";
    sqlStatement($query);

  } else {

   $userid = sqlInsert("INSERT INTO users ( " .
    "username, password, authorized, info, source, " .
    "title, fname, lname, mname,  " .
    "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe, " .
    "specialty, organization, valedictory, assistant, billname, email, email_direct, url, " .
    "street, streetb, city, state, zip, " .
    "street2, streetb2, city2, state2, zip2, " .
    "phone, phonew1, phonew2, phonecell, fax, notes, abook_type "            .
    ") VALUES ( "                        .
    "'', "                               . // username
    "'', "                               . // password
    "0, "                                . // authorized
    "'', "                               . // info
    "NULL, "                             . // source
    $form_title                   . ", " .
    $form_fname                   . ", " .
    $form_lname                   . ", " .
    $form_mname                   . ", " .
    invalue('form_federaltaxid')  . ", " .
    "'', "                               . // federaldrugid
    invalue('form_upin')          . ", " .
    "'', "                               . // facility
    "0, "                                . // see_auth
    "1, "                                . // active
    invalue('form_npi')           . ", " .
    invalue('form_taxonomy')      . ", " .
	invalue('form_cpoe')          . ", " .
    invalue('form_specialty')     . ", " .
    invalue('form_organization')  . ", " .
    invalue('form_valedictory')   . ", " .
    invalue('form_assistant')     . ", " .
    "'', "                               . // billname
    invalue('form_email')         . ", " .
    invalue('form_email_direct')  . ", " .
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
   sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($userid));
  }

 }

 if ($_POST['form_save'] || $_POST['form_delete']) {
  // Close this window and redisplay the updated list.
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('".addslashes($info_msg)."');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 if ($userid) {
  $row = sqlQuery("SELECT * FROM users WHERE id = ?", array($userid));
 }

 if ($type) { // note this only happens when its new
  // Set up type
  $row['abook_type'] = $type;
 }

?>

<script language="JavaScript">
 $(document).ready(function() {
  // customize the form via the type options
  typeSelect("<?php echo attr($row['abook_type']); ?>");
  if(abook_type == 'ord_lab') {
    $('#cpoe_span').css('display','inline');
   }
 });
</script>

<form method='post' name='theform' action='addrbook_edit.php?userid=<?php echo attr($userid) ?>'>
<center>

<table border='0' width='100%'>

<?php if (acl_check('admin', 'practice' )) { // allow choose type option if have admin access ?>
 <tr>
  <td width='1%' nowrap><b><?php echo xlt('Type'); ?>:</b></td>
  <td>
<?php
 echo generate_select_list('form_abook_type', 'abook_type', $row['abook_type'], '', 'Unassigned', '', 'typeSelect(this.value)');
?>
  </td>
 </tr>
<?php } // end of if has admin access ?>

 <tr id="nameRow">
  <td width='1%' nowrap><b><?php echo xlt('Name'); ?>:</b></td>
  <td>
<?php
 generate_form_field(array('data_type'=>1,'field_id'=>'title','list_id'=>'titles','empty_title'=>' '), $row['title']);
?>
   <b><?php echo xlt('Last'); ?>:</b><input type='text' size='10' name='form_lname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['lname']); ?>'/>&nbsp;
   <b><?php echo xlt('First'); ?>:</b> <input type='text' size='10' name='form_fname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['fname']); ?>' />&nbsp;
   <b><?php echo xlt('Middle'); ?>:</b> <input type='text' size='4' name='form_mname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['mname']); ?>' />
  </td>
 </tr>

 <tr id="specialtyRow">
  <td nowrap><b><?php echo xlt('Specialty'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_specialty' maxlength='250'
    value='<?php echo attr($row['specialty']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Organization'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_organization' maxlength='250'
    value='<?php echo attr($row['organization']); ?>'
    style='width:100%' class='inputtext' />
	<span id='cpoe_span' style="display:none;">
		<input type='checkbox' title="<?php echo xla('CPOE'); ?>" name='form_cpoe' id='form_cpoe' value='1' <?php if($row['cpoe']=='1') echo "CHECKED"; ?>/>
		<label for='form_cpoe'><b><?php echo xlt('CPOE'); ?></b></label>
   </span>
  </td>
 </tr>

 <tr id="nameDirectorRow">
  <td width='1%' nowrap><b><?php echo xlt('Director Name'); ?>:</b></td>
  <td>
<?php
 generate_form_field(array('data_type'=>1,'field_id'=>'director_title','list_id'=>'titles','empty_title'=>' '), $row['title']);
?>
   <b><?php echo xlt('Last'); ?>:</b><input type='text' size='10' name='form_director_lname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['lname']); ?>'/>&nbsp;
   <b><?php echo xlt('First'); ?>:</b> <input type='text' size='10' name='form_director_fname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['fname']); ?>' />&nbsp;
   <b><?php echo xlt('Middle'); ?>:</b> <input type='text' size='4' name='form_director_mname' class='inputtext'
     maxlength='50' value='<?php echo attr($row['mname']); ?>' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Valedictory'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_valedictory' maxlength='250'
    value='<?php echo attr($row['valedictory']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Home Phone'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phone' value='<?php echo attr($row['phone']); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php echo xlt('Mobile'); ?>:</b><input type='text' size='11' name='form_phonecell'
    maxlength='30' value='<?php echo attr($row['phonecell']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Work Phone'); ?>:</b></td>
  <td>
   <input type='text' size='11' name='form_phonew1' value='<?php echo attr($row['phonew1']); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php echo xlt('2nd'); ?>:</b><input type='text' size='11' name='form_phonew2' value='<?php echo attr($row['phonew2']); ?>'
    maxlength='30' class='inputtext' />&nbsp;
   <b><?php echo xlt('Fax'); ?>:</b> <input type='text' size='11' name='form_fax' value='<?php echo attr($row['fax']); ?>'
    maxlength='30' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Assistant'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_assistant' maxlength='250'
    value='<?php echo attr($row['assistant']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Email'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_email' maxlength='250'
    value='<?php echo attr($row['email']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Trusted Email'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_email_direct' maxlength='250'
    value='<?php echo attr($row['email_direct']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Website'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_url' maxlength='250'
    value='<?php echo attr($row['url']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Main Address'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street' maxlength='60'
    value='<?php echo attr($row['street']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb' maxlength='60'
    value='<?php echo attr($row['streetb']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('City'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city' maxlength='30'
    value='<?php echo attr($row['city']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('State')."/".xlt('county'); ?>:</b> <input type='text' size='10' name='form_state' maxlength='30'
    value='<?php echo attr($row['state']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('Postal code'); ?>:</b> <input type='text' size='10' name='form_zip' maxlength='20'
    value='<?php echo attr($row['zip']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Alt Address'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_street2' maxlength='60'
    value='<?php echo attr($row['street2']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap>&nbsp;</td>
  <td>
   <input type='text' size='40' name='form_streetb2' maxlength='60'
    value='<?php echo attr($row['streetb2']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('City'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_city2' maxlength='30'
    value='<?php echo attr($row['city2']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('State')."/".xlt('county'); ?>:</b> <input type='text' size='10' name='form_state2' maxlength='30'
    value='<?php echo attr($row['state2']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('Postal code'); ?>:</b> <input type='text' size='10' name='form_zip2' maxlength='20'
    value='<?php echo attr($row['zip2']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('UPIN'); ?>:</b></td>
  <td>
   <input type='text' size='6' name='form_upin' maxlength='6'
    value='<?php echo attr($row['upin']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('NPI'); ?>:</b> <input type='text' size='10' name='form_npi' maxlength='10'
    value='<?php echo attr($row['npi']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('TIN'); ?>:</b> <input type='text' size='10' name='form_federaltaxid' maxlength='10'
    value='<?php echo attr($row['federaltaxid']); ?>' class='inputtext' />&nbsp;
   <b><?php echo xlt('Taxonomy'); ?>:</b> <input type='text' size='10' name='form_taxonomy' maxlength='10'
    value='<?php echo attr($row['taxonomy']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Notes'); ?>:</b></td>
  <td>
   <textarea rows='3' cols='40' name='form_notes' style='width:100%'
    wrap='virtual' class='inputtext' /><?php echo text($row['notes']) ?></textarea>
  </td>
 </tr>

</table>

<br />

<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />

<?php if ($userid && !$row['username']) { ?>
&nbsp;
<input type='submit' name='form_delete' value='<?php echo xla('Delete'); ?>' style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
