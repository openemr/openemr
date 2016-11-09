<?php require 'header.php'; ?>
<main id="add-group">
    <div class="container container-group">
        <form method="post" name="addGroup">
            <input type="hidden" value="<?php echo isset($groupData['group_id']) ? $groupData['group_id'] : '';?>">
            <div class="row group-row">
                <div class="col-md-10">
                    <span class="title"><?php echo xlt('Add group') ?> </span>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-7 col-sm-6">
                    <div class="row">
                        <div class="col-md-3 col-sm-5">
                            <span class="bold"><?php echo xlt('Group’s name') ?>:</span>
                        </div>
                        <div class="col-md-9 col-sm-7">
                            <input type="text" name="group_name" class="full-width" value="<?php echo $groupData['group_name'];?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-5">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Starting date'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" name="group_start_date" class="full-width datepicker"  value="<?php echo $groupData['group_start_date'];?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Type of group'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="1" name="group_type" <?php echo is_null($groupData['group_type']) || $groupData['group_type'] == '1' ? 'checked' : '';?>><?php echo xlt('Closed'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="2" name="group_type"  <?php echo $groupData['group_type'] == '2' ? 'checked' : '';?>><?php echo xlt('Open'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="3" name="group_type"  <?php echo  $groupData['group_type'] == '3' ? 'checked' : '';?>><?php echo xlt('Train'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Obligatory participation'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="1" name="group_participation" <?php echo is_null($groupData['group_participation']) || $groupData['group_participation'] == '1' ? 'checked' : '';?>><?php echo xlt('Mandatory'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="2" name="group_participation" <?php echo $groupData['group_participation'] == '2' ? 'checked' : '';?>><?php echo xlt('Optional'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Status'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <select name="group_status" class="full-width"  value="<?php echo $groupData['group_status'];?>">
                                <?php foreach($statuses as $key => $status): ?>
                                <option value="<?php echo $key;?>"><?php echo xlt($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 col-sm-5">
                            <span class="bold"><?php echo xlt('Main counselors'); ?>:</span>
                        </div>
                        <div class="col-md-8 col-sm-7">
                            <select name="counselors[]" multiple class="full-width">
                                <?php foreach($users as $user): ?>
                                <option value="<?php echo $user['id'];?>"><?php echo $user['fname'] . ' ' . $user['lname'];?></option>
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
                            <textarea name="group_notes" class="full-width" style="height: 70px"><?php echo $groupData['group_notes'];?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 col-sm-5">
                            <span class="bold"><?php echo xlt('Guest counselors'); ?>:</span>
                        </div>
                        <div class="col-md-8 col-sm-7">
                           <input type="text" name="group_guest_counselors" class="full-width"  value="<?php echo $groupData['group_guest_counselors'];?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-3">
                    <button type="submit" name="save" value="save" <?php echo $savingStatus == 'success' ? 'disabled' : '';?>><?php echo xlt('Add group');?></button>
                </div>
                <div class="col-md-9 col-sm 12">

                    <?php if($savingStatus == 'exist'): ?>
                        <div id="exist-group"><h4 class="group-error-msg"><?php echo $message ?></h4><button id="cancel-save"><?php echo xlt('cancel') ?></button><button type="submit" value="save_anyway" name="save"><?php echo xlt('Creating anyway') ?></button></div>
                    <?php endif ?>
                    <?php if($savingStatus == 'success'): ?>
                        <h4 class="group-success-msg"><?php echo $message ?></h4>
                    <?php endif ?>
                    <?php if($savingStatus == 'failed'): ?>
                        <h4 class="group-serror-msg"><?php echo $message ?></h4>
                    <?php endif ?>
                </div>
            <div>
        </form>
    </div>
</main>
<script>
    $(document).ready(function(){
       $('.datepicker').datepicker({
           dateFormat: "yy-mm-dd"
       });
    });
    $('#cancel-save').on('click', function(e){
        e.preventDefault();
        $('#exist-group').hide();
    });
</script>
<?php    $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF'] . '?method=addGroup');?>
<?php require 'footer.php'; ?>

