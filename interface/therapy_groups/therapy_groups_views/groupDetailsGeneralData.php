<?php
/**
 * interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php contains group details view .
 *
 * This is the therapy group detail screen for the chosen group.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>


<?php $edit = acl_check("groups", "gadd", false, 'write');?>
<?php $edit_encounter = acl_check("groups", "glog", false, 'write');?>
<?php $view = acl_check("groups", "gadd", false, 'view');?>


<?php require 'header.php'; ?>
<?php if ($view || $edit) :?>

<main id="group-details">
    <div class="container-group">
        <span class="hidden title"><?php echo text($groupData['group_name']);?></span>
        <div class="row">
            <div id="main-component" class="col-md-8 col-sm-12">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <ul class="tabNav">
                            <li  class="current"><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . attr($groupData['group_id']); ?>"><?php echo xlt('General data');?></a></li>
                            <li><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupParticipants&group_id=' . attr($groupData['group_id']); ?>"><?php echo xlt('Participants ');?></a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <?php if ($edit) :?>
                            <?php if ($edit_encounter) :?>
                                <button onclick="newGroup()"><?php echo xlt('Add encounter'); ?></button>
                            <?php endif;?>
                        <?php if ($readonly == '') : ?>
                            <button class="float-right" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . attr($groupData['group_id']); ?>'"><?php echo xlt('Cancel');?></button>
                            <button  id="saveUpdates" class="float-right"><?php echo xlt('Save');?></button>
                        <?php else : ?>
                            <button class="float-right" onclick="location.href='<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&editGroup=1&group_id=' . attr($groupData['group_id']); ?>'"><?php echo xlt('Update');?></button>
                        <?php endif; ?>
                        <?php endif;?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="component-border">
                            <form method="post" id='editGroup' name="editGroup">
                                <input type="hidden" name="group_id" value="<?php echo isset($groupData['group_id']) ? attr($groupData['group_id']) : '';?>">
                                <div class="row group-row">
                                    <div class="col-md-6 col-sm-7">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-5">
                                                <span class="bold"><?php echo xlt("Group's name") ?>:</span>
                                            </div>
                                            <div class="col-md-8 col-sm-7">
                                                <input type="text" name="group_name" class="full-width" value="<?php echo attr($groupData['group_name']);?>" <?php echo $readonly; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-3">
                                        <span class="bold"><?php echo xlt('Group number') ?>:</span>
                                        <span ><?php echo text($groupData['group_id'])?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-6 attach-input">
                                                <span class="bold"><?php echo xlt('Status'); ?>:</span>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <select name="group_status" class="full-width"  value="<?php echo attr($groupData['group_status']);?>" <?php echo $readonly; ?>>
                                                    <?php foreach ($statuses as $key => $status) : ?>
                                                        <option value="<?php echo attr($key);?>" <?php echo $key == $groupData['group_status'] ? 'selected' : ''; ?>><?php echo xlt($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row group-row">
                                    <div class="col-md-6">
                                        <span class="bold"><?php echo xlt('Type of group'); ?>:</span>
                                        <label class="radio-inline radio-pos">
                                            <input type="radio" value="1" name="group_type" <?php echo is_null($groupData['group_type']) || $groupData['group_type'] == '1' ? 'checked' : '';?> <?php echo $readonly; ?>><?php echo xlt('Closed'); ?>
                                        </label>
                                        <label class="radio-inline radio-pos">
                                            <input type="radio" value="2" name="group_type"  <?php echo $groupData['group_type'] == '2' ? 'checked' : '';?> <?php echo $readonly; ?>><?php echo xlt('Open'); ?>
                                        </label>
                                        <label class="radio-inline radio-pos">
                                            <input type="radio" value="3" name="group_type"  <?php echo  $groupData['group_type'] == '3' ? 'checked' : '';?> <?php echo $readonly; ?>><?php echo xlt('Train'); ?>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="bold"><?php echo xlt('Obligatory participation'); ?>:</span>
                                        <label class="radio-inline radio-pos">
                                            <input type="radio" value="1" name="group_participation" <?php echo is_null($groupData['group_participation']) || $groupData['group_participation'] == '1' ? 'checked' : '';?> <?php echo $readonly; ?>><?php echo xlt('Mandatory'); ?>
                                        </label>
                                        <label class="radio-inline radio-pos">
                                            <input type="radio" value="2" name="group_participation" <?php echo $groupData['group_participation'] == '2' ? 'checked' : '';?> <?php echo $readonly; ?>><?php echo xlt('Optional'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="row group-row">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="row">
                                            <div class="col-md-5 col-sm-6">
                                                <span class="bold"><?php echo xlt('Starting date'); ?>:</span>
                                            </div>
                                            <div class="col-md-offset1 col-md-6 col-sm-6">
                                                <input type="text" name="group_start_date" class="full-width datepicker"  value="<?php echo attr(oeFormatShortDate($groupData['group_start_date']));?>" <?php echo $readonly; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="row">
                                            <div class="col-md-5 col-sm-6">
                                                <span class="bold"><?php echo xlt('Ending date'); ?>:</span>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" name="group_end_date" class="full-width datepicker"  value="<?php echo $groupData['group_end_date'] == '0000-00-00' ? '' : attr(oeFormatShortDate($groupData['group_end_date'])) ;?>" <?php echo $readonly; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row group-row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-5">
                                                <span class="bold"><?php echo xlt('Main Counselors'); ?>:</span>
                                            </div>
                                            <div class="col-md-8 col-sm-7">
                                                <select name="counselors[]" multiple class="full-width" <?php echo $readonly; ?>>
                                                    <?php foreach ($users as $user) : ?>
                                                        <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-5">
                                                <span class="bold"><?php echo xlt('Notes'); ?>:</span>
                                            </div>
                                            <div class="col-md-9 col-sm-7">
                                                <textarea name="group_notes" class="full-width" style="height: 70px" <?php echo $readonly; ?>><?php echo text($groupData['group_notes']);?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row group-row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-5">
                                                <span class="bold"><?php echo xlt('Guest counselors'); ?>:</span>
                                            </div>
                                            <div class="col-md-8 col-sm-7">
                                                <input type="text" name="group_guest_counselors" class="full-width"  value="<?php echo attr($groupData['group_guest_counselors']);?>" <?php echo $readonly; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row group-row">
                                    <div class="col-md-9 col-sm 12">
                                        <?php if ($savingStatus == 'exist') : ?>
                                            <div id="exist-group"><h4 class="group-error-msg"><?php echo text($message) ?></h4>   <?php if ($edit) :
?><button id="cancel-save"><?php echo xlt('cancel') ?></button><button type="submit" value="save_anyway" name="save"><?php echo xlt('Creating anyway') ?></button><?php
                                                                                              endif;?></div>
                                        <?php endif ?>
                                        <?php if ($savingStatus == 'success') : ?>
                                            <h4 class="group-success-msg"><?php echo text($message) ?></h4>
                                        <?php endif ?>
                                        <?php if ($savingStatus == 'failed') : ?>
                                            <h4 class="group-serror-msg"><?php echo text($message) ?></h4>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="appointment-component" class="col-md-2 col-sm-12">
                <?php require 'appointmentComponent.php';?>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function(){
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
    $('#cancel-save').on('click', function(e){
        e.preventDefault();
        $('#exist-group').hide();
    });

    $('#saveUpdates').on('click', function () {
        $('#editGroup').append('<input type="hidden" name="save">').submit();
    });

    function refreshme() {
        top.restoreSession();
        location.href = "<?php echo $GLOBALS['webroot'] . '/interface/therapy_groups/index.php?method=groupDetails&group_id='. attr($groupId) ?>";
    }

    function newGroup(){
        <?php if ($GLOBALS['new_tabs_layout']) : ?>
        top.restoreSession();
        parent.left_nav.loadFrame('gcv4','enc','forms/newGroupEncounter/new.php?autoloaded=1&calenc=')
        <?php else : ?>
        top.restoreSession();
        top.frames['RBot'].location = '<?php echo $GLOBALS['web_root'] . "/interface/" ?>' + 'forms/newGroupEncounter/new.php?autoloaded=1&calenc=';
        <?php endif; ?>
    }

    parent.left_nav.setTherapyGroup(<?php echo attr($groupData['group_id'])?>,'<?php echo attr($groupData['group_name'])?>');
    <?php if (!$GLOBALS['new_tabs_layout']) : ?>
    top.restoreSession();
    parent.left_nav.loadFrame('enc2', 'RBot', '/patient_file/history/encounters.php');
    $(parent.Title.document.getElementById('clear_active')).hide();
    <?php endif;?>
    /* show the encounters menu in the title menu (code like interface/forms/newGroupEncounter/save.php) */
    <?php
    $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_groups_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.group_id = ? order by fe.date desc", array( $groupData['group_id']));
    ?>

    EncounterDateArray=new Array;
    CalendarCategoryArray=new Array;
    EncounterIdArray=new Array;
    Count=0;
    <?php
    if (sqlNumRows($result4)>0) {
        while ($rowresult4 = sqlFetchArray($result4)) {
        ?>
        EncounterIdArray[Count]='<?php echo attr($rowresult4['encounter']); ?>';
    EncounterDateArray[Count]='<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>';
    CalendarCategoryArray[Count]='<?php echo attr(xl_appt_category($rowresult4['pc_catname'])); ?>';
    Count++;
    <?php
        }
    }
    ?>
    top.window.parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
</script>
<?php $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF'] . '?method=groupDetails');?>
<?php require 'footer.php'; ?>

<?php else :?>

    <div class="container">

        <div class="row alert alert-info">
            <h1 class="col-md-12"><i class="col-md-3 glyphicon glyphicon-alert"></i><span class="col-md-6"><?php echo xlt("access not allowed");?></span></h1>
        </div>
    </div>



<?php endif;?>
