<?php

/**
 * treatment plan form.
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

formHeader("Form:Treatment Planning");
$returnurl = 'encounter_top.php';
$formid = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_treatment_plan", $formid) : array();

// Get the providers list.
 $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE " .
  "authorized != 0 AND active = 1 ORDER BY lname, fname");
    ?>
<html><head>

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
<p><span class="forms-title"><?php echo xlt('Treatment Planning'); ?></span></p>
<br />
<?php
echo "<form method='post' name='my_form' " .
  "action='$rootdir/forms/treatment_plan/save.php?id=" . attr_url($formid) . "'>\n";
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
      <td align="left"  class="forms"><?php echo xlt('Client Number'); ?>:</td>
        <td class="forms">
            <label class="forms-data" > <?php if (is_numeric($pid)) {
                $result = getPatientData($pid, "*");
                echo text($result['pid']);
                                        }

                                        $patient_id = $result['pid'];
                                        ?>
   </label>
    <input type="hidden" name="client_number" value="<?php echo attr($patient_id);?>">
        </td>


        <td align="left" class="forms"><?php echo xlt('Admit Date'); ?>:</td>
        <td class="forms">
               <input type='text' size='10' class='datepicker' name='admit_date' id='admission_date' <?php echo attr($disabled) ?>;
               value='<?php echo attr($obj["admit_date"]); ?>'
               title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
        </td>

        </tr>
        <tr>
        <td align="left" class="forms"><?php echo xlt('Provider'); ?>:</td>
         <td class="forms" width="280px">
    <?php

    echo "<select name='provider' style='width:60%' />";
    while ($urow = sqlFetchArray($ures)) {
        echo "    <option value='" . attr($urow['lname']) . "'";
        if ($urow['lname'] == attr($obj["provider"])) {
            echo " selected";
        }

        echo ">" . text($urow['lname']);
        if ($urow['fname']) {
            echo ", " . text($urow['fname']);
        }

        echo "</option>\n";
    }

    echo "</select>";
    ?>
        </td>

        </tr>

    <tr>

  <td colspan='3' nowrap style='font-size:8pt'>
   &nbsp;
    </td>
    </tr>

    <tr>
        <td align="left" class="forms"><?php echo xlt('Presenting Issue(s)'); ?>:</td>
        <td colspan="3"><textarea name="presenting_issues" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["presenting_issues"]);?></textarea></td>

    </tr>
    <tr>
        <td align="left" class="forms"><?php echo xlt('Patient History'); ?>:</td>
        <td colspan="3"><textarea name="patient_history" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["patient_history"]);?></textarea></td>

    </tr>
    <tr>

        <td align="left" class="forms"><?php echo xlt('Medications'); ?>:</td>
        <td colspan="3"><textarea name="medications" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["medications"]);?></textarea></td>


    </tr>
    <tr>
        <td align="left" class="forms"><?php echo xlt('Anyother Relevant Information'); ?>:</td>
        <td colspan="3"><textarea name="anyother_relevant_information" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["anyother_relevant_information"]);?></textarea></td>

    </tr>
    <tr>
        <td align="left" class="forms"><?php echo xlt('Diagnosis'); ?>:</td>
        <td colspan="3"><textarea name="diagnosis" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["diagnosis"]);?></textarea></td>

    </tr>
    <tr>
        <td align="left" class="forms"><?php echo xlt('Treatment Received'); ?>:</td>
        <td colspan="3"><textarea name="treatment_received" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["treatment_received"]);?></textarea></td>

    </tr>
    <tr>
        <td align="left" class="forms"><?php echo xlt('Recommendation For Follow Up'); ?>:</td>
        <td colspan="3"><textarea name="recommendation_for_follow_up" rows="2" cols="60" wrap="virtual name"><?php echo text($obj["recommendation_for_follow_up"]);?></textarea></td>

    </tr>
    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td align="left colspan="3" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td></td>
    <td><input type='submit'  value='<?php echo xla('Save');?>' class="button-css">&nbsp;
  <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />&nbsp;

    <input type='button' class="button-css" value='<?php echo xla('Cancel');?>'
 onclick="parent.closeTab(window.name, false)" /></td>
    </tr>
</table>
</form>
<?php
formFooter();
?>
