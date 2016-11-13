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
                            <form id="updateParticipants" method="post">
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
                                                <select name="status[]" <?php echo $readonly; ?>>
                                                    <?php foreach ($statuses as $key => $status): ?>
                                                        <option value="<?php echo $key;?>"><?php echo xlt($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="group_patient_start[]" id="start-date<?php echo $i+1?>" class="datepicker"  value="<?php echo $participant['group_patient_start'];?>" <?php echo $readonly; ?>></td>
                                            <td><input type="text" name="group_patient_end[]" id="end-date<?php echo $i+1?>" class="datepicker"  value="<?php echo $participant['group_patient_end'];?>" <?php echo $readonly; ?>></td>
                                            <td><input type="text" name="comment[]" class="full-width"/></td>
                                            <?php if($readonly == ''): ?>
                                            <td class="delete_btn">
                                                <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id='. $groupId .'&deleteParticipant=1&pid=' . $participant['pid']; ?>"><button>X</button></a>
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
            ]
        });
        $('#updateParticipants').on('submit', function(){

            for(var i = 1; i <= table.rows().count(); i++ ){

                if($('#start-date'+i).val() == ''){

                }
            }
        });
    });
</script>

<?php require_once 'library/validation/validation_script.js.php'?>
<?php require 'footer.php'; ?>

