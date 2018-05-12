<?php
// Copyright (C) 2012, 2016 Rod Roark <rod@sunsetsystems.com>
// Sponsored by David Eschelbacher, MD
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once "$srcdir/user.inc";

$uspfx = 'patient_finder.'; //substr(__FILE__, strlen($webserver_root)) . '.';
$patient_finder_exact_search = prevSetting($uspfx, 'patient_finder_exact_search', 'patient_finder_exact_search', ' ');

$popup = empty($_REQUEST['popup']) ? 0 : 1;

// Generate some code based on the list of columns.
//
$colcount = 0;
$header0 = "";
$header = "";
$coljson = "";
$res = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title");
while ($row = sqlFetchArray($res)) {
    $colname = $row['option_id'];
    $title = xl_list_label($row['title']);
    $header .= "   <th>";
    $header .= text($title);
    $header .= "</th>\n";
    $header0 .= "   <td align='center'><input type='text' size='10' ";
    $header0 .= "value='' class='search_init' /></td>\n";
    if ($coljson) {
        $coljson .= ", ";
    }
    $coljson .= "{\"sName\": \"" . addcslashes($colname, "\t\r\n\"\\") . "\"}";
    ++$colcount;
}
?>
<html>
<head>
    <?php html_header_show(); ?>
    <title><?php echo xlt("Patient Finder"); ?></title>
    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt-1-10-13/css/jquery.dataTables.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt-1-3-2/css/colReorder.dataTables.min.css" type="text/css">

    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-10-2/index.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-1-10-13/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-1-3-2/js/dataTables.colReorder.min.js"></script>

    <script language="JavaScript">

    var uspfx = '<?php echo attr($uspfx); ?>';

    $(document).ready(function () {

            // Initializing the DataTable.
            //
            var oTable = $('#pt_table').dataTable({
                "processing": true,
                // next 2 lines invoke server side processing
                "serverSide": true,
                // NOTE kept the legacy command 'sAjaxSource' here for now since was unable to get
                // the new 'ajax' command to work.
                "sAjaxSource": "dynamic_finder_ajax.php",
                "fnServerParams": function (aoData) {
                    var searchType = $("#setting_search_type:checked").length > 0;
                    aoData.push({"name": "searchType", "value": searchType});
                },
                // dom invokes ColReorderWithResize and allows inclusion of a custom div
                "dom": 'Rlfrt<"mytopdiv">ip',
                // These column names come over as $_GET['sColumns'], a comma-separated list of the names.
                // See: http://datatables.net/usage/columns and
                // http://datatables.net/release-datatables/extras/ColReorder/server_side.html
                "columns": [ <?php echo $coljson; ?> ],
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": <?php echo empty($GLOBALS['gbl_pt_list_page_size']) ? '10' : $GLOBALS['gbl_pt_list_page_size']; ?>,
                <?php // Bring in the translations ?>
                <?php $translationsDatatablesOverride = array('search' => (xla('Search all columns') . ':')); ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
            });
            $("div.mytopdiv").html("<form name='myform'><label for='form_new_window' id='form_new_window_label'><input type='checkbox' id='form_new_window' name='form_new_window' value='1' <?php if (!empty($GLOBALS['gbl_pt_list_new_window'])) {
                echo ' checked';}?> / ><?php echo xlt('Open in New Window'); ?></label><label for='setting_search_type' id='setting_search_type_label'><input type='checkbox' name='setting_search_type'  id='setting_search_type' onchange='persistCriteria(this, event)' value='<?php echo $patient_finder_exact_search; ?>'<?php echo $patient_finder_exact_search ?>/><?php echo xlt('Search with exact method'); ?></label></form>");
            // This is to support column-specific search fields.
            // Borrowed from the multi_filter.html example.
            $("thead input").keyup(function () {
                // Filter on the column (the index) of this element
                oTable.fnFilter(this.value, $("thead input").index(this));
            });
            // OnClick handler for the rows
            $('#pt_table').on('click', 'tbody tr', function () {
                // ID of a row element is pid_{value}
                var newpid = this.id.substring(4);
                // If the pid is invalid, then don't attempt to set
                // The row display for "No matching records found" has no valid ID, but is
                // otherwise clickable. (Matches this CSS selector).  This prevents an invalid
                // state for the PID to be set.
                if (newpid.length === 0) {
                    return;
                }
                if (document.myform.form_new_window.checked) {
                    openNewTopWindow(newpid);
                }
                else {
                    top.restoreSession();
                    top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + newpid;
                }
            });
        });

        function openNewTopWindow(pid) {
            document.fnew.patientID.value = pid;
            top.restoreSession();
            document.fnew.submit();
        }

        function persistCriteria(el, e){
            e.preventDefault();
            let target = uspfx + "patient_finder_exact_search";
            let val = el.checked ? ' checked' : ' ';
            $.post( "../../../library/ajax/user_settings.php", { target: target, setting: val });
        }

    </script>

</head>
<body class="body_top">

<div id="dynamic"><!-- TBD: id seems unused, is this div required? -->

    <!-- Class "display" is defined in demo_table.css -->
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="pt_table">
        <thead>
        <tr>
            <?php echo $header0; ?>
        </tr>
        <tr class="head">
            <?php echo $header; ?>
        </tr>
        </thead>
        <tbody>
        <tr>
            <!-- Class "dataTables_empty" is defined in jquery.dataTables.css -->
            <td colspan="<?php echo $colcount; ?>" class="dataTables_empty">...</td>
        </tr>
        </tbody>
    </table>

</div>

<!-- form used to open a new top level window when a patient row is clicked -->
<form name='fnew' method='post' target='_blank' action='../main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>'>
    <input type='hidden' name='patientID' value='0'/>
</form>

</body>
</html>
