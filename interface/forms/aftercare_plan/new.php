<?php

/*
 * aftercare_plan new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Sherwin Gaddis <sherwingaddis@gmail.com>
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
$formid = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_aftercare_plan", $formid) : array();

?>
<!DOCTYPE>
<html lang="eng">
<head>
<title>
    <?php echo xlt('After Care Planning'); ?>
</title>
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
                <?php // can add any additional javascript settings to datetimepicker here;
                // need to prepend first setting with a comma ?>
            });
        });
    </script>
</head>
<body class="body_top">
<div class="container">
    <div class="row">

                <h1 class="forms-title"><?php echo xlt('After Care Planning'); ?></h1>

        <div class="col-md-12 mt-4">
            <form method="post" name="my_form" action="<?php echo $form_action;?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <table class="table">
                <tr>
                    <td  class="forms">
                        <?php echo xlt('Client Name'); ?>:
                    </td>
                    <td class="forms">
                        <label class="forms-data">
                            <?php
                                $result = getPatientData($pid, "fname,lname,squad");
                                echo text($result['fname']) . " " . text($result['lname']);
                                $patient_name = ($result['fname']) . " " . ($result['lname']);
                            ?>
                        </label>
                        <input type="hidden" name="client_name" value="<?php echo attr($patient_name);?>">
                    </td>
                    <td   class="forms"><?php echo xlt('DOB'); ?>:</td>
                    <td class="forms">
                        <label class="forms-data">
                            <?php
                                 $result = getPatientData($pid, "*");
                                 echo text($result['DOB']);
                                 $dob = ($result['DOB']);
                            ?>
                        </label>
                        <input type="hidden" name="DOB" value="<?php echo attr($dob);?>">
                    </td>
                </tr>
                <tr>
                    <td  class="forms"><?php echo xlt('Admit Date'); ?>:</td>
                    <td class="forms">
                        <input type='text' size='10' class='datepicker form-control'
                               name='admit_date' id='admission_date' <?php echo attr($disabled); ?>
                               value='<?php echo attr($obj["admit_date"]); ?>'
                               title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                    </td>
                    <td  class="forms"><?php echo xlt('Discharged'); ?>:</td>
                    <td class="forms">
                        <input type='text' size='10' class='datepicker form-control'
                               name='discharged' id='discharge_date' <?php echo attr($disabled); ?>
                               value='<?php echo attr($obj["discharged"]); ?>'
                               title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                    </td>
                </tr>
                <tr>
                    <td class="forms-subtitle" colspan="4"><strong><?php echo xlt('Goal and Methods');?></strong></td>
                </tr>
                <tr>
                    <td class="forms-subtitle" colspan="4">
                        <strong><?php echo xlt('Goal A');?>:</strong>
                        <?php echo xlt(' Acute Intoxication/Withdrawal'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="forms">1.</td>
                    <td colspan="3">
                        <textarea name="goal_a_acute_intoxication" rows="2"
                                  class="form-control"
                                  cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_a_acute_intoxication"]);?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="forms">2.</td>
                    <td colspan="3">
                        <textarea name="goal_a_acute_intoxication_I"
                                  class="form-control"
                                  rows="2" cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_a_acute_intoxication_I"]);?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="forms">3.</td>
                    <td colspan="3">
                        <textarea name="goal_a_acute_intoxication_II"
                                  class="form-control"
                                  rows="2" cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_a_acute_intoxication_II"]);?></textarea>
                    </td>
                <tr>
                    <td class="forms-subtitle" colspan="4">
                        <strong><?php echo xlt('Goal B');?>:</strong>
                        <?php  echo xlt(' Emotional / Behavioral Conditions & Complications'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="forms">1.</td>
                    <td colspan="3">
                        <textarea
                                name="goal_b_emotional_behavioral_conditions"
                                class="form-control"
                                rows="2" cols="80" wrap="virtual name"><?php
                                echo text($obj["goal_b_emotional_behavioral_conditions"]);?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="forms">2.</td>
                    <td colspan="3">
                        <textarea name="goal_b_emotional_behavioral_conditions_I"
                                  class="form-control"
                                  rows="2" cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_b_emotional_behavioral_conditions_I"]);?></textarea>
                    </td>
                </tr>

                <tr>
                    <td class="forms-subtitle" colspan="4">
                        <label for="goal_c"> <strong><?php echo xlt('Goal C');?>:</strong></label>
                        <?php  echo xlt('Relapse Potential'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="forms">1.</td>
                    <td colspan="3">
                        <textarea name="goal_c_relapse_potential"
                                  class="form-control"
                                  rows="2" cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_c_relapse_potential"]);?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="forms">2.</td>
                    <td colspan="3">
                        <textarea name="goal_c_relapse_potential_I"
                                  class="form-control"
                                  rows="2" cols="80" wrap="virtual name"><?php
                                    echo text($obj["goal_c_relapse_potential_I"]);?></textarea>
                    </td>

                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div class="btn-group">
                            <button class='btn btn-primary btn-save'><?php echo xla('Save'); ?></button>&nbsp
                            <button class='btn btn-secondary' id='printbutton'><?php echo xla('Print'); ?></button>
                            <button type='button' class='btn btn-cancel' onclick="parent.closeTab(window.name, false)">
                            <?php echo xla('Cancel'); ?></button>
                        </div>
                    </td>
                </tr>
            </table>
            </form>
        </div>
    </div>
</div>
<?php
formFooter();
