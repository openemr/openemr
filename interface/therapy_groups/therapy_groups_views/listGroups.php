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
<?php $edit = acl_check("groups", "gadd", false, 'write');?>
<?php $view = acl_check("groups", "gadd", false, 'view');?>


<?php require 'header.php'; ?>
<?php if ($view || $edit) :?>

<span class="hidden title"><?php echo xlt('Therapy Group Finder');?></span>
<div id="therapy_groups_list_container" class="container">

    <!--------- ERRORS ----------->
    <?php if ($deletion_try == 1 && $deletion_response['success'] == 0) :?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-danger text-center">
                    <p class="failed_message"><?php echo xlt($deletion_response['message']);?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!---------- FILTERS SECTION ------------->
    <?php if ($edit) :?>
    <button id="clear_filters" class="btn"><?php echo xlt("Clear Filters")?></button>
    <?php endif;?>

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
                    <?php foreach ($group_types as $type) :?>
                        <option value="<?php echo attr($type);?>"><?php echo text($type) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_status_filter"><?php echo xlt('Status');?>:</label>
                <select type="text" class="form-control" id="group_status_filter" placeholder="" >
                    <option value="<?php echo attr($statuses[10]); ?>"><?php echo xlt($statuses[10]);?></option>
                    <?php foreach ($statuses as $status) :?>
                        <?php if ($status != $statuses[10]) : ?>
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
                    <?php foreach ($counselors as $counselor) :?>
                        <option value="<?php echo attr($counselor);?>"><?php echo text($counselor) ;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class=" form-group col-md-2">
                <label class="" for="group_from_start_date_filter"><?php echo xlt('Starting Date From');?>:</label>
                <input type="text" class="form-control datepicker" id="group_from_start_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_start_date_filter"><?php echo xlt('Starting Date To');?>:</label>
                <input type="text" class="form-control datepicker" id="group_to_start_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_from_end_date_filter"><?php echo xlt('End Date From');?>:</label>
                <input type="text" class="form-control datepicker" id="group_from_end_date_filter" placeholder="" >
            </div>
            <div class=" form-group col-md-2">
                <label class="" for="group_to_end_date_filter"><?php echo xlt('End Date To');?>:</label>
                <input type="text" class="form-control datepicker" id="group_to_end_date_filter" placeholder="" >
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
                    <td><a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=groupDetails&group_id=' . attr($group['group_id']); ?>"><?php echo text($group['group_name']);?></a></td>
                    <td><?php echo text($group['group_id']);?></td>
                    <td><?php echo xlt($group_types[$group['group_type']]);?></td>
                    <td><?php echo xlt($statuses[$group['group_status']]);?></td>
                    <td><?php echo text(oeFormatShortDate($group['group_start_date']));?></td>
                    <td><?php echo ($group['group_end_date'] == '0000-00-00' or $group['group_end_date'] == '00-00-0000' or empty($group['group_end_date'])) ? '' : text(oeFormatShortDate($group['group_end_date'])); ?></td>
                    <td>
                        <?php foreach ($group['counselors'] as $counselor) {
                            echo text($counselor) . " </br> ";
} ;?>
                    </td>
                    <td><?php echo text($group['group_notes']);?></td>
                    <td class="delete_btn">
                        <?php
                        //Enable deletion only for groups that weren't yet deleted.
                        if ($group['group_status'] == 10) { ?>
                            <a href="<?php echo $GLOBALS['rootdir'] . '/therapy_groups/index.php?method=listGroups&deleteGroup=1&group_id=' . attr($group['group_id']); ?>"><?php
                            if ($edit) { ?>
                                <button>X</button><?php
                            } ?>
                            </a></td><?php
                        } ?>

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

        /* Initialise Date Picker */
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });

        /* Initialise Datatable */
        var table = $('#therapy_groups_list').DataTable({
            initComplete: function () {
                $('#therapy_groups_list_filter').hide(); //hide searchbar
            },
            ordering: false,
            <?php // Bring in the translations ?>
            <?php $translationsDatatablesOverride = array('lengthMenu'=>(xla('Display').' _MENU_  '.xla('records per page')),
                                                          'zeroRecords'=>(xla('Nothing found - sorry')),
                                                          'info'=>(xla('Showing page') .' _PAGE_ '. xla('of') . ' _PAGES_'),
                                                          'infoEmpty'=>(xla('No records available')),
                                                          'infoFiltered'=>('('.xla('filtered from').' _MAX_ '.xla('total records').')'),
                                                          'infoPostFix'=>(''),
                                                          'url'=>('')); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });

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

    /* Bring in the DateToYYYYMMDD_js function */
    <?php require($GLOBALS['srcdir'] . '/formatting_DateToYYYYMMDD_js.js.php'); ?>

    /* ========= End Of Data Table & Filters Initialisation ========= */

    /* ======= DATATABLE FILTER EXTENSIONS ======== */

    /* Extension for distribution date */
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {

            if(document.getElementById('group_from_start_date_filter').value === ""){
                var iFini = document.getElementById('group_from_start_date_filter').value;
            }
            else{
                var iFini = new Date(DateToYYYYMMDD_js(document.getElementById('group_from_start_date_filter').value));
            }

            if(document.getElementById('group_to_start_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_start_date_filter').value;
            }
            else{
                var iFfin = new Date(DateToYYYYMMDD_js(document.getElementById('group_to_start_date_filter').value));
            }

            var iStartDateCol = 4;
            var iEndDateCol = 4;

            var datofini = new Date(DateToYYYYMMDD_js(aData[iStartDateCol]));
            var datoffin = new Date(DateToYYYYMMDD_js(aData[iEndDateCol]));


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
                var iFini = new Date(DateToYYYYMMDD_js(document.getElementById('group_from_end_date_filter').value));
            }

            if(document.getElementById('group_to_end_date_filter').value === ""){
                var iFfin = document.getElementById('group_to_end_date_filter').value;
            }
            else{
                var iFfin = new Date(DateToYYYYMMDD_js(document.getElementById('group_to_end_date_filter').value));
            }

            var iStartDateCol = 5;
            var iEndDateCol = 5;



            var datofini = new Date(DateToYYYYMMDD_js(aData[iStartDateCol]));
            var datoffin = new Date(DateToYYYYMMDD_js(aData[iEndDateCol]));


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
<?php else :?>

    <div class="container">

        <div class="row alert alert-info">
            <h1 class="col-md-12"><i class="col-md-3 glyphicon glyphicon-alert"></i><span class="col-md-6"><?php echo xlt("access not allowed");?></span></h1>
        </div>
    </div>



<?php endif;?>
