<?php

/**
 * interface/forms/group_attendance/new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("functions.php");
require_once(dirname(__FILE__) . "/../../../library/group.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

//Check acl
$can_view = AclMain::aclCheckCore("groups", "gadd", false, 'view');
$can_edit = AclMain::aclCheckCore("groups", "gadd", false, 'write');

if (!$can_view && !$can_edit) {
    formJump();
}

$statuses_in_meeting = getGroupAttendanceStatuses();

$returnurl = 'encounter_top.php';

//If editing form, get participants from therapy_groups_participant_attendance table. Otherwise get from therapy_groups_participants table.
if (isset($_GET['id'])) {//clicked edit form
    $form_id = $_GET['id'];
} else {//In case didn't click 'edit' but an attendance form already exists (can't have 2 attendance forms for same encounter)
    $result = get_form_id_of_existing_attendance_form($encounter, $therapy_group);
    $form_id = $result['form_id'];
}


if ($form_id) {//If editing a form or the form already exists (inwhich case will automatically go into edit mode for existing form)
    $participants_sql =  "SELECT tgpa.*, p.fname, p.lname " .
        "FROM therapy_groups_participant_attendance as tgpa " .
        "JOIN patient_data as p ON tgpa.pid = p.id " .
        "WHERE tgpa.form_id = ?;";
    $result = sqlStatement($participants_sql, array($form_id));
    while ($p = sqlFetchArray($result)) {
        $participants[] = $p;
    }
} else {//new form
    $participants = getParticipants($therapy_group, true);
}

?>
<html>

<head>

    <?php Header::setupHeader(['datatables', 'datatables-dt', 'datatables-bs']); ?>

</head>

<body class="body_top">
<div class="container-fluid">
<?php if ($form_id) { ?>
<form id="group_attendance_form" method='post' onclick="top.restoreSession();" action="<?php echo $rootdir;?>/forms/group_attendance/save.php?mode=update&id=<?php echo attr_url($form_id) ;?>" name="my_form">
<?php } else { ?>
<form id="group_attendance_form" method='post' onclick="top.restoreSession();" action="<?php echo $rootdir;?>/forms/group_attendance/save.php?mode=new" name="my_form">
<?php } ?>
    <div id="add_participant">
        <div class="button_wrap">
            <span class='title'><?php echo xlt('Group Attendance Form'); ?></span>
            <input class="btn btn-primary add_button" type="button" value="<?php echo xla('Add'); ?>" <?php if (!$can_edit) {
                ?> disabled <?php } ?> />
        </div>
        <div id="add_participant_element"  style="display: none;">
            <div class="patient_wrap">
                <span class="input_label"><?php echo xlt("Participant's name");?></span>
                <input name="new_id" class="new_patient_id" type="hidden" value="" />
                <input name="new_patient" class="form-control new_patient" type="text" value=""  readonly />
                <div class="error_wrap">
                    <span class="error"></span>
                </div>
            </div>
            <div class="comment_wrap">
                <span class="input_label"><?php echo xlt("Comment");?></span>
                <input name="new_comment" class="form-control new_comment" type="text" value="" />
            </div>
            <div class="btn-group">
                <input class="btn btn-primary add_patient_button" type="button" value="<?php echo xla('Add Patient'); ?>" />
                <input class="btn btn-secondary cancel_button" type="button" value="<?php echo xla('Cancel'); ?>" />
            </div>
        </div>
    </div>
    <table id="group_attendance_form_table">
        <thead>
        <tr class="text-center">
            <th><?php echo xlt("Participant's name"); ?></th>
            <th><?php echo xlt("Patient's number"); ?></th>
            <th><?php echo xlt('Status in the meeting'); ?></th>
            <th><?php echo xlt('Comment'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($participants as $participant) {?>
            <tr>
                <td ><?php echo text($participant['fname'] . ", " . $participant['lname']); ?></td>
                <td ><?php echo text($participant['pid']); ?></td>
                <td >
                    <select class="form-control status_select" name="<?php echo "patientData[" . attr($participant['pid']) . "][status]" ;?>" <?php if (!$can_edit) {
                        ?> disabled <?php
                                                                     } ?> >
                        <?php foreach ($statuses_in_meeting as $status_in_meeting) {?>
                            <option value="<?php echo attr($status_in_meeting['option_id']); ?>" <?php if ($participant['meeting_patient_status'] == $status_in_meeting['option_id']) {
                                echo 'selected';
                                           }?> > <?php echo xlt($status_in_meeting['title']); ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td >
                    <input class="form-control comment" type="text" name="<?php echo "patientData[" . attr($participant['pid']) . "][comment]";  ?>" value="<?php echo attr($participant['meeting_patient_comment']) ;?>" <?php if (!$can_edit) {
                        ?> disabled <?php } ?> ></input>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="btn-group action_buttons">
        <input name="submit" class="btn btn-primary" type="submit" value="<?php echo xla('Save'); ?>" <?php if (!$can_edit) {
            ?> disabled <?php
                                                                          } ?> >
        <input class="btn btn-secondary cancel" type="button" value="<?php echo xla('Cancel'); ?>" />
    </div>
</form>
<script>
    $(function () {

        /* Initialise Datatable */
        var table = $('#group_attendance_form_table').DataTable({
            initComplete: function () {
                $('#group_attendance_form_table_filter').hide(); //hide searchbar
            },
            <?php // Bring in the translations ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });

        /* 'Add Participant' elements */
        $('.add_button').on('click', function () {
            $('#add_participant_element').show();
            $(this).hide();
        });

        $('.new_patient').on('click', function(){
            top.restoreSession();
            $('.new_patient').css("border-color", "var(--body-color)");
            $('.error_wrap .error').html("");
            var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_patient_popup.php';
            dlgopen(url, '_blank', 500, 400);
        });

        $('.cancel_button').on('click', function () {
            $('#add_participant_element').hide();
            $('.add_button').show();

            $('.new_patient_id').val('');
            $('.new_patient').val('');
            $('.new_comment').val('');
        });

        $('.add_patient_button').on('click', function(e){
            var name = $('.new_patient').val();

            if(name == ""){
                //If no patient was chosen (validation)
                $('.new_patient').css("border-color", "var(--danger)");
                var err_msg = <?php echo xlj("Choose Patient"); ?>;
                $('.error_wrap .error').html(err_msg);
            }
            else{

                // Get new participant details
                var new_patient_id = $('.new_patient_id').val();
                var new_patient_name = $('.new_patient').val();
                var new_patient_comment = $('.new_comment').val();

                //Check if patient already exists in form
                var ids_array = [];
                $('#group_attendance_form_table tbody tr td:nth-child(2)').each(function(){
                    ids_array.push($(this).text());
                });
                var exists = $.inArray(new_patient_id, ids_array);
                if(exists >= 0){
                    $('.new_patient').css("border-color", "var(--danger)");
                    var err_msg = <?php echo xlj("Patient already in form"); ?>;
                    $('.error_wrap .error').html(err_msg);
                    return;
                }

                //Get statuses from list into json and create select element
                var statuses = <?php echo json_encode($statuses_in_meeting); ?>;
                var select_element = $("<select class=\"form-control status_select\" name=\"\" />");
                $.each(statuses, function(key, value) {
                    $("<option />", {value: value.option_id, text: value.title}).appendTo(select_element);
                });
                var attended_sign = '@';
                select_element.attr('name', 'patientData[' + new_patient_id + '][status]');
                select_element.find("option[value='" + attended_sign +"']").attr('selected', 'selected');

                //Create comment element
                var comment_element = $("<input class=\"form-control comment\" type=\"text\" name=\"\" value=\"\">");
                comment_element.attr('name', 'patientData[' + new_patient_id + '][comment]');
                comment_element.attr('value', new_patient_comment);

                //Convert html object into string for insertion into datatable
                var select_html_string =  select_element.prop('outerHTML');
                var comment_html_string = comment_element.prop('outerHTML');
                if(select_html_string == undefined){ //firefox support
                    select_html_string = new XMLSerializer().serializeToString(select_element[0]);
                    comment_html_string = new XMLSerializer().serializeToString(comment_element[0]);
                }

                //Insert new row into datatable
                $('#group_attendance_form_table').dataTable().fnAddData([
                    new_patient_name,
                    new_patient_id,
                    select_html_string,
                    comment_html_string
                ]);

                //Empty values from inputs
                $('.new_patient').val("");
                $('.new_comment').val("");

            }
        });


        /* Form elements */
        $('.cancel').on('click', function () {
            top.restoreSession();
            parent.closeTab(window.name, false);
        });



    });

    /* For patient popup search */
    function setpatient(pid, lname, fname, dob){
        $('.new_patient_id').val(pid);
        $('.new_patient').val(fname + " " + lname);
    }


</script>
</div>
<?php
formFooter();
?>
