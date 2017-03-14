<?php
/**
 * interface/therapy_groups/therapy_groups_views/listGroups.php contains the group list view .
 *
 * In this view all therapy groups are listed with their details and links to their details screen.
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
<?php require 'header.php'; ?>
<span class="hidden title"><?php echo xlt('Therapy Group Finder');?></span>
<div id="therapy_groups_list_container" class="container">

    <!--------- ERRORS ----------->
    <?php if($deletion_try == 1 && $deletion_response['success'] == 0) :?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-danger text-center">
                    <p class="failed_message"><?php echo xlt($deletion_response['message']);?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!---------- FILTERS SECTION ------------->
    <button id="clear_filters" class="btn"><?php echo xlt("Clear Filters")?></button>
    </br></br></br>
    <div id="filters">
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_name_filter"><?php echo xlt('Group Name');?>:</label>
                <input type="text" class="form-control" id="group_name_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_id_filter"><?php echo xlt('Group Id');?>:</label>
                <input type="number" class="form-control" id="group_id_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_type_filter"><?php echo xlt('Group Type');?>:</label>
                <select type="text" class="form-control" id="group_type_filter" placeholder="" >
                    <option value=""><?php echo xlt('choose');?></option>
                    <?php foreach ($group_types as $type):?>
                        <option value="<?php echo attr($type);?>"><?php echo text($type) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_status_filter"><?php echo xlt('Status');?>:</label>
                <select type="text" class="form-control" id="group_status_filter" placeholder="" >
                    <option value="<?php echo attr($statuses[10]); ?>"><?php echo xlt($statuses[10]);?></option>
                    <?php foreach ($statuses as $status):?>
                        <?php if($status != $statuses[10]): ?>
                            <option value="<?php echo attr($status);?>"><?php echo xlt($status) ;?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <option value="all"><?php echo xlt("all");?></option>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="counselors_filter"><?php echo xlt('Main Counselors');?>:</label>
                <select type="text" class="form-control" id="counselors_filter" placeholder="" >
                    <option value=""><?php echo xlt('choose');?></option>
                    <?php foreach ($counselors as $counselor):?>
                        <option value="<?php echo attr($counselor);?>"><?php echo text($counselor) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_from_start_date_filter"><?php echo xlt('Starting Date From');?>:</label>
                <input type="text" class="form-control datetimepicker" id="group_from_start_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_start_date_filter"><?php echo xlt('Starting Date To');?>:</label>
                <input type="text" class="form-control datetimepicker" id="group_to_start_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_from_end_date_filter"><?php echo xlt('End Date From');?>:</label>
                <input type="text" class="form-control datetimepicker" id="group_from_end_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_end_date_filter"><?php echo xlt('End Date To');?>:</label>
                <input type="text" class="form-control datetimepicker" id="group_to_end_date_filter" placeholder="" >
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
                <th><?php echo xlt('Group Name'); ?></th>
                <th><?php echo xlt('Group Id'); ?></th>
                <th><?php echo xlt('Group Type'); ?></th>
                <th><?php echo xlt('Status'); ?></th>
                <th><?php echo xlt('Start Date'); ?></th>
                <th><?php echo xlt('End Date'); ?></th>
                <th><?php echo xlt('Main Counselors'); ?></th>
                <th><?php echo xlt('Comment'); ?></th>
                <th><?php echo xlt('Delete'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($therapyGroups as $group) : ?>
                <tr>
                    <?php
                    if($_SESSION['language_direction'] == 'rtl'){
                        $date_start_date = date('d/m/Y',strtotime(text($group['group_start_date'])));
                        $date_end_date = date('d/m/Y',strtotime(text($group['group_end_date'])));
                    }else{
                        $date_start_date = text($group['group_start_date']);
                        $date_end_date = text($group['group_end_date']);
                    }


                    ?>

                    <td><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . attr($group['group_id']); ?>"><?php echo text($group['group_name']);?></a></td>
                    <td><?php echo text($group['group_id']);?></td>
                    <td><?php echo xlt($group_types[$group['group_type']]);?></td>
                    <td><?php echo xlt($statuses[$group['group_status']]);?></td>
                    <td><?php echo $date_start_date;?></td>
                    <td><?php echo ($group['group_end_date'] == '0000-00-00' OR $group['group_end_date'] == '00-00-0000' OR empty($group['group_end_date'])) ? '' : $date_end_date; ?></td>
                    <td>
                        <?php foreach ($group['counselors'] as $counselor){
                            echo text($counselor) . " </br> ";
                        } ;?>
                    </td>
                    <td><?php echo text($group['group_notes']);?></td>
                    <td class="delete_btn">
                        <?php
                        //Enable deletion only for groups that weren't yet deleted.
                        if($group['group_status'] != 20): ?>
                        <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=listGroups&deleteGroup=1&group_id=' . attr($group['group_id']); ?>"><button>X</button></a></td>
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

        /* Initialise Datetime Picker */
        $('.datetimepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = (($_SESSION['language_direction'] == 'rtl') ? true : false); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });

        /* Initialise Datatable */
        var table = $('#therapy_groups_list').DataTable({
            language: {
                "lengthMenu": '<?php echo xlt("Display")  .' _MENU_  ' .xlt("records per page")?>',
                "zeroRecords": '<?php echo xlt("Nothing found - sorry")?>',
                "info": '<?php echo xlt("Showing page") .' _PAGE_ '. xlt("of") . ' _PAGES_'; ?>',
                "infoEmpty": '<?php echo xlt("No records available") ?>',
                "infoFiltered": '<?php echo "(" . xlt("filtered from") . ' _MAX_ '. xlt("total records") . ")"; ?>',
                "infoPostFix":  "",
                "search":       "<?php echo xlt('Search')?>",
                "url":          "",
                "oPaginate": {
                    "sFirst":    "<?php echo xlt('First')?>",
                    "sPrevious": "<?php echo xlt('Previous')?>",
                    "sNext":     "<?php echo xlt('Next')?>",
                    "sLast":     "<?php echo xlt('Last')?>"
                }
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
            top.restoreSession();
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
                var iFini_StartDateCol = document.getElementById('group_from_start_date_filter').value;
                iFini_StartDateCol = iFini_StartDateCol.replace(/\//g,'-');

                <?php
                if($_SESSION['language_direction'] == 'rtl') {
                    echo "var parts = iFini_StartDateCol.split('-');\n";
                    echo "iFini_StartDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";
                }
                ?>

                iFini = new Date(iFini_StartDateCol);
            }

            if(document.getElementById('group_to_start_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_start_date_filter').value;
            }
            else{
                var iFfin_StartDateCol = document.getElementById('group_from_start_date_filter').value;
                iFfin_StartDateCol = iFfin_StartDateCol.replace(/\//g,'-');

                <?php
                if($_SESSION['language_direction'] == 'rtl') {
                    echo "var parts = iFfin_StartDateCol.split('-');\n";
                    echo "iFfin_StartDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";
                }
                ?>

                iFfin = new Date(iFfin_StartDateCol);
            }

            var iStartDateCol = 4;
            var iEndDateCol = 4;

            iStartDateCol = aData[iStartDateCol].replace(/\//g,'-');
            iEndDateCol = aData[iEndDateCol].replace(/\//g,'-');

            <?php
            if($_SESSION['language_direction'] == 'rtl'){
                echo "var parts = iStartDateCol.split('-');\n";
                echo "iStartDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";

                echo "var parts = iEndDateCol.split('-');\n";
                echo "iEndDateCol = parts[2]  + '-' + parts[1] + '-' + parts[0];\n";
            }
            ?>

            var datofini = new Date(iStartDateCol);
            var datoffin = new Date(iEndDateCol);


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
                var iFini_EndDateCol = document.getElementById('group_from_end_date_filter').value;
                iFini_EndDateCol = iFini_EndDateCol.replace(/\//g,'-');

                <?php
                if($_SESSION['language_direction'] == 'rtl') {
                    echo "var parts = iFini_EndDateCol.split('-');\n";
                    echo "iFini_EndDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";
                }
                ?>

                iFini = new Date(iFini_EndDateCol);
            }

            if(document.getElementById('group_to_end_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_end_date_filter').value;
            }
            else{
                var iFfin_EndDateCol = document.getElementById('group_from_end_date_filter').value;
                iFfin_EndDateCol = iFfin_EndDateCol.replace(/\//g,'-');

                <?php
                if($_SESSION['language_direction'] == 'rtl') {
                    echo "var parts = iFfin_EndDateCol.split('-');\n";
                    echo "iFfin_EndDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";
                }
                ?>

                iFfin = new Date(iFfin_EndDateCol);
            }

            var iStartDateCol = 5;
            var iEndDateCol = 5;

            iStartDateCol = aData[iStartDateCol].replace(/\//g,'-');
            iEndDateCol = aData[iEndDateCol].replace(/\//g,'-');

            <?php
            if($_SESSION['language_direction'] == 'rtl'){
                echo "var parts = iStartDateCol.split('-');\n";
                echo "iStartDateCol = parts[2] + '-' + parts[1]  + '-' + parts[0];\n";

                echo "var parts = iEndDateCol.split('-');\n";
                echo "iEndDateCol = parts[2]  + '-' + parts[1] + '-' + parts[0];\n";
            }
            ?>

            var datofini = new Date(iStartDateCol);
            var datoffin = new Date(iEndDateCol);


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
            else if(iVersion.indexOf(iVal) != -1){
                return true;
            }
            return false;
        }
    );

    /* Extension for group id */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {
            var iColumn = 1;
            var iVal = document.getElementById('group_id_filter').value;
            var iVersion = aData[iColumn] == "-" ? 0 : aData[iColumn];

            if(iVal === "" || iVal == 0){
                return true;
            }
            else if(iVersion.indexOf(iVal) != -1){
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