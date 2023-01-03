<?php

/**
 * transfer summary form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form:Transfer Summary");
$returnurl = 'encounter_top.php';
$formid = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_transfer_summary", $formid) : array();

?>
<html>
<head>

<?php Header::setupHeader('datetime-picker'); ?>

<script>
 $(function () {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
 });
</script>

</head>

<body class="body_top">
<p><span class="forms-title"><?php echo xlt('Transfer Summary'); ?></span></p>
<br />
<?php
echo "<form method='post' name='my_form' " .
  "action='$rootdir/forms/transfer_summary/save.php?id=" . attr_url($formid) . "'>\n";
?>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<table  border="0">
<tr>
<td align="left" class="forms" class="forms"><?php echo xlt('Client Name'); ?>:</td>
        <td class="forms">
            <label class="forms-data"> <?php if (is_numeric($pid)) {
                $result = getPatientData($pid, "fname,lname,squad");
                echo text($result['fname']) . " " . text($result['lname']);
                                       }

                                       $patient_name = ($result['fname']) . " " . ($result['lname']);
                                        ?>
   </label>
   <input type="hidden" name="client_name" value="<?php echo attr($patient_name);?>">
        </td>
        <td align="left"  class="forms"><?php echo xlt('DOB'); ?>:</td>
        <td class="forms">
        <label class="forms-data"> <?php if (is_numeric($pid)) {
            $result = getPatientData($pid, "*");
            echo text($result['DOB']);
                                   }

                                   $dob = ($result['DOB']);
                                    ?>
   </label>
     <input type="hidden" name="DOB" value="<?php echo attr($dob);?>">
        </td>
        </tr>
    <tr>

    <td align="left" class="forms"><?php echo xlt('Transfer to'); ?>:</td>

     <td class="forms">
         <input type="text" name="transfer_to" id="transfer_to"
        value="<?php echo text($obj["transfer_to"]);?>"></td>

        <td align="left" class="forms"><?php echo xlt('Transfer date'); ?>:</td>
        <td class="forms">
               <input type='text' size='10' class='datepicker' name='transfer_date' id='transfer_date' <?php echo attr($disabled)?>;
       value='<?php echo attr($obj["transfer_date"]); ?>'
       title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
        </td>

    </tr>

    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>

    <tr>
        <td align="left" class="forms"><b><?php echo xlt('Status Of Admission'); ?>:</b></td>
        <td colspan="3"><textarea name="status_of_admission" rows="3" cols="60" wrap="virtual name"><?php echo text($obj["status_of_admission"]);?></textarea></td>
        </tr>
        <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td align="left" class="forms"><b><?php echo xlt('Diagnosis'); ?>:</b></td>
        <td colspan="3"><textarea name="diagnosis" rows="3" cols="60" wrap="virtual name"><?php echo text($obj["diagnosis"]);?></textarea></td>
            </tr>
            <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td align="left" class="forms"><b><?php echo xlt('Intervention Provided'); ?>:</b></td>
        <td colspan="3"><textarea name="intervention_provided" rows="3" cols="60" wrap="virtual name"><?php echo text($obj["intervention_provided"]);?></textarea></td>
    </tr>
    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td align="left" class="forms"><b><?php echo xlt('Overall Status Of Discharge'); ?>:</b></td>
        <td colspan="3"><textarea name="overall_status_of_discharge" rows="3" cols="60" wrap="virtual name"><?php echo text($obj["overall_status_of_discharge"]);?></textarea></td>
    </tr>

<tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>

    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td></td>
    <td><input type='submit'  value='<?php echo xlt('Save');?>' class="button-css">&nbsp;
    <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />&nbsp;
    <input type='button' class="button-css" value='<?php echo xla('Cancel'); ?>'
 onclick="parent.closeTab(window.name, false)" />

 </td>
    </tr>
</table>
</form>

<?php
formFooter();
?>
