<?php require 'header.php'; ?>
<main id="group-details">
    <div class="container-group">
        <div class="row">
            <div id="main-component" class="col-md-8 col-sm-12">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <ul class="tabNav">
                            <li><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . $groupId; ?>"><?php echo xlt('General data');?></a></li>
                            <li class="current"><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . $groupId; ?>"><?php echo xlt('Participants ');?></a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <?php if($readonly == ''): ?>
                            <button class="float-right" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . $groupId; ?>'"><?php echo xlt('Cancel');?></button>
                            <button type="submit" form="updateParticipants" name="save" class="float-right"><?php echo xlt('Save');?></button>
                        <?php else: ?>
                            <button class="float-right" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&editParticipants=1&group_id=' . $groupId; ?>'"><?php echo xlt('Update');?></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="component-border">
                            <div  class="row">
                                <form  id="add-participant-form" name="add-participant-form" class="<?php echo isset($addStatus) ? 'showAddForm' : '' ?>" action="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=addParticipant&group_id=' . $groupId?>" method="post">
                                    <input type="hidden" id="pid" name="pid" value="<?php echo !is_null($participant_data) ? $participant_data['pid']: ''?>">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-offset-1 col-md-5">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="bold"><?php echo xlt('Participant’s name'); ?>:</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" id="participant_name" name="participant_name" class="full-width" value="<?php echo !is_null($participant_data) ? $participant_data['participant_name']: ''?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="bold"><?php echo xlt('Date of registration'); ?>:</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="group_patient_start" class="full-width datepicker"  value="<?php echo !is_null($participant_data) ? $participant_data['group_patient_start']: date('Y-m-d');?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-offset-1 col-md-2">
                                                <span class="bold"><?php echo xlt('Comment'); ?>:</span>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" name="group_patient_comment" value="<?php echo !is_null($participant_data) ? $participant_data['group_patient_comment']: ''?>" class="full-width">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-offset-4 col-md-4 text-center">
                                                <input type="submit" name="save" value="<?php echo xl('Adding a participant'); ?>">
                                                <input id="cancelAddParticipant" type="button" value="<?php echo xl('Cancel'); ?>">
                                            </div>
                                        </div>
                                        <?php if(isset($message)): ?>
                                        <div class="row">
                                            <div class="col-md-offset-2 col-md-8">
                                                <p class="<?php echo $addStatus == 'failed' ? 'groups-error-msg' : 'groups-success-msg' ?>"><?php echo $message?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <form id="updateParticipants" method="post">
                                        <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($groupId,ENT_QUOTES); ?>" />
                                        <button id="addParticipant"><?php echo xl('Add'); ?></button>
                                        <table  id="participants_table" class="dataTable display">
                                            <thead>
                                            <tr>
                                                <th><?php echo xl('Participant’s name'); ?></th>
                                                <th><?php echo xl('Patient’s number'); ?></th>
                                                <th><?php echo xl('Status in the group'); ?></th>
                                                <th><?php echo xl('Date of registration'); ?></th>
                                                <th><?php echo xl('Date of exit'); ?></th>
                                                <th><?php echo xl('Comment'); ?></th>
                                                <?php if($readonly == ''): ?>
                                                    <th><?php echo xl('Delete'); ?></th>
                                                <?php endif; ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($participants as $i => $participant) : ?>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="pid[]" value="<?php echo htmlspecialchars($participant['pid'],ENT_QUOTES); ?>" />
                                                        <span><?php echo htmlspecialchars($participant['lname'],ENT_QUOTES) .', ' . htmlspecialchars($participant['fname'],ENT_QUOTES); ?></span>
                                                    </td>
                                                    <td><span><?php echo htmlspecialchars($participant['pid'],ENT_QUOTES); ?></span></td>
                                                    <td>
                                                        <select name="group_patient_status[]" <?php echo $readonly; ?>>
                                                            <?php foreach ($statuses as $key => $status): ?>
                                                                <option value="<?php echo $key;?>"><?php echo xlt($status); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="group_patient_start[]" id="start-date<?php echo $i+1?>" class="datepicker"  value="<?php echo $participant['group_patient_start'];?>" <?php echo $readonly; ?>></td>
                                                    <td><input type="text" name="group_patient_end[]" id="end-date<?php echo $i+1?>" class="datepicker"  value="<?php echo $participant['group_patient_end'];?>" <?php echo $readonly; ?>></td>
                                                    <td><input type="text" name="group_patient_comment[]" class="full-width" class="datepicker"  value="<?php echo $participant['group_patient_comment'];?>" <?php echo $readonly; ?> /></td>
                                                    <?php if($readonly == ''): ?>
                                                        <td class="delete_btn">
                                                            <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id='. $groupId .'&deleteParticipant=1&pid=' . $participant['pid']; ?>"><span>X</span></a>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="appointment-component" class="col-md-4 col-sm-12">
                <?php require 'appointmentComponent.php';?>
            </div>
        </div>
        <div class="row">
            <div id="history-component" class="col-md-12">
                <?php require 'pastMeetingsComponent.php';?>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function(){
        $('.datepicker').datepicker({
            dateFormat: "yy-mm-dd"
        });

        var table = $('#participants_table').DataTable({
            "columnDefs": [
                { "width": "35%", "targets": 5 }
            ],
            "pageLength":6,
            "searching": false
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
                    if(moment($('#end-date'+i).val()).isBefore($('#start-date'+i).val())){
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
        });

        $('#participant_name').on('focus', function(){
            var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_patient_popup.php';
            dlgopen(url, '_blank', 500, 400);
        });

    });

    function setpatient(pid, lname, fname, dob){

        $('#pid').val(pid);
        $('#participant_name').val(fname + " " + lname);
    }
</script>
<?php    $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF'] . '?method=groupParticipants');?>
<script src="<?php echo $GLOBALS['webroot']?>/library/dialog.js"></script>
<?php require 'footer.php'; ?>

