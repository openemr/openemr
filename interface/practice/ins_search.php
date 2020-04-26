<?php

/**
 * This module is used to find and add insurance companies.
 * It is opened as a popup window.  The opener may have a
 * JavaScript function named set_insurance(id, name), in which
 * case selecting or adding an insurance company will cause the
 * function to be called passing the ID and name of that company.
 *
 * When used for searching, this module will in turn open another
 * popup window ins_list.php, which lists the matched results and
 * permits selection of one of them via the same set_insurance()
 * function.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Putting a message here will cause a popup window to display it.
$info_msg = "";

// This is copied from InsuranceCompany.class.php.  It should
// really be in a SQL table.
$ins_type_code_array = array(''
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
<title><?php echo xlt('Insurance Company Search/Add');?></title>

<?php Header::setupHeader(['opener','topdialog']); ?>

<style>
td {
    font-size: 0.8125rem;
}

#form_entry {
    display: block;
}

#form_list {
    display: none;
}

</style>

<script>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // This is invoked when our Search button is clicked.
 function dosearch() {

    $("#form_entry").hide();
    var f = document.forms[0];
    var search_list = 'ins_list.php' +
   '?form_name='   + encodeURIComponent(f.form_name.value  ) +
   '&form_attn='   + encodeURIComponent(f.form_attn.value  ) +
   '&form_addr1='  + encodeURIComponent(f.form_addr1.value ) +
   '&form_addr2='  + encodeURIComponent(f.form_addr2.value ) +
   '&form_city='   + encodeURIComponent(f.form_city.value  ) +
   '&form_state='  + encodeURIComponent(f.form_state.value ) +
   '&form_zip='    + encodeURIComponent(f.form_zip.value   ) +
   '&form_phone='  + encodeURIComponent(f.form_phone.value ) +
   '&form_cms_id=' + encodeURIComponent(f.form_cms_id.value) +
   '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;

    top.restoreSession();
    $("#form_list").load( search_list ).show();

  return false;
 }

 // The ins_list.php window calls this to set the selected insurance.
 function set_insurance(ins_id, ins_name) {
  if (opener.closed || ! opener.set_insurance)
   alert('The target form was closed; I cannot apply your selection.');
  else
   opener.set_insurance(ins_id, ins_name);
   dlgclose('InsSaveClose',false);
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

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $ins_id = '';
    $ins_name = $_POST['form_name'];

    if ($ins_id) {
       // sql for updating could go here if this script is enhanced to support
       // editing of existing insurance companies.
    } else {
        $ins_id = generate_id();

        sqlStatement("INSERT INTO insurance_companies ( " .
        "id, name, attn, cms_id, ins_type_code, x12_receiver_id, x12_default_partner_id " .
        ") VALUES ( " .
        "'" . add_escape_custom($ins_id)                   . "', " .
        "'" . add_escape_custom($ins_name)                 . "', " .
        "'" . add_escape_custom($_POST['form_attn'])       . "', " .
        "'" . add_escape_custom($_POST['form_cms_id'])     . "', " .
        "'" . add_escape_custom($_POST['form_ins_type_code']) . "', " .
        "'" . add_escape_custom($_POST['form_partner'])    . "', " .
        "'" . add_escape_custom($_POST['form_partner'])    . "' "  .
        ")");

        sqlStatement("INSERT INTO addresses ( " .
        "id, line1, line2, city, state, zip, country, foreign_id " .
        ") VALUES ( " .
        "'" . add_escape_custom(generate_id())          . "', " .
        "'" . add_escape_custom($_POST['form_addr1'])   . "', " .
        "'" . add_escape_custom($_POST['form_addr2'])   . "', " .
        "'" . add_escape_custom($_POST['form_city'])    . "', " .
        "'" . add_escape_custom($_POST['form_state'])   . "', " .
        "'" . add_escape_custom($_POST['form_zip'])     . "', " .
        "'" . add_escape_custom($_POST['form_country']) . "', " .
        "'" . add_escape_custom($ins_id)                . "' " .
        ")");

        $phone_parts = array();
        preg_match(
            "/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
            $_POST['form_phone'],
            $phone_parts
        );

        sqlStatement("INSERT INTO phone_numbers ( " .
        "id, country_code, area_code, prefix, number, type, foreign_id " .
        ") VALUES ( " .
        "'" . add_escape_custom(generate_id())   . "', " .
        "'+1'"                . ", "  .
        "'" . add_escape_custom($phone_parts[1]) . "', " .
        "'" . add_escape_custom($phone_parts[2]) . "', " .
        "'" . add_escape_custom($phone_parts[3]) . "', " .
        "'2'"                 . ", "  .
        "'" . add_escape_custom($ins_id)         . "' "  .
        ")");
    }

  // Close this window and tell our opener to select the new company.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " top.restoreSession();\n";
    echo " if (opener.set_insurance) opener.set_insurance(" . js_escape($ins_id) . "," . js_escape($ins_name) . ");\n";
    echo " dlgclose();\n";
    echo "</script></body></html>\n";
    exit();
}

 // Query x12_partners.
 $xres = sqlStatement(
     "SELECT id, name FROM x12_partners ORDER BY name"
 );
    ?>
<div id="form_entry">

<form method='post' name='theform' action='ins_search.php' onsubmit='return validate(this)'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<center>

<p>
<table class="w-100 border-0">
 <tr>
  <td class="font-weight-bold" width='1%' nowrap><?php echo xlt('Name'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_name' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Name of insurance company'); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Attention');?>:</td>
  <td>
   <input type='text' size='20' name='form_attn' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Contact name'); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Address1'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_addr1' maxlength='35' class='form-control form-control-sm' title='First address line' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Address2'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_addr2' maxlength='35' class='form-control form-control-sm' title='Second address line, if any' />
  </td>
 </tr>

 <tr>
     <td class="font-weight-bold" nowrap><?php echo xlt('City/State'); ?>:</td>
     <td class="form-row">
         <div class="col">
             <input type='text' size='20' name='form_city' maxlength='25' class='form-control form-control-sm' title='City name' />
         </div>
         <div class="col">
             <input type='text' size='3' name='form_state' maxlength='35' class='form-control form-control-sm' title='State or locality' />
         </div>
     </td>
 </tr>

 <tr>
     <td class="font-weight-bold" nowrap><?php echo xlt('Zip/Country:'); ?></td>
     <td class="form-row">
         <div class="col">
             <input type='text' size='20' name='form_zip' maxlength='10' class='form-control form-control-sm' title='Postal code' />
         </div>
         <div class="col">
             <input type='text' size='20' class="form-control form-control-sm" name='form_country' value='USA' maxlength='35' title='Country name' />
         </div>
     </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Phone'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_phone' maxlength='20' class='form-control form-control-sm' title='Telephone number' />
  </td>
 </tr>
 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Payer ID'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_cms_id' maxlength='15' class='form-control form-control-sm' title='Identifier assigned by CMS' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Payer Type'); ?>:</td>
  <td>
   <select name='form_ins_type_code' class="form-control form-control-sm">
<?php
for ($i = 1; $i < count($ins_type_code_array); ++$i) {
    echo "   <option value='" . attr($i) . "'";
  // if ($i == $row['ins_type_code']) echo " selected";
    echo ">" . text($ins_type_code_array[$i]) . "\n";
}
?>
   </select>
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('X12 Partner'); ?>:</td>
  <td>
   <select name='form_partner' title='Default X12 Partner' class="form-control form-control-sm">
    <option value=""><?php echo '-- ' . xlt('None{{Partner}}') . ' --'; ?></option>
<?php
while ($xrow = sqlFetchArray($xres)) {
    echo "   <option value='" . attr($xrow['id']) . "'";
  // if ($xrow['id'] == $row['x12_default_partner_id']) echo " selected";
    echo ">" . text($xrow['name']) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

</table>

<input type='button' value='<?php echo xla('Search'); ?>' class='btn btn-primary' onclick='dosearch()' />
<input type='submit' value='<?php echo xla('Save as New'); ?>' class='btn btn-primary' name='form_save' onmousedown='save_clicked=true' />
<input type='button' value='<?php echo xla('Cancel'); ?>' class='btn btn-primary' onclick='window.close();'/>

</center>
</form>
</div>

<div id="form_list">
</div>

</body>
</html>
