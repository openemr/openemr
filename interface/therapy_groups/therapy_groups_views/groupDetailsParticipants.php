<?php

/**
 * interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php contains group participants details view .
 *
 * This is the therapy group's participants detail screen for the chosen group.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>.
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;

?>
<?php $edit = AclMain::aclCheckCore("groups", "gadd", false, 'write');?>
<?php $edit_encounter = AclMain::aclCheckCore("groups", "glog", false, 'write');?>
<?php $view = AclMain::aclCheckCore("groups", "gadd", false, 'view');?>

<?php require 'header.php'; ?>
<?php if ($view || $edit) {?>
<main id="group-details">
    <div class="container-group">
        <span class="hidden title"><?php echo text($groupName);?></span>
        <div class="row">
            <div id="main-component" class="col-md-9 col-sm-12">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <ul class="tabNav">
                            <li><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . attr_url($groupId); ?>"><?php echo xlt('General data');?></a></li>
                            <li class="current"><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . attr_url($groupId); ?>"><?php echo xlt('Participants ');?></a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <?php if ($edit) {?>
                            <?php if ($edit_encounter) { ?>
                                <button class="btn btn-primary" onclick="newGroup()"><?php echo xlt('Add encounter'); ?></button>
                            <?php }?>
                            <?php if ($readonly == '') { ?>
                            <button class="btn btn-secondary" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . attr_url($groupId); ?>'"><?php echo xlt('Cancel');?></button>
                            <button class="btn btn-primary" id="saveForm"><?php echo xlt('Save');?></button>
                        <?php } else { ?>
                            <button class="btn btn-primary" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&editParticipants=1&group_id=' . attr_url($groupId); ?>'"><?php echo xlt('Update');?></button>
                        <?php } ?>
                      <?php } ?>
                    </div>
                </div>
              <div id="component-border">
                  <div class="row">
                      <form id="add-participant-form" name="add-participant-form" class="<?php echo isset($addStatus) ? 'showAddForm' : '' ?>" action="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=addParticipant&group_id=' . attr_url($groupId)?>" method="post">
                          <input type="hidden" id="pid" name="pid" value="<?php echo !is_null($participant_data) ? attr($participant_data['pid']) : ''?>">
                          <div class="col-md-12">
                              <div class="row">
                                  <div class="col-md-5">
                                      <div class="row">
                                          <label class="col-form-label col-md-4 font-weight-bold"><?php echo xlt("Participant's name"); ?>:</label>
                                          <div class="col-md-8">
                                              <input type="text" id="participant_name" name="participant_name" class="form-control" value="<?php echo !is_null($participant_data) ? attr($participant_data['participant_name']) : ''?>" readonly />
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-md-5">
                                      <div class="row">
                                          <label class="col-form-label col-md-4 font-weight-bold"><?php echo xlt('Date of registration'); ?>:</label>
                                          <div class="col-md-8">
                                              <input type="text" id="group_patient_start" name="group_patient_start" class="w-100 form-control datepicker" value="<?php echo !is_null($participant_data) ? attr(oeFormatShortDate($participant_data['group_patient_start'])) : oeFormatShortDate(date('Y-m-d'));?>" />
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="row">
                                  <label class="col-form-label col-md-2 font-weight-bold"><?php echo xlt('Comment'); ?>:</label>
                                  <div class="col-md-8">
                                      <input type="text" id="group_patient_comment" name="group_patient_comment" value="<?php echo !is_null($participant_data) ? attr($participant_data['group_patient_comment']) : ''?>" class="form-control" />
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="offset-md-4 col-md-4 text-center">
                                      <?php if ($edit) { ?>
                                      <input type="submit" class="btn btn-primary" name="save_new" value="<?php echo xla('Adding a participant'); ?>" />
                                      <input id="cancelAddParticipant" class="btn btn-secondary" type="button" value="<?php echo xla('Cancel'); ?>" />
                                    <?php } ?>
                                  </div>
                              </div>
                              <?php if (isset($message)) { ?>
                              <div class="row">
                                  <div class="col-md-8">
                                      <p class="<?php echo $addStatus == 'failed' ? 'groups-error-msg' : 'groups-success-msg' ?>"><?php echo text($message); ?></p>
                                  </div>
                              </div>
                            <?php } ?>
                              <hr/>
                          </div>
                      </form>
                  </div>
                  <form id="updateParticipants" method="post">
                      <input type="hidden" name="group_id" value="<?php echo attr($groupId); ?>" />
                      <?php if ($edit) { ?>
                      <button class="btn btn-primary" id="addParticipant"><?php echo xlt('Add'); ?></button>
                      <?php }?>
                      <table id="participants_table" class="dataTable display">
                          <thead>
                          <tr>
                              <th><?php echo xlt("Participant's name"); ?></th>
                              <th><?php echo xlt("Patient's number"); ?></th>
                              <th><?php echo xlt('Status in the group'); ?></th>
                              <th><?php echo xlt('Date of registration'); ?></th>
                              <th><?php echo xlt('Date of exit'); ?></th>
                              <th><?php echo xlt('Comment'); ?></th>
                              <?php if ($readonly == '') { ?>
                                  <th><?php echo xlt('Delete'); ?></th>
                              <?php } ?>
                          </tr>
                          </thead>
                          <tbody>
                          <?php foreach ($participants as $i => $participant) { ?>
                              <tr>
                                  <td>
                                      <input type="hidden" name="pid[]" value="<?php echo attr($participant['pid']); ?>" />
                                      <span><?php echo text($participant['lname']) . ', ' . text($participant['fname']); ?></span>
                                  </td>
                                  <td><span><?php echo text($participant['pid']); ?></span></td>
                                  <td>
                                      <select name="group_patient_status[]" <?php echo $readonly; ?>>
                                          <?php foreach ($statuses as $key => $status) { ?>
                                              <option value="<?php echo attr($key);?>" <?php if ($key == $participant['group_patient_status']) {
                                                    echo 'selected';
                                                             } ?> > <?php echo text($status); ?> </option>
                                          <?php } ?>
                                      </select>
                                  </td>
                                  <td><input type="text" name="group_patient_start[]" id="start-date<?php echo $i + 1?>" class="datepicker"  value="<?php echo attr(oeFormatShortDate($participant['group_patient_start']));?>" <?php echo $readonly; ?> /></td>
                                  <td><input type="text" name="group_patient_end[]" id="end-date<?php echo $i + 1?>" class="datepicker" value="<?php echo $participant['group_patient_end'] == '0000-00-00' ? '' : attr(oeFormatShortDate($participant['group_patient_end'])) ;?>" <?php echo $readonly; ?> /></td>
                                  <td><input type="text" name="group_patient_comment[]" class="w-100" value="<?php echo attr($participant['group_patient_comment']);?>" <?php echo $readonly; ?> /></td>
                                  <?php if ($readonly == '') { ?>
                                      <td class="delete_btn">
                                          <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . attr_url($groupId) . '&deleteParticipant=1&pid=' . attr_url($participant['pid']); ?>">&times;</a>
                                      </td>
                                  <?php } ?>
                              </tr>
                          <?php } ?>
                          </tbody>
                      </table>
                  </form>
              </div>
            </div>

            <div id="appointment-component" class="col-md-2 col-sm-12">
                <?php require 'appointmentComponent.php';?>
            </div>
        </div>
    </div>
</main>
<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });

        var table = $('#participants_table').DataTable({
            "columnDefs": [
                { "width": "35%", "targets": 5 }
            ],
            "pageLength":6,
            //order by status doesn't work with js therefore sorting done by php.
            "order": false,
            "searching": false,
            <?php // Bring in the translations ?>
            <?php $translationsDatatablesOverride = array('lengthMenu' => (xla('Display') . ' _MENU_  ' . xla('records per page')),
                                                          'zeroRecords' => (xla('Nothing found - sorry')),
                                                          'info' => (xla('Showing') . ' _START_ ' . xla('to{{range}}') . ' _END_ ' . xla('of') . ' _TOTAL_ ' . xla('participants')),
                                                          'infoEmpty' => (xla('No records available')),
                                                          'infoFiltered' => ('(' . xla('filtered from') . ' _MAX_ ' . xla('total records') . ')'),
                                                          'infoPostFix' => (''),
                                                          'url' => ('')); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });
        var countRows =table.rows().count();
        console.log(countRows);

        // validation on submit -
        // 1. start date not empty
        // 2. end date not smaller than start date
        $('#updateParticipants').on('submit', function(e){

            for(var i = 1; i <= countRows; i++ ){

                if($('#start-date'+i).val() == ''){

                   $('#start-date'+i).addClass('error-border').after('<p class="error-message" id="error-start-date' + i + '" ><?php echo xlt('is not valid');?></p>');
                   $('#start-date'+i).on('focus', function(){
                        $(this).removeClass('error-border');
                        $('#error-start-date' + i).hide();
                   });
                   return  e.preventDefault();
                }
                if(typeof $('#end-date'+i).val() == 'string'){
                    if(moment(DateToYYYYMMDD_js($('#end-date'+i).val())).isBefore(DateToYYYYMMDD_js($('#start-date'+i).val()))){
                        $('#end-date'+i).addClass('error-border').after('<p class="error-message" id="error-end-date' + i + '" ><?php echo xlt('End date must be equal or bigger than start date');?></p>');
                        $('#end-date'+i).on('focus', function(){
                            $(this).removeClass('error-border');
                            $('#error-end-date' + i).hide();
                        });
                        return  e.preventDefault();
                    }
                }
            }
        });

        $('#saveForm').on('click', function () {
            top.restoreSession();
            $('#updateParticipants').append('<input type="hidden" name="save">').submit();
        });

        $('#addParticipant').on('click', function(e){
            e.preventDefault();
           if(!$('#add-participant-form').hasClass('showAddForm')){
               $('#add-participant-form').addClass('showAddForm');
           } else {
               $('#add-participant-form').removeClass('showAddForm');
           }
        });
        $('#cancelAddParticipant').on('click', function(e){
            e.preventDefault();
            $('#add-participant-form').removeClass('showAddForm');
            $('#participant_name').val('');
            $('#group_patient_comment').val('');
            $('#group_patient_start').val('<?php echo date('Y-m-d');?>');
        });

        $('#participant_name').on('click', function(){
            top.restoreSession();
            var url = <?php echo js_escape($GLOBALS['webroot']); ?> + '/interface/main/calendar/find_patient_popup.php';
            dlgopen(url, '_blank', 500, 400);
        });

    });

    function setpatient(pid, lname, fname, dob){
        $('#pid').val(pid);
        $('#participant_name').val(fname + " " + lname);
    }

    function refreshme() {
        top.restoreSession();
        location.href = <?php echo js_escape($GLOBALS['webroot']); ?> + '/interface/therapy_groups/index.php?method=groupParticipants&group_id=' + <?php echo js_url($groupId); ?>;
    }

    function newGroup(){
        top.restoreSession();
        parent.left_nav.loadFrame('gcv4','enc','forms/newGroupEncounter/new.php?autoloaded=1&calenc=');
    }
   // parent.left_nav.setTherapyGroup(<?php echo attr_js($group_id);?>,<?php echo attr_js('test'); ?>);
    /* show the encounters menu in the title menu (code like interface/forms/newGroupEncounter/save.php) */
    <?php
    $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_groups_encounter AS fe " .
        " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.group_id = ? order by fe.date desc", array($groupId));
    ?>

    EncounterDateArray=new Array;
    CalendarCategoryArray=new Array;
    EncounterIdArray=new Array;
    Count=0;
    <?php
    if (sqlNumRows($result4) > 0) {
        while ($rowresult4 = sqlFetchArray($result4)) {
            ?>
        EncounterIdArray[Count]=<?php echo js_escape($rowresult4['encounter']); ?>;
    EncounterDateArray[Count]=<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
    CalendarCategoryArray[Count]=<?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
    Count++;
            <?php
        }
    }
    ?>
    top.window.parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);

</script>
    <?php $use_validate_js = 1;?>
    <?php validateUsingPageRules($_SERVER['PHP_SELF'] . '?method=groupParticipants');?>

<?php } else { ?>
    <div class="container">
        <div class="alert alert-info">
          <h1 class="row"><span class="col-md-3"><i class="fas fa-exclamation-triangle"></i></span><span class="col-md-6"><?php echo xlt("access not allowed");?></span></h1>
        </div>
    </div>
<?php } ?>
<?php require 'footer.php'; ?>
