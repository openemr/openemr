<?php require 'header.php'; ?>
<main id="add-group">
    <div class="container">
        <form>
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
                            <input type="text" name="group_name" class="full-width">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-5">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Starting date'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" name="group_start_date" class="full-width datepicker">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Type of group'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" name="group_type" checked><?php echo xlt('Closed'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" name="group_type"><?php echo xlt('Open'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" name="group_type"><?php echo xlt('Train'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Obligatory participation'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" name="group_participation" checked><?php echo xlt('Mandatory'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" name="group_participation"><?php echo xlt('Optional'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Status'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <select name="group_status" class="full-width">
                                <option><?php echo xlt('Active'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-5">
                            <span class="bold"><?php echo xlt('Main counselors'); ?>:</span>
                        </div>
                        <div class="col-md-7">
                            <select multiple class="full-width">
                                <option>Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-5">

                        </div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>l
</main>
<script>
    $(document).ready(function(){
       $('.datepicker').datepicker({
           dateFormat: "yy-mm-dd"
       });
    });
</script>
<?php require 'footer.php'; ?>

