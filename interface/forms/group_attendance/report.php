<?php

/**
 * interface/forms/group_attendance/report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once("{$GLOBALS['srcdir']}/group.inc.php");
require_once("functions.php");
function group_attendance_report($pid, $encounter, $cols, $id)
{

    global $therapy_group;
    $encounter = $_SESSION["encounter"];
    $sql = "SELECT * FROM `form_group_attendance` WHERE id=? AND group_id = ? AND encounter_id = ?";
    $res = sqlStatement($sql, array($id,$therapy_group, $encounter));
    $form_data = sqlFetchArray($res);
    $group_data = getGroup($therapy_group);
    $group_name = $group_data['group_name'];
    $result = get_form_id_of_existing_attendance_form($encounter, $therapy_group);

    $form_id = $result['form_id'];
    $participants = getGroupAttendance($form_id);
    if ($form_data) { ?>
        <table class="table table-bordered w-100">
            <tr class="text-center">
                <td><span class='font-weight-bold'><?php echo xlt('Date'); ?></span></td>
                <td><span class='font-weight-bold'><?php echo xlt('Group'); ?></span></td>
            </tr>
            <tr>
                <td><span class='text'><?php echo text($form_data['date']); ?></span></td>
                <td><span class='text'><?php echo text($group_name); ?></span></td>
            </tr>
        </table>
        <table class="table table-bordered w-100">
            <tr>
                <td><span class='font-weight-bold'><?php echo xlt('Participant'); ?></span></td>
                <td><span class='font-weight-bold'><?php echo xlt('Status'); ?></span></td>
                <td><span class='font-weight-bold'><?php echo xlt('Comments'); ?></span></td>
            </tr>
            <?php
            if ($participants) {
                foreach ($participants as $participant) {
                    $name = $participant['lname'] . ', ' . $participant['fname'];
                    $attnStatus = getAttendanceStatus($participant['meeting_patient_status'])
                    ?>
                    <tr>
                        <td><span class='text'><?php echo text($name); ?></span></td>
                        <td><span class='text'><?php echo text(xl_list_label($attnStatus)); ?></span></td>
                        <td width="65%"><span
                                class='text'><?php echo text($participant['meeting_patient_comment']); ?></span></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="3"><span class='text'><?php echo xlt('No participants'); ?></span></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
?>
