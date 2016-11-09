<?php require 'header.php'; ?>

<div id="medicine_history_container" class="container">
    <h2><?php echo xl("Therapy Groups List"); ?></h2>

    <!---------- FILTERS SECTION ------------->
    <a id="show_filters" class="btn btn-alert"><?php echo xl("Show Filters")?></a>
    <a id="hide_filters" class="btn btn-alert" style="display: none;"><?php echo xl("Hide Filters")?></a>
    <button id="clear_filters" class="btn"><?php echo xl("Clear Filters")?></button>
    <div id="filters" style="display: none;">
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_name_filter"><?php echo xl('Group Name');?>:</label>
                <input type="text" class="form-control" id="group_name_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-1">
                <label class="" for="group_id_filter"><?php echo xl('Group Id');?>:</label>
                <input type="number" class="form-control" id="group_id_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-1">
                <label class="" for="group_type_filter"><?php echo xl('Group type');?>:</label>
                <select type="text" class="form-control" id="group_type_filter" placeholder="" >
                    <option value=""><?php echo xl('choose');?></option>
                    <?php foreach ($group_types as $key => $type):?>
                        <option value="<?php echo $key;?>"><?php echo $type ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class=" form-group col-md-1">
                <label class="" for="group_status_filter"><?php echo xl('Status');?>:</label>
                <select type="text" class="form-control" id="group_status_filter" placeholder="" >
                    <option value=""><?php echo xl('choose');?></option>
                    <?php foreach ($statuses as $key => $status):?>
                        <option value="<?php echo $key;?>"><?php echo $status ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_from_start_date_filter"><?php echo xl('Starting Date');?>:</label>
                <input type="text" class="form-control" id="group_from_start_date_filter" placeholder="<?php echo xl('from');?>" >
                <input type="text" class="form-control" id="group_to_start_date_filter" placeholder="<?php echo xl("to");?>" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_from_end_date_filter"><?php echo xl('End Date');?>:</label>
                <input type="text" class="form-control" id="group_from_end_date_filter" placeholder="<?php echo xl('from');?>" >
                <input type="text" class="form-control" id="group_to_end_date_filter" placeholder="<?php echo xl("to");?>" >
            </div>

        </div>
    </div>
    <!---------- END OF FILTERS SECTION ------------->

    <!---------- TABLE SECTION -------------->
    <div class="row">
        <table  id="therapy_groups_list" class="dataTable display">
            <thead>
            <tr>
                <th><?php echo xl('Group Name'); ?></th>
                <th><?php echo xl('Group Id'); ?></th>
                <th><?php echo xl('Group Type'); ?></th>
                <th><?php echo xl('Status'); ?></th>
                <th><?php echo xl('Start Date'); ?></th>
                <th><?php echo xl('End Date'); ?></th>
                <th><?php echo xl('Main Counselors'); ?></th>
                <th><?php echo xl('Comment'); ?></th>
                <th><?php echo xl('Delete'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($therapyGroups as $group) : ?>
                <tr>
                    <td><?php echo $group['group_name'];?></td>
                    <td><?php echo $group['group_id'];?></td>
                    <td><?php echo $group_types[$group['group_type']];?></td>
                    <td><?php echo $statuses[$group['group_status']];?></td>
                    <td><?php echo $group['group_start_date'];?></td>
                    <td><?php echo $group['group_end_date'];?></td>
                    <td>
                        <?php foreach ($group['counselors'] as $counselor){
                            echo $counselor . "   ";
                        } ;?>
                    </td>
                    <td><?php echo $group['group_notes'];?></td>
                    <td>Delete</td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!---------- END OF TABLE SECTION -------------->

</div>

<?php require  'footer.php'; ?>
