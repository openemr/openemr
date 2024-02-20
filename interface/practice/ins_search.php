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
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\{
    AddressService,
    InsuranceCompanyService,
    PhoneNumberService
};

// Putting a message here will cause a popup window to display it.
$info_msg = "";

// Grab insurance type codes from service
$insuranceCompany = new InsuranceCompanyService();
$phoneNumber = new PhoneNumberService();
$ins_type_code_array = $insuranceCompany->getInsuranceTypes();

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
     window.top.restoreSession(); // make sure to restore the session before we do anything else
     if (!window.opener) {
         return; // nothing to do here as somehow we got here without the opener
     }
     let postMessage = {
         action: 'insurance-search-set-insurance'
         ,insuranceId: ins_id
         ,insuranceName: ins_name
     };
     // fire off a message so we can decouple things so we don't have to have a specific function
     // name in the global scope of the opener
     opener.postMessage(postMessage, window.location.origin);
    if (opener.closed) {
      alert('The target form was closed; I cannot apply your selection.');
    }
    else if (opener.set_insurance) {
      opener.set_insurance(ins_id, ins_name);
      dlgclose('InsSaveClose', false);
    } else {
        // if we don't have a set_insurance function then we will just close the window as the opener is
        // using post message to receive events.
        dlgclose('InsSaveClose', false);
    }
 }

 // This is set to true on a mousedown of the Save button.  The
 // reason is so we can distinguish between clicking on the Save
 // button vs. hitting the Enter key, as we prefer the "default"
 // action to be search and not save.
 var save_clicked = false;
 let update_clicked = false;

 // Onsubmit handler.
 function validate(f) {
  // If save was not clicked then default to searching.
  if (!(save_clicked || update_clicked)) return dosearch();
  save_clicked = false;

  msg = '';
  if (update_clicked && !f.form_id.value.length) msg += 'Id is missing for Update \n';
  if (! f.form_name.value.length ) msg += 'Company name is missing. ';
  if (! f.form_addr1.value.length) msg += 'Address is missing. ';
  if (! f.form_city.value.length ) msg += 'City is missing. ';
  if (! f.form_state.value.length) msg += 'State is missing. ';
  if (! f.form_zip.value.length  ) msg += 'Zip is missing.';
  update_clicked = false;

  if (msg) {
   alert(msg);
   return false;
  }

  top.restoreSession();
  return true;
 }

function clearForm() {
  let f = document.forms[0];
  f.form_id.value = '';
  f.form_name.value = '';
  f.form_attn.value = '';
  f.form_addr1.value = '';
  f.form_addr2.value = '';
  f.form_city.value = '';
  f.form_state.value = '';
  f.form_country.value = '';
  f.form_zip.value = '';
  f.form_phone.value = '';
  f.form_cms_id.value = '';
  f.form_ins_type_code.value = '';
  f.form_partner.value = '';
  f.form_cqm_sop.value = '';
}

</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if (
    ($_POST['form_save'] ?? '')
    || ($_POST['form_update'] ?? '')
) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if (($_POST['form_save'] ?? '') == 'Save as New') {
        $ins_id = '';
    } else {
        $ins_id = $_POST['form_id'];
    }
    $ins_name = $_POST['form_name'];

    if ($ins_id) {
       // sql for updating could go here if this script is enhanced to support
       // editing of existing insurance companies.
        $insuranceCompany->update(
            array(
            'name' => $ins_name,
            'attn' => $_POST['form_attn'],
            'cms_id' => $_POST['form_cms_id'],
            'ins_type_code' => $_POST['form_ins_type_code'],
            'x12_receiver_id' => $_POST['form_x12_receiver'] ?? null,
            'x12_default_partner_id' => $_POST['form_partner'],
            'alt_cms_id' => null,
            'line1' => $_POST['form_addr1'],
            'line2' => $_POST['form_addr2'],
            'city' => $_POST['form_city'],
            'state' => $_POST['form_state'],
            'zip' => $_POST['form_zip'],
            'country' => $_POST['form_country'],
            'phone' => $_POST['form_phone'],
            'foreign_id' => $ins_id,
            'cqm_sop' => $_POST['form_cqm_sop']
            ),
            $ins_id
        );
    } else {
        $ins_id = $insuranceCompany->insert(
            array(
                'name' => $ins_name,
                'attn' => $_POST['form_attn'],
                'cms_id' => $_POST['form_cms_id'],
                'ins_type_code' => $_POST['form_ins_type_code'],
                'x12_receiver_id' => $_POST['form_receiver'] ?? null,
                'x12_default_partner_id' => $_POST['form_partner'],
                'alt_cms_id' => null,
                'line1' => $_POST['form_addr1'],
                'line2' => $_POST['form_addr2'],
                'city' => $_POST['form_city'],
                'state' => $_POST['form_state'],
                'zip' => $_POST['form_zip'],
                'country' => $_POST['form_country'],
                'phone' => $_POST['form_phone'],
                'foreign_id' => $ins_id,
                'cqm_sop' => $_POST['form_cqm_sop']
            )
        );
    }

  // Close this window and tell our opener to select the new company.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    // we need to follow the global settings for the display of this name so we will return the name in the set_insurance method
    $ins_name = (new InsuranceCompanyService())->getInsuranceDisplayName($ins_id);
    // call the set_insurance method in our header
    echo " set_insurance(" . js_escape($ins_id) . "," . js_escape($ins_name) . ");\n";
    echo "</script></body></html>\n";
    exit();
} else {
    $ins_co = (new InsuranceCompanyService())->getOneById($_GET['ins']) ?? null;
    $ins_co_address = (new AddressService())->getOneByForeignId($_GET['ins']) ?? null;
    $ins_co_phone = (new PhoneNumberService())->getOneByForeignId($_GET['ins']) ?? null;
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
  <td class="font-weight-bold" width='1%' nowrap><?php echo xlt('Id'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_id' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Id of insurance company'); ?>'
       readonly='readonly' value='<?php echo attr($ins_co['id'] ?? ''); ?>' />
  </td>
 </tr>
 <tr>
  <td class="font-weight-bold" width='1%' nowrap><?php echo xlt('Name'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_name' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Name of insurance company'); ?>'
       value='<?php echo attr($ins_co['name'] ?? ''); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Attention');?>:</td>
  <td>
   <input type='text' size='20' name='form_attn' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Contact name'); ?>'
       value='<?php echo attr($ins_co['attn'] ?? ''); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Address1'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_addr1' maxlength='35' class='form-control form-control-sm' title='First address line'
       value='<?php echo attr($ins_co_address['line1'] ?? ''); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Address2'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_addr2' maxlength='35' class='form-control form-control-sm' title='Second address line, if any'
       value='<?php echo attr($ins_co_address['line2'] ?? ''); ?>' />
  </td>
 </tr>

 <tr>
     <td class="font-weight-bold" nowrap><?php echo xlt('City/State'); ?>:</td>
     <td class="form-row">
         <div class="col">
             <input type='text' size='20' name='form_city' maxlength='25' class='form-control form-control-sm' title='City name'
                 value='<?php echo attr($ins_co_address['city'] ?? ''); ?>' />
         </div>
         <div class="col">
             <input type='text' size='3' name='form_state' maxlength='35' class='form-control form-control-sm' title='State or locality'
                 value='<?php echo attr($ins_co_address['state'] ?? ''); ?>' />
         </div>
     </td>
 </tr>

 <tr>
     <td class="font-weight-bold" nowrap><?php echo xlt('Zip/Country:'); ?></td>
     <td class="form-row">
         <div class="col">
             <input type='text' size='20' name='form_zip' maxlength='10' class='form-control form-control-sm' title='Postal code'
                 value='<?php echo attr(($ins_co_address['zip'] ?? '') . ($ins_co_address['plus_four'] ?? '')); ?>' />
         </div>
         <div class="col">
             <input type='text' size='20' class="form-control form-control-sm" name='form_country' value='USA' maxlength='35' title='Country name'
                 value='<?php echo attr($ins_co_address['country'] ?? ''); ?>' />
         </div>
     </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Phone'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_phone' maxlength='20' class='form-control form-control-sm' title='Telephone number'
       value='<?php echo attr((
         ($ins_co_phone['area_code'] ?? '') .
         ($ins_co_phone['prefix'] ?? '') .
         ($ins_co_phone['number'] ?? '')
       )); ?>'
    />
  </td>
 </tr>
 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Payer ID'); ?>:</td>
  <td>
   <input type='text' size='20' name='form_cms_id' maxlength='15' class='form-control form-control-sm' title='Identifier assigned by CMS'
     value='<?php echo attr($ins_co['cms_id'] ?? ''); ?>' />
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('Payer Type'); ?>:</td>
  <td>
   <select name='form_ins_type_code' class="form-control form-control-sm">
<?php
for ($i = 1; $i < count($ins_type_code_array); ++$i) {
    echo "   <option value='" . attr($i) . "'";
    if (!empty($ins_co)) {
        if ($i == $ins_co['ins_type_code'] ?? '') {
            echo " selected";
        }
    }
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
    if ($xrow['id'] == $ins_co['x12_default_partner_id']) {
        echo " selected";
    }
    echo ">" . text($xrow['name']) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr>
  <td class="font-weight-bold" nowrap><?php echo xlt('CQM Source of Payment'); ?>:</td>
  <td>
   <select name='form_cqm_sop' title='CQM Source of Payment' class="form-control form-control-sm">
    <option value=""><?php echo '-- ' . xlt('None{{CQM SOP}}') . ' --'; ?></option>
<?php
$cqm_sop_array = $insuranceCompany->getInsuranceCqmSop();
foreach ($cqm_sop_array as $key => $value) {
    echo "   <option value='" . attr($key) . "'";
    echo ">" . text($value) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

</table>

<input type='button' value='<?php echo xla('Search'); ?>' class='btn btn-primary' onclick='dosearch()' />
<input type='submit' value='<?php echo xla('Save as New'); ?>' class='btn btn-primary' name='form_save' onmousedown='save_clicked=true' />
<input type='submit' value='<?php echo xla('Update'); ?>' class='btn btn-primary' name='form_update' onmousedown='update_clicked=true' />
<input type='button' value='<?php echo xla('Clear'); ?>' class='btn btn-primary' onclick='clearForm()' />
<input type='button' value='<?php echo xla('Cancel'); ?>' class='btn btn-primary' onclick='window.close();'/>

</center>
</form>
</div>

<div id="form_list">
</div>

</body>
</html>
