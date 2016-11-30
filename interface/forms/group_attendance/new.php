<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once ("$srcdir/group.inc");
include_once ("statuses.php");

$returnurl = 'encounter_top.php';

$participants = getParticipants($therapy_group);
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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/group_attendance/js/functions.js"></script>



</head>

<body class="body_top">

<form id="group_attendance_form" method=post action="<?php echo $rootdir;?>/forms/group_attendance/save.php?mode=new" name="my_form">
    <div id="add_participant">
        <div class="button_wrap">
            <input class="add_button" type="button" value="<?php echo xla('Add'); ?>" class="button-css">
        </div>
        <div id="add_participant_element"  style="display: none;">
            <div class="patient_wrap">
                <span class="input_label"><?php echo xlt("Patient Name");?></span>
                <input name="new_id" class="patient_id" type="hidden" value="" class="button-css">
                <input name="new_patient" class="patient" type="text" value="" class="button-css" readonly>
                <div class="error_wrap">
                    <span class="error"></span>
                </div>
            </div>
            <div class="comment_wrap">
                <span class="input_label"><?php echo xlt("Comment");?></span>
                <input name="new_comment" class="comment" type="text" value="" class="button-css">
            </div>
            <div class="button_wrap">
                <input name="submit_new_patient" class="add_patient_button button-css" type="submit" value="<?php echo xla('Add Patient'); ?>">
                <input class="cancel_button button-css" type="button" value="<?php echo xla('Cancel'); ?>" >
            </div>
        </div>
    </div>
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
                    <select class= "status_select" name="<?php echo "patientData[" . $participant['pid'] . "][status]" ;?>">
                        <?php foreach ($statuses_in_meeting as $key => $status_in_meeting){?>
                            <option value="<?php echo $key; ?>"><?php echo $status_in_meeting; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td align="center"><input type="text" name="<?php echo "patientData[" . $participant['pid'] . "][comment]";  ?>"></input></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="action_buttons">
        <input name="submit" type="submit" value="<?php echo xla('Save'); ?>" class="button-css">
        <input class="cancel button-css" type="button" value="<?php echo xla('Cancel'); ?>">
    </div>
</form>
<script>
    $(document).ready(function () {

        $('.cancel').click(function () {
            top.restoreSession();
            var srcdir = "<?php echo $GLOBALS['rootdir'];?>";
            var url = srcdir + "/patient_file/encounter/encounter_top.php";
            window.location = url;
        });

        $('.patient').on('focus', function(){
            var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_patient_popup.php';
            dlgopen(url, '_blank', 500, 400);
        });

    });

</script>

<?php
formFooter();
?>
