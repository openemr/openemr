<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
//formHeader("Form: group_attendance");
$returnurl = 'encounter_top.php';
//GET GROUP_ID, GET PARTICIPANTS IDS FROM GROUP, GET THEIR NAMES BY IDS, INTO $participants WHICH IS ARRAY WITH ID + NAME
$participants = '';
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
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
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</form>
<?php
formFooter();
?>
