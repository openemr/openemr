<?php require 'header.php'; ?>

<div id="therapy_groups_list_container" class="container">

    <!--------- ERRORS ----------->
    <?php if($deletion_try == 1 && $deletion_response['success'] == 0) :?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-danger text-center">
                    <p class="failed_message"><?php echo $deletion_response['message'];?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!---------- FILTERS SECTION ------------->
    <a id="show_filters" class="btn btn-alert"><?php echo xl("Show Filters")?></a>
    <a id="hide_filters" class="btn btn-alert" style="display: none;"><?php echo xl("Hide Filters")?></a>
    <button id="clear_filters" class="btn"><?php echo xl("Clear Filters")?></button>
    </br></br>
    <div id="filters" style="display: none;">
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_name_filter"><?php echo xl('Group Name');?>:</label>
                <input type="text" class="form-control" id="group_name_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_id_filter"><?php echo xl('Group Id');?>:</label>
                <input type="number" class="form-control" id="group_id_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_type_filter"><?php echo xl('Group Type');?>:</label>
                <select type="text" class="form-control" id="group_type_filter" placeholder="" >
                    <option value=""><?php echo xl('choose');?></option>
                    <?php foreach ($group_types as $type):?>
                        <option value="<?php echo $type;?>"><?php echo xl($type) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_status_filter"><?php echo xl('Status');?>:</label>
                <select type="text" class="form-control" id="group_status_filter" placeholder="" >
                    <option value="<?php echo $statuses[10]; ?>"><?php echo xl($statuses[10]);?></option>
                    <?php foreach ($statuses as $status):?>
                        <?php if($status != $statuses[10]): ?>
                            <option value="<?php echo $status;?>"><?php echo xl($status) ;?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <option value="all"><?php echo xl("all");?></option>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="counselors_filter"><?php echo xl('Main Counselors');?>:</label>
                <select type="text" class="form-control" id="counselors_filter" placeholder="" >
                    <option value=""><?php echo xl('choose');?></option>
                    <?php foreach ($counselors as $counselor):?>
                        <option value="<?php echo $counselor;?>"><?php echo xlt($counselor) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_from_start_date_filter"><?php echo xl('Starting Date From');?>:</label>
                <input type="text" class="form-control" id="group_from_start_date_filter" placeholder="<?php echo xl('from');?>" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_start_date_filter"><?php echo xl('Starting Date To');?>:</label>
                <input type="text" class="form-control" id="group_to_start_date_filter" placeholder="<?php echo xl("to");?>" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_from_end_date_filter"><?php echo xl('End Date From');?>:</label>
                <input type="text" class="form-control" id="group_from_end_date_filter" placeholder="<?php echo xl('from');?>" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_end_date_filter"><?php echo xl('End Date To');?>:</label>
                <input type="text" class="form-control" id="group_to_end_date_filter" placeholder="<?php echo xl("to");?>" >
            </div>

        </div>
    </div>
    <!---------- END OF FILTERS SECTION ------------->

    </br></br>

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
                    <td><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . $group['group_id']; ?>"><?php echo $group['group_name'];?></a></td>
                    <td><?php echo $group['group_id'];?></td>
                    <td><?php echo $group_types[$group['group_type']];?></td>
                    <td><?php echo $statuses[$group['group_status']];?></td>
                    <td><?php echo $group['group_start_date'];?></td>
                    <td><?php echo $group['group_end_date'];?></td>
                    <td>
                        <?php foreach ($group['counselors'] as $counselor){
                            echo xlt($counselor) . " </br> ";
                        } ;?>
                    </td>
                    <td><?php echo $group['group_notes'];?></td>
                    <td class="delete_btn">
                        <?php
                        //Enable deletion only for groups that weren't yet deleted.
                        if($group['group_status'] != 20): ?>
                            <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=listGroups&deleteGroup=1&group_id=' . $group['group_id']; ?>"><button>X</button></a></td>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!---------- END OF TABLE SECTION -------------->

</div>

<script>


    /* ========= Initialise Data Table & Filters ========= */
    $(document).ready(function() {

//        var lang = '<?php //echo $lang ?>//';//get language support for filters

        /* Initialise Datetime Pickers */
        $('#group_from_start_date_filter').datetimepicker();
        $('#group_to_start_date_filter').datetimepicker();

        $('#group_from_end_date_filter').datetimepicker();
        $('#group_to_end_date_filter').datetimepicker();


        /* Initialise Datatable */
        var table = $('#therapy_groups_list').DataTable({
            language: {
//                url: BASE_PATH + JS_BASE_PATH + '/lib/datatables/i18n/' + lang + '.lang'
            },
            initComplete: function () {
                $('#therapy_groups_list_filter').hide(); //hide searchbar
            }
        });

        /* Order by Start Date column (descending) */
        table.order( [ 4, 'desc' ] ).draw();

        /* Hide/Show filters */
        $("#show_filters").click(function () {
            $('#filters').show();
            $("#hide_filters").show();
            $("#show_filters").hide();

        });
        $("#hide_filters").click(function () {
            $('#filters').hide();
            $("#hide_filters").hide();
            $("#show_filters").show();
        });

        /* ------------ Toggle filter functions on keyup/change ----------- */

        /*
        * Note: where there is an explicit extension made for the filter, just table.draw() was used.
        * Otherwise 'table.columns(  ).search( this.value ).draw();' was used.
        */


        /* ---- Datetimepickers ---- */
        $('#group_from_start_date_filter').change( function() {
            table.draw();
        } );
        $('#group_to_start_date_filter').change( function() {
            table.draw();
        } );

        $('#group_from_end_date_filter').change( function() {
            table.draw();
        } );
        $('#group_to_end_date_filter').change( function() {
            table.draw();
        } );

        /* --- Text inputs --- */
        $('#group_name_filter').keyup( function() {
            table.draw();
        } );
        $('#group_id_filter').keyup( function() {
            table.draw();
        } );

        /* ---- Select Boxes ---- */
        $('#group_type_filter').change(function () {
            table.columns( 2 ).search( this.value ).draw();
        } );

        $('#group_status_filter').change( function() {
            table.draw();
        } );

        $('#counselors_filter').change( function() {
            table.columns( 6 ).search( this.value ).draw();
        } );

        /* ----------------- End of filter toggles -------------------- */


        /* --------- Reset Filters ------ */
        $('#clear_filters').click(function(){
            location.reload();
        });
    });

    /* ========= End Of Data Table & Filters Initialisation ========= */

    /* ======= DATATABLE FILTER EXTENSIONS ======== */

    /* Extension for distribution date */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {

            if(document.getElementById('group_from_start_date_filter').value === ""){
                var iFini = document.getElementById('group_from_start_date_filter').value;
            }
            else{
                var iFini = new Date(document.getElementById('group_from_start_date_filter').value);
            }

            if(document.getElementById('group_to_start_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_start_date_filter').value;
            }
            else{
                var iFfin = new Date(document.getElementById('group_to_start_date_filter').value);
            }

            var iStartDateCol = 4;
            var iEndDateCol = 4;
            var datofini = new Date(aData[iStartDateCol]);
            var datoffin = new Date(aData[iEndDateCol]);


            if ( iFini === "" && iFfin === "" )
            {
                return true;
            }
            else if ( iFini <= datofini && iFfin === "")
            {
                return true;
            }
            else if ( iFfin >= datoffin && iFini === "")
            {
                return true;
            }
            else if (iFini <= datofini && iFfin >= datoffin)
            {
                return true;
            }
            return false;
        }
    );

    /* Extension for Irregular approval date */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {

            if(document.getElementById('group_from_end_date_filter').value === ""){
                var iFini = document.getElementById('group_from_end_date_filter').value;
            }
            else{
                var iFini = new Date(document.getElementById('group_from_end_date_filter').value);
            }

            if(document.getElementById('group_to_end_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_end_date_filter').value;
            }
            else{
                var iFfin = new Date(document.getElementById('group_to_end_date_filter').value);
            }

            var iStartDateCol = 5;
            var iEndDateCol = 5;
            var datofini = new Date(aData[iStartDateCol]);
            var datoffin = new Date(aData[iEndDateCol]);


            if ( iFini === "" && iFfin === "" )
            {
                return true;
            }
            else if ( iFini <= datofini && iFfin === "")
            {
                return true;
            }
            else if ( iFfin >= datoffin && iFini === "")
            {
                return true;
            }
            else if (iFini <= datofini && iFfin >= datoffin)
            {
                return true;
            }
            return false;
        }
    );

    /* Extension for group name */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {
            var iColumn = 0;
            var iVal = document.getElementById('group_name_filter').value;
            var iVersion = aData[iColumn] == "-" ? 0 : aData[iColumn];

            if(iVal === "" || iVal == 0){
                return true;
            }
            else if(iVal == iVersion){
                return true;
            }
            return false;
        }
    );

    /* Extension for group id */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {
            var iColumn = 1;
            var iVal = document.getElementById('group_id_filter').value*1 ;
            var iVersion = aData[iColumn] == "-" ? 0 : aData[iColumn]*1;

            if(iVal === "" || iVal == 0){
                return true;
            }
            else if(iVal == iVersion){
                return true;
            }
            return false;
        }
    );

    /* Extension for group status */
    $.fn.dataTableExt.afnFiltering.push(
        function( settings, data, dataIndex ) {
            var status_selected = $("#group_status_filter").val()||'';
            var status = data[3] || '';
            if(status_selected=='' || status_selected == 'all'){
                return true;
            }
            if(status==status_selected)
                return true;
            return false;
        });



    /* ========= END OF EXTENSIONS ============= */


</script>

<?php require  'footer.php'; ?>
