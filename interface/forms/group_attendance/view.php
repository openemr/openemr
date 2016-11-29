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
//will use $therapy_groups global
$group_id = 2;
//
$participants_sql = "SELECT tgpa.*, p.fname, p.lname FROM therapy_groups_participant_attendance as tgpa JOIN patient_data as p ON tgpa.pid = p.id WHERE tgpa.form_id = ?;";
$result = sqlStatement($participants_sql, array($_GET['id']));
while($p = sqlFetchArray($result)){
    $participants[] = $p;
}
?>
<html>

<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-jqui-1-10-11/css/dataTables.jqueryui.min.css" type="text/css">
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-min-1-9-1/index.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-ui-1-10-4/ui/jquery.ui.core.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-1-10-11/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $GLOBALS['web_root'];?>/library/dialog.js"></script>


</head>

<body class="body_top">
<div id="add_participant">
    <div class="button_wrap">
        <input class="add_button" type="button" value="<?php echo xla('Add'); ?>" class="button-css">
    </div>
    <div id="add_participant_element"  style="display: none;">
        <div class="patient_wrap">
            <span class="input_label"><?php echo xlt("Patient Name");?></span>
            <input class="patient_id" type="hidden" value="" class="button-css">
            <input class="patient" type="text" value="" class="button-css" readonly>
        </div>
        <div class="comment_wrap">
            <span class="input_label"><?php echo xlt("Comment");?></span>
            <input class="comment" type="text" value="" class="button-css">
        </div>
        <div class="button_wrap">
            <input class="add_patient_button" type="button" value="<?php echo xla('Add Patient'); ?>" class="button-css">
            <input class="cancel_button" type="button" value="<?php echo xla('Cancel'); ?>" class="button-css">
        </div>
    </div>
</div>
<form id="group_attendance_form" method=post action="<?php echo $rootdir;?>/forms/group_attendance/save.php?mode=update" name="my_form">
    <table id="group_attendance_form_table">
        <thead>
        <tr>
            <th align="center"><?php echo xl('Participant’s name'); ?></th>
            <th align="center"><?php echo xl('Patient’s number'); ?></th>
            <th align="center"><?php echo xl('Status in the meeting'); ?></th>
            <th align="center"><?php echo xl('Comment'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($participants as $participant){?>
            <tr>
                <td align="center"><?php echo text($participant['fname'] . ", " . $participant['lname']); ?></td>
                <td align="center"><?php echo $participant['pid']; ?></td>
                <td align="center">
                    <select name="<?php echo "patientData[" . $participant['pid'] . "]['status']" ;?>">
                        <?php foreach ($statuses_in_meeting as $key => $status_in_meeting){?>
                            <option value="<?php echo $key; ?>"><?php echo $status_in_meeting; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td align="center"><input type="text" name="<?php echo "patientData[" . $participant['pid'] . "]['comment']";  ?>"></input></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="action_buttons">
        <input type="submit" value="<?php echo xla('Save'); ?>" class="button-css">
        <input class="cancel" type="button" value="<?php echo xla('Cancel'); ?>" class="button-css">
    </div>
</form>
<script>
    $(document).ready(function () {
        /* Initialise Datatable */
        var table = $('#group_attendance_form_table').DataTable({
            language: {
            },
            initComplete: function () {
                $('#group_attendance_form_table_filter').hide(); //hide searchbar
            }
        });

        $('.cancel').click(function () {
            top.restoreSession();
            var srcdir = "<?php echo $GLOBALS['rootdir'];?>";
            var url = srcdir + "/patient_file/encounter/encounter_top.php";
            window.location = url;
        });

        $('.add_button').click(function () {
            $('#add_participant_element').show();
            $(this).hide();
        });

        $('.cancel_button').click(function () {
            $('#add_participant_element').hide();
            $('.add_button').show();
        });

        $('.patient').on('focus', function(){
            var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_patient_popup.php';
            dlgopen(url, '_blank', 500, 400);
        });

    });

    function setpatient(pid, lname, fname, dob){
        $('.patient_id').val(pid);
        $('.patient').val(fname + " " + lname);
    }
</script>
<?php
formFooter();
?>
