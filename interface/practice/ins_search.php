<?php
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This module is used to find and add insurance companies.
 // It is opened as a popup window.  The opener may have a
 // JavaScript function named set_insurance(id, name), in which
 // case selecting or adding an insurance company will cause the
 // function to be called passing the ID and name of that company.

 // When used for searching, this module will in turn open another
 // popup window ins_list.php, which lists the matched results and
 // permits selection of one of them via the same set_insurance()
 // function.

 include_once("../globals.php");
 include_once("$srcdir/acl.inc");

 // Putting a message here will cause a popup window to display it.
 $info_msg = "";

 // This is copied from InsuranceCompany.class.php.  It should
 // really be in a SQL table.
 $freeb_type_array = array(''
  , xl('Other HCFA')
  , xl('Medicare Part B')
  , xl('Medicaid')
  , xl('ChampUSVA')
  , xl('ChampUS')
  , xl('Blue Cross Blue Shield')
  , xl('FECA')
  , xl('Self Pay')
  , xl('Central Certification')
  , xl('Other Non-Federal Programs')
  , xl('Preferred Provider Organization (PPO)')
  , xl('Point of Service (POS)')
  , xl('Exclusive Provider Organization (EPO)')
  , xl('Indemnity Insurance')
  , xl('Health Maintenance Organization (HMO) Medicare Risk')
  , xl('Automobile Medical')
  , xl('Commercial Insurance Co.')
  , xl('Disability')
  , xl('Health Maintenance Organization')
  , xl('Liability')
  , xl('Liability Medical')
  , xl('Other Federal Program')
  , xl('Title V')
  , xl('Veterans Administration Plan')
  , xl('Workers Compensation Health Plan')
  , xl('Mutually Defined')
 );

?>
<html>
<head>
<title><?php xl('Insurance Company Search/Add','e');?></title>
<link rel=stylesheet href='<?php  echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
.search { background-color:#aaffaa }
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

 var mypcc = '<?php  echo $GLOBALS['phone_country_code'] ?>';

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 function doescape(value) {
  return escape(value);
 }

 // This is invoked when our Search button is clicked.
 function dosearch() {
  var f = document.forms[0];
  dlgopen('ins_list.php' +
   '?form_name='   + doescape(f.form_name.value  ) +
   '&form_attn='   + doescape(f.form_attn.value  ) +
   '&form_addr1='  + doescape(f.form_addr1.value ) +
   '&form_addr2='  + doescape(f.form_addr2.value ) +
   '&form_city='   + doescape(f.form_city.value  ) +
   '&form_state='  + doescape(f.form_state.value ) +
   '&form_zip='    + doescape(f.form_zip.value   ) +
   '&form_phone='  + doescape(f.form_phone.value ) +
   '&form_cms_id=' + doescape(f.form_cms_id.value) +
   '', '_blank', 780, 500);

  return false;
 }

 // The ins_list.php window calls this to set the selected insurance.
 function set_insurance(ins_id, ins_name) {
  if (opener.closed || ! opener.set_insurance)
   alert('The target form was closed; I cannot apply your selection.');
  else
   opener.set_insurance(ins_id, ins_name);
  window.close();
 }

 // This is set to true on a mousedown of the Save button.  The
 // reason is so we can distinguish between clicking on the Save
 // button vs. hitting the Enter key, as we prefer the "default"
 // action to be search and not save.
 var save_clicked = false;

 // Onsubmit handler.
 function validate(f) {
  // If save was not clicked then default to searching.
  if (! save_clicked) return dosearch();
  save_clicked = false;

  msg = '';
  if (! f.form_name.value.length ) msg += 'Company name is missing. ';
  if (! f.form_addr1.value.length) msg += 'Address is missing. ';
  if (! f.form_city.value.length ) msg += 'City is missing. ';
  if (! f.form_state.value.length) msg += 'State is missing. ';
  if (! f.form_zip.value.length  ) msg += 'Zip is missing.';

  if (msg) {
   alert(msg);
   return false;
  }

  top.restoreSession();
  return true;
 }

</script>

</head>

<body <?php echo $top_bg_line;?> onunload='imclosing()'>
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {
  $ins_id = '';
  $ins_name = $_POST['form_name'];

  if ($ins_id) {
   // sql for updating could go here if this script is enhanced to support
   // editing of existing insurance companies.
  } else {
   $ins_id = generate_id();

   sqlInsert("INSERT INTO insurance_companies ( " .
    "id, name, attn, cms_id, freeb_type, x12_receiver_id, x12_default_partner_id " .
    ") VALUES ( " .
    $ins_id                         . ", "  .
    "'" . $ins_name                 . "', " .
    "'" . $_POST['form_attn']       . "', " .
    "'" . $_POST['form_cms_id']     . "', " .
    "'" . $_POST['form_freeb_type'] . "', " .
    "'" . $_POST['form_partner']    . "', " .
    "'" . $_POST['form_partner']    . "' "  .
   ")");

   sqlInsert("INSERT INTO addresses ( " .
    "id, line1, line2, city, state, zip, country, foreign_id " .
    ") VALUES ( " .
    generate_id()                . ", "  .
    "'" . $_POST['form_addr1']   . "', " .
    "'" . $_POST['form_addr2']   . "', " .
    "'" . $_POST['form_city']    . "', " .
    "'" . $_POST['form_state']   . "', " .
    "'" . $_POST['form_zip']     . "', " .
    "'" . $_POST['form_country'] . "', " .
    $ins_id                      . " "   .
   ")");

   $phone_parts = array();
   preg_match("/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $_POST['form_phone'],
    $phone_parts);

   sqlInsert("INSERT INTO phone_numbers ( " .
    "id, country_code, area_code, prefix, number, type, foreign_id " .
    ") VALUES ( " .
    generate_id()         . ", "  .
    "'+1'"                . ", "  .
    "'" . $phone_parts[1] . "', " .
    "'" . $phone_parts[2] . "', " .
    "'" . $phone_parts[3] . "', " .
    "'2'"                 . ", "  .
    $ins_id               . " "   .
   ")");
  }

  // Close this window and tell our opener to select the new company.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.set_insurance) opener.set_insurance($ins_id,'$ins_name');\n";
  echo "</script></body></html>\n";
  exit();
 }

 // Query x12_partners.
 $xres = sqlStatement(
  "SELECT id, name FROM x12_partners ORDER BY name"
 );
?>
<form method='post' name='theform' action='ins_search.php'
 onsubmit='return validate(this)'>
<center>

<p>
<table border='0' width='100%'>

 <!--
 <tr>
  <td valign='top' width='1%' nowrap>&nbsp;</td>
  <td>
   Note: Green fields are searchable.
  </td>
 </tr>
 -->

 <tr>
  <td valign='top' width='1%' nowrap><b><?php xl('Name','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_name' maxlength='35'
    class='search' style='width:100%' title=<?php xl('Name of insurance company','e');?> />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Attention','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_attn' maxlength='35'
    class='search' style='width:100%' title=".xl('Contact name')." />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Address1','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_addr1' maxlength='35'
    class='search' style='width:100%' title='First address line' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Address2','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_addr2' maxlength='35'
    class='search' style='width:100%' title='Second address line, if any' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('City/State','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_city' maxlength='25'
    class='search' title='City name' />
   &nbsp;
   <input type='text' size='3' name='form_state' maxlength='35'
    class='search' title='State or locality' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Zip/Country:','e'); ?></b></td>
  <td>
   <input type='text' size='20' name='form_zip' maxlength='10'
    class='search' title='Postal code' />
   &nbsp;
   <input type='text' size='20' name='form_country' value='USA' maxlength='35'
    title='Country name' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Phone','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_phone' maxlength='20'
    class='search' title='Telephone number' />
  </td>
 </tr>

 <!--
 <tr>
  <td valign='top' width='1%' nowrap>&nbsp;</td>
  <td>
   &nbsp;<br><b>Other data:</b>
  </td>
 </tr>
 -->

 <tr>
  <td valign='top' nowrap><b><?php xl('CMS ID','e');?>:</b></td>
  <td>
   <input type='text' size='20' name='form_cms_id' maxlength='15'
    class='search' title='Identifier assigned by CMS' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Payer Type','e');?>:</b></td>
  <td>
   <select name='form_freeb_type'>
<?php
 for ($i = 1; $i < count($freeb_type_array); ++$i) {
  echo "   <option value='$i'";
  // if ($i == $row['freeb_type']) echo " selected";
  echo ">" . $freeb_type_array[$i] . "\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('X12 Partner','e');?>:</b></td>
  <td>
   <select name='form_partner' title='Default X12 Partner'>
    <option value=""><?php xl('None','e','-- ',' --'); ?></option>
<?php
 while ($xrow = sqlFetchArray($xres)) {
  echo "   <option value='" . $xrow['id'] . "'";
  // if ($xrow['id'] == $row['x12_default_partner_id']) echo " selected";
  echo ">" . $xrow['name'] . "</option>\n";
 }
?>
   </select>
  </td>
 </tr>

</table>

<p>&nbsp;<br>
<input type='button' value='<?php xl('Search','e'); ?>' class='search' onclick='dosearch()' />
&nbsp;
<input type='submit' value='<?php xl('Save as New','e'); ?>' name='form_save' onmousedown='save_clicked=true' />
&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
