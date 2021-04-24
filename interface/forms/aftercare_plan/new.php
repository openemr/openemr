<?php

/**
 * aftercare_plan new.php
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
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form:AfterCare Planning");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_aftercare_plan", $formid) : array();

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
<p><span class="forms-title"><?php echo xlt('AfterCare Planning'); ?></span></p>
<br />
<?php
echo "<form method='post' name='my_form' " .
  "action='$rootdir/forms/aftercare_plan/save.php?id=" . attr_url($formid) . "'>\n";
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


  <td align="left" class="forms"><?php echo xlt('Admit Date'); ?>:</td>
        <td class="forms">
               <input type='text' size='10' class='datepicker' name='admit_date' id='admission_date' <?php echo attr($disabled); ?>;
               value='<?php echo attr($obj["admit_date"]); ?>'
               title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
        </td>
        <td align="left" class="forms"><?php echo xlt('Discharged'); ?>:</td>
        <td class="forms">
               <input type='text' size='10' class='datepicker' name='discharged' id='discharge_date' <?php echo attr($disabled); ?>;
      value='<?php echo attr($obj["discharged"]); ?>'
       title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
        </td>
    </tr>
    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
        <tr>

        <td class="forms-subtitle" colspan="4"><B><?php echo xlt('Goal and Methods');?></B></td>

    </tr>
    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>

        <td class="forms-subtitle" colspan="4"><B><?php echo xlt('Goal A');?>:</B>&nbsp;<?php echo xlt('Acute Intoxication/Withdrawal'); ?></td>

    </tr>
    <tr>
        <td align="right" class="forms">1.</td>
        <td colspan="3"><textarea name="goal_a_acute_intoxication" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_a_acute_intoxication"]);?></textarea></td>

    </tr>
    <tr>
        <td align="right" class="forms">2.</td>
        <td colspan="3"><textarea name="goal_a_acute_intoxication_I" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_a_acute_intoxication_I"]);?></textarea></td>

    </tr>
    <tr>
        <td align="right" class="forms">3.</td>
        <td colspan="3"><textarea name="goal_a_acute_intoxication_II" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_a_acute_intoxication_II"]);?></textarea></td>


    <tr>

        <td class="forms-subtitle" colspan="4"><B><?php echo xlt('Goal B');?>:</B>&nbsp;<?php  echo xlt('Emotional / Behavioral Conditions & Complications'); ?></td>

    </tr>
    <tr>
        <td align="right" class="forms">1.</td>
        <td colspan="3"><textarea name="goal_b_emotional_behavioral_conditions" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_b_emotional_behavioral_conditions"]);?></textarea></td>

    </tr>
    <tr>
        <td align="right" class="forms">2.</td>
        <td colspan="3"><textarea name="goal_b_emotional_behavioral_conditions_I" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_b_emotional_behavioral_conditions_I"]);?></textarea></td>

    </tr>


        <td class="forms-subtitle" colspan="4"><B><?php echo xlt('Goal C');?>:</B>&nbsp;<?php  echo xlt('Relapse Potential'); ?></td>

    </tr>
    <tr>
        <td align="right" class="forms">1.</td>
        <td colspan="3"><textarea name="goal_c_relapse_potential" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_c_relapse_potential"]);?></textarea></td>

    </tr>
    <tr>
        <td align="right" class="forms">2.</td>
        <td colspan="3"><textarea name="goal_c_relapse_potential_I" rows="2" cols="80" wrap="virtual name"><?php echo text($obj["goal_c_relapse_potential_I"]);?></textarea></td>

    </tr>

    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td></td>
    <td><input type='submit' value='<?php echo xla('Save'); ?>' class='button-css' />&nbsp;
    <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' class='button-css' />&nbsp;
    <input type='button' class='button-css' value='<?php echo xla('Cancel'); ?>'
 onclick="parent.closeTab(window.name, false)" /></td>
    </tr>
</table>
</form>
<?php
formFooter();
?>
