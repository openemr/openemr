<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once ("$srcdir/group.inc");

//formHeader("Form: group_attendance");
$returnurl = 'encounter_top.php';
//GET GROUP_ID, GET PARTICIPANTS IDS FROM GROUP, GET THEIR NAMES BY IDS, INTO $participants WHICH IS ARRAY WITH ID + NAME
$statuses_in_meeting = array(
    '10' => 'Not Reported',
    '20' => 'Attended',
    '30' => 'Did Not Attend',
    '40' => 'Late Arrival',
    '50' => 'Cancelled',
);
$encounter = 2;
$group_id = 2;
$participants = getParticipants($group_id);
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-jqui-1-10-11/css/dataTables.jqueryui.min.css" type="text/css">
<script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-min-1-9-1/index.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-ui-1-10-4/ui/jquery.ui.core.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-1-10-11/js/jquery.dataTables.min.js"></script>


</head>

<body class="body_top">
<form id="group_attendance_form" method=post action="<?php echo $rootdir;?>/forms/group_attendance/save.php?mode=new" name="my_form">
    <table id="group_attendance_form_table">
        <thead>
        <tr>
            <th><?php echo xl('Participant’s name'); ?></th>
            <th><?php echo xl('Patient’s number'); ?></th>
            <th><?php echo xl('Status in the meeting'); ?></th>
            <th><?php echo xl('Comment'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($participants as $participant){?>
            <tr>
                <td><?php echo text($participant['fname'] . ", " . $participant['lname']); ?></td>
                <td><?php echo $participant['pid']; ?></td>
                <td>
                    <select>
                        <?php foreach ($statuses_in_meeting as $key => $status_in_meeting){?>
                            <option value="<?php echo $key; ?>"><?php echo $status_in_meeting; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="text" name="participant_comment"></input></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</form>
<script>

</script>
<?php
formFooter();
?>
