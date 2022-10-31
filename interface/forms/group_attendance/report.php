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

function group_attendance_report($pid, $encounter, $cols, $id)
{

    global $therapy_group;

    $sql = "SELECT * FROM `form_group_attendance` WHERE id=? AND group_id = ? AND encounter_id = ?";
    $res = sqlStatement($sql, array($id,$therapy_group, $_SESSION["encounter"]));
    $form_data = sqlFetchArray($res);
    $group_data = getGroup($therapy_group);
    $group_name = $group_data['group_name'];

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
        <?php
    }
}
?>
