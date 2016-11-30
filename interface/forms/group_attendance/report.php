<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
include_once("{$GLOBALS['srcdir']}/group.inc");

function group_attendance_report($pid, $encounter, $cols, $id) {

    global $therapy_group;

    $sql = "SELECT * FROM `form_therapy_groups_attendance` WHERE id=? AND group_id = ? AND encounter_id = ?";
    $res = sqlStatement($sql, array($id,$therapy_group, $_SESSION["encounter"]));
    $form_data = sqlFetchArray($res);
    $group_data = getGroup($therapy_group);
    $group_name = $group_data['group_name'];

    if ($form_data) { ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Date'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Group'); ?></span></td>
            </tr>
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($form_data['date']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($group_name); ?></span></td>
            </tr>
        </table>
        <?php
    }
}
?>