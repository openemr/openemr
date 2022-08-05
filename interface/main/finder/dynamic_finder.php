<?php

/**
 * dynamic_finder.php
 *
 * Sponsored by David Eschelbacher, MD
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2012-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../globals.php");
require_once "$srcdir/user.inc";
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$uspfx = 'patient_finder.'; //substr(__FILE__, strlen($webserver_root)) . '.';
$patient_finder_exact_search = prevSetting($uspfx, 'patient_finder_exact_search', 'patient_finder_exact_search', ' ');

$popup = empty($_REQUEST['popup']) ? 0 : 1;
$searchAny = empty($_GET['search_any']) ? "" : $_GET['search_any'];
unset($_GET['search_any']);
// Generate some code based on the list of columns.
//
$colcount = 1;
$header_middle = "<td><input type='hidden'></td>";
$header_bottom = "<th></th>";
$coljson = "{ name: 'fname', className: 'dt-control', orderable: false, data: null, searchable: false, defaultContent: '' }";
$orderjson = "";
$res = sqlStatement("SELECT option_id, title, toggle_setting_1, subtype FROM list_options WHERE " .
    "list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title");
$sort_dir_map = generate_list_map('Sort_Direction');
$colwidth = array();
$colwidth[] = "0.5rem";
while ($row = sqlFetchArray($res)) {
    $colname = $row['option_id'];
    $colindex[$colname] = $colcount;
    $colorder = $sort_dir_map[$row['toggle_setting_1']]; // Get the title 'asc' or 'desc' using the value
    $colwidth[] = $row['subtype'];
    $title = xl_list_label($row['title']);
    $title1 = ($title == xl('Full Name')) ? xl('Name') : $title;
    $header_bottom .= "   <th>";
    $header_bottom .= text($title);
    $header_bottom .= "</th>\n";
    $header_middle .= "   <td class='pl-1 pr-3'><input type='text' size='20' ";
    $header_middle .= "value='' class='form-control search_init pl-2' placeholder='" . $title1 . "'/></td>\n";
    if ($coljson) {
        $coljson .= ", ";
    }
    $colname_escaped = addcslashes($colname, "\t\r\n\"\\");
    $coljson .= "{ name: '$colname_escaped', data: '$colname_escaped'";
    //$coljson .= "{ name: '$colname_escaped'";
    //if ($title1 == xl('Name')) {
    //    $coljson .= ", \"mRender\": wrapInLink";
    //}
    $coljson .= "}";
    if ($orderjson) {
        $orderjson .= ", ";
    }
    $orderjson .= "[\"$colcount\", \"" . addcslashes($colorder, "\t\r\n\"\\") . "\"]";
    ++$colcount;
}
$loading = "<div class='spinner-border' role='status'><span class='sr-only'>" . xlt("Loading") . "...</span></div>";
?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs']); ?>
    <title><?php echo xlt("Patient Finder"); ?></title>
<style>
    /* Finder Processing style */
    div.dataTables_wrapper div.dataTables_processing {
        width: auto;
        margin: 0;
        color: var(--danger);
        transform: translateX(-50%);
    }

    div.dataTables_wrapper div#header-buttons {
        padding-top: 0;
        padding-left: 2rem;
        display: inline-block;
    }
    
    div.dataTables_wrapper div#select-search {
        padding-top: 0;
        padding-left: 0;
        display: inline-block;
    }

    div.dataTables_wrapper a#exp_cont_icon {
        padding-top: 0rem;
        padding-left: 1.5rem;
        font-size: 1.5rem !important;
        display: inline-block;
        vertical-align: middle;
        color: var(--primary) !important;
    }

    div.dataTables_wrapper i#show_hide {
        padding-top: 0rem;
        padding-left: 1rem;
        font-size: 1.5rem;
        display: inline-block;
        vertical-align: middle;
        color: var(--primary) !important;
    }

    div.dataTables_wrapper .my_bottom_div {
        padding-left: 0.75rem;
        padding-top: 0.5rem;
    }

    .card {
        border: 0;
        border-radius: 0;
    }

    @media screen and (max-width: 640px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper #custom-search {
            float: inherit;
            text-align: justify;
        }
    }

    table.dataTable thead th,
    table.dataTable thead td {
        border-bottom: 0;
        padding: 0.25rem 0.25rem;
    }

    table.dataTable thead #column-search {
        border-bottom: 0;
        background-color: var(--white) !important;
    }

    table.dataTable thead #column-search td input.search_init {
        height: 1.7rem;
    }

    

    table.dataTable thead tr.header-labels th {
        border-bottom: 0;
        padding: 0.25rem 0.75rem;
    }

    table.dataTable thead tr.header-labels th.dt-control {
        padding-left: 0.1rem;
        padding-right: 0.1rem;
    }

    table.dataTable tfoot th,
    table.dataTable tfoot td {
        border-top: 0;
    }

    table.dataTable tbody tr {
        background-color: var(--white) !important;
        cursor: pointer;
    }

    table.dataTable tbody td {
        padding: 0.1rem 0.75rem;
        border-bottom: 1px solid var(--primary) !important;
    }

    table.dataTable tbody td.dt-control {
        padding: 0.1rem 0.2rem;
        border-bottom: 1px solid var(--primary) !important;
    }

    table.dataTable.row-border tbody th,
    table.dataTable.row-border tbody td,
    table.dataTable.display tbody th,
    table.dataTable.display tbody td {
        border-top: 1px solid var(--gray300) !important;
    }

    table.dataTable.cell-border tbody th,
    table.dataTable.cell-border tbody td {
        border-top: 1px solid var(--gray300) !important;
        border-right: 1px solid var(--gray300) !important;
    }

    table.dataTable.cell-border tbody tr th:first-child,
    table.dataTable.cell-border tbody tr td:first-child {
        border-left: 1px solid var(--gray300) !important;
    }

    table.dataTable.stripe tbody tr.odd,
    table.dataTable.display tbody tr.odd {
        background-color: var(--light) !important;
    }

    table.dataTable tbody tr:hover,
    table.dataTable tbody tr:hover a,
    table.dataTable.display tbody tr:hover,
    table.dataTable.display tbody tr:hover a {
        background-color: var(--secondary) !important;
        text-decoration: none !important;
        color: var(--light);
    }

    table.dataTable tbody a:hover,
    table.dataTable.display tbody a:hover {
        text-decoration: none !important;
        color: var(--light);
    }

    table.dataTable.order-column tbody tr>.sorting_1,
    table.dataTable.order-column tbody tr>.sorting_2,
    table.dataTable.order-column tbody tr>.sorting_3,
    table.dataTable.display tbody tr>.sorting_1,
    table.dataTable.display tbody tr>.sorting_2,
    table.dataTable.display tbody tr>.sorting_3 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.odd>.sorting_1,
    table.dataTable.order-column.stripe tbody tr.odd>.sorting_1 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.odd>.sorting_2,
    table.dataTable.order-column.stripe tbody tr.odd>.sorting_2 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.odd>.sorting_3,
    table.dataTable.order-column.stripe tbody tr.odd>.sorting_3 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.even>.sorting_1,
    table.dataTable.order-column.stripe tbody tr.even>.sorting_1 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.even>.sorting_2,
    table.dataTable.order-column.stripe tbody tr.even>.sorting_2 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr.even>.sorting_3,
    table.dataTable.order-column.stripe tbody tr.even>.sorting_3 {
        background-color: var(--light) !important;
    }

    table.dataTable.display tbody tr:hover>.sorting_1,
    table.dataTable.order-column.hover tbody tr:hover>.sorting_1 {
        background-color: var(--gray300) !important;
    }

    table.dataTable.display tbody tr:hover>.sorting_2,
    table.dataTable.order-column.hover tbody tr:hover>.sorting_2 {
        background-color: var(--gray300) !important;
    }

    table.dataTable.display tbody tr:hover>.sorting_3,
    table.dataTable.order-column.hover tbody tr:hover>.sorting_3 {
        background-color: var(--gray300) !important;
    }

    table.dataTable.display tbody .odd:hover,
    table.dataTable.display tbody .even:hover {
        background-color: var(--gray300) !important;
    }

    table.dataTable.no-footer {
        border-bottom: 0;
    }

    .dataTables_wrapper .dataTables_processing {
        background-color: var(--white) !important;
        background: -webkit-gradient(linear, left top, right top, color-stop(0%, transparent), color-stop(25%, rgba(var(--black), 0.9)), color-stop(75%, rgba(var(--black), 0.9)), color-stop(100%, transparent)) !important;
        background: -webkit-linear-gradient(left, transparent 0%, rgba(var(--black), 0.9) 25%, rgba(var(--black), 0.9) 75%, transparent 100%) !important;
        background: -moz-linear-gradient(left, transparent 0%, rgba(var(--black), 0.9) 25%, rgba(var(--black), 0.9) 75%, transparent 100%) !important;
        background: -ms-linear-gradient(left, transparent 0%, rgba(var(--black), 0.9) 25%, rgba(var(--black), 0.9) 75%, transparent 100%) !important;
        background: -o-linear-gradient(left, transparent 0%, rgba(var(--black), 0.9) 25%, rgba(var(--black), 0.9) 75%, transparent 100%) !important;
        background: linear-gradient(to right, transparent 0%, rgba(var(--black), 0.9) 25%, rgba(var(--black), 0.9) 75%, transparent 100%) !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper #custom-search,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: var(--dark) !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-left: 0.75rem;
        padding-top: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-right: 0;
        margin-bottom: 1rem !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper #custom-search {
        margin-top: 0.2rem;
        padding: 0;
        padding-right: 0.5rem;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper #custom-search label {
        margin-bottom: 0;
    }

    div.dataTables_wrapper div#custom-search input {
        display: inline-block !important;
        float: none;
        width: 12rem !important;
        vertical-align: middle;
        height: 1.7rem;
        font-size: 1rem;
        margin-left: 0.5rem;
    }

    div.dataTables_length select {
        width: 50px !important;
    }

    .dataTables_wrapper.no-footer .dataTables_scrollBody {
        border-bottom: 0;
    }

    /* Pagination button Overrides for jQuery-DT */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
    }

    /* Sort indicator Overrides for jQuery-DT */
    table thead .sorting::before,
    table thead .sorting_asc::before,
    table thead .sorting_asc::after,
    table thead .sorting_desc::before,
    table thead .sorting_desc::after,
    table thead .sorting::after {
        display: none !important;
    }

    .dataTables_wrapper #search {
        float: right;
    }

    .dataTables_wrapper .btn-select-search {
        display: inline-block;
        margin-left: -35px;
        border: 0;
        transition: 0;
        background: transparent;
        padding: 7px 7px 8px 5px;
        outline: none;
    }

    .dataTables_wrapper .btn-select-search:focus {
        outline: none !important;
    }

    .dataTables_wrapper .dropdown-toggle::after {
        display: none !important;
    }

    .dataTables_wrapper #custom-search {
        float: none !important;
    }

    .dataTables_wrapper #pt_table_info {
        padding-left: 0;
    }

    .dt-control {
        padding: 0;
    }

    .dt-control::before {
        background-color: var(--primary) !important;
        height: 0.9em !important;
        width: 0.9em !important;
        font-size: 0.8em !important;
        line-height: 0.9em !important;
        vertical-align:0.2em !important;
        padding: 0;
        box-shadow: 0 0 .3em #444 !important;
    }

    table.dataTable tr.dt-hasChild td.dt-control::before {
        background-color: var(--danger) !important;
    }


    .noHover {
        pointer-events: none;
    }

    .patient_detail_container div.tab{
        padding-left: 2rem;
        margin-bottom: 1rem;
        min-height: 0;

    }

    .patient_detail_container div.tab table td{
        border: 0 !important;
    }

</style>
<script>
    var uspfx = '<?php echo attr($uspfx); ?>';

    $(function () {
        // Initializing the DataTable.
        //
        let serverUrl = "dynamic_finder_ajax.php?csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        let srcAny = <?php echo js_url($searchAny); ?>;
        if (srcAny) {
            serverUrl += "&search_any=" + srcAny;
        }
        var oTable = $('#pt_table').dataTable({
            "processing": true,
            // next 2 lines invoke server side processing
            "serverSide": true,
            // NOTE kept the legacy command 'sAjaxSource' here for now since was unable to get
            // the new 'ajax' command to work.
            "sAjaxSource": serverUrl,
            "fnServerParams": function (aoData) {
                var searchExact = $("#setting-search-exact:checked").length > 0;
                aoData.push({"name": "searchExact", "value": searchExact});
            },
            // dom invokes ColReorderWithResize and allows inclusion of a custom div
            "dom": 'lrtip',
            // These column names come over as $_GET['sColumns'], a comma-separated list of the names.
            // See: http://datatables.net/usage/columns and
            // http://datatables.net/release-datatables/extras/ColReorder/server_side.html
            "columns": [ <?php echo $coljson; ?> ],
            "order": [ <?php echo $orderjson; ?> ],
            //"fixedHeader": true,
            "lengthMenu": [10, 25, 50, 100, 250],
            "pageLength": <?php echo empty($GLOBALS['gbl_pt_list_page_size']) ? '10' : $GLOBALS['gbl_pt_list_page_size']; ?>,
            "initComplete": postDataTableInitialization,
            <?php // Bring in the translations ?>
            <?php $translationsDatatablesOverride = array('search' => (xla('Search all columns') . ':')); ?>
            <?php $translationsDatatablesOverride = array('processing' => $loading); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });

        <?php
        $arrOeUiSettings = array(
            'heading_title' => ' ',
            'include_patient_name' => false,
            'expandable' => true,
            'expandable_files' => array('dynamic_finder_xpd'),//all file names need suffix _xpd
            'action' => "search",//conceal, reveal, search, reset, link or back
            'action_title' => "",//only for action link, leave empty for conceal, reveal, search
            'action_href' => "",//only for actions - reset, link or back
            'show_help_icon' => false,
            'help_file_name' => ""
            );
        $oemr_ui = new OemrUI($arrOeUiSettings);
        $pageHeading = $oemr_ui->pageHeading() . "\r\n";
        $replace = ['<h2>', '</h2>', 'oe-superscript-small '];
        $pageHeading = str_replace($replace, '', $pageHeading);
        $oeContainer = $oemr_ui->oeContainer();
        $pt_table_w_auto = str_contains($oeContainer, "fluid") ? false : true;

        $checked = (!empty($GLOBALS['gbl_pt_list_new_window'])) ? 'checked' : '';
        ?>

        if(!('nextElementSibling' in document.documentElement))
        {
            Object.defineProperty(Element.prototype, 'nextElementSibling',
            {
                get: function()
                {
                    var e = this.nextSibling;
                    while (e && e.nodeType !== 1)
                        e = e.nextSibling;

                    return e;
                }
            });
        }

        header_topNode = document.getElementById("header-top");
        searchNode = document.getElementById("search");
        header_buttonsNode = document.getElementById("header-buttons");
        select_searchNode = document.getElementById("select-search");
        custom_searchNode = document.getElementById("custom-search");
        pt_table_lengthNode = document.getElementById("pt_table_length");

        //searchNode.insertBefore(custom-searchNode, select_searchNode);
        header_topNode.insertBefore(pt_table_lengthNode, header_buttonsNode);
  
        pt_table_infoNode = document.getElementById("pt_table_info");
        pt_table_paginateNode = document.getElementById("pt_table_paginate");
        footer_bottomNode = document.getElementById("footer-bottom");
        footer_bottomNode.append(pt_table_infoNode);
        footer_bottomNode.append(pt_table_paginateNode);        

        colindex = <?php echo json_encode($colindex); ?>

        // This is to support column-specific search fields.
        // Borrowed from the multi_filter.html example.
        $("#column-search input").keyup(function () {
            // Filter on the column (the index) of this element
            oTable.fnFilter(this.value, $("#column-search input").index(this));
        });

        $("div#custom-search input").keyup(function () {
            // Filter on the column (the index) of this element
            //oTable.search(this.value);
            $('#pt_table').DataTable().search(this.value).draw();
        });

        //$('#pt_table').on('mouseenter', 'tbody tr', function() {
        //    $(this).find('a').css('text-decoration', 'underline');
        //});
        //$('#pt_table').on('mouseleave', 'tbody tr', function() {
        //    $(this).find('a').css('text-decoration', '');
        //});


        // OnClick handler for the rows
       
        function showhidePatientData(target) {
            let rowNode = target.parentNode;
            if (rowNode.classList.contains("childShown")) {
                rowNode.nextElementSibling.classList.add("d-none");
                rowNode.classList.remove("childShown");
                rowNode.classList.remove("dt-hasChild");                
            } else {
                rowNode.classList.add("childShown");
                rowNode.classList.add("dt-hasChild");
                if (rowNode.classList.contains("hasChild")) {
                    rowNode.nextElementSibling.classList.remove("d-none");
                } else {
                    let templateNode = document.querySelector(".template_patient_detail");
                    let clonedNode = templateNode.content.cloneNode(true);
                    rowNode.parentNode.insertBefore(clonedNode, rowNode.nextSibling);
                    rowNode.classList.add("hasChild");
                    pid = rowNode.id.substring(4); 

                    patientDetailNode = rowNode.nextElementSibling.querySelector(".patient_detail_container");
                    getPatientDetail(patientDetailNode, pid);
                    
                }
            }
        }

        async function getPatientDetail(patientDetailNode, pid) {
            url = "patient_data_ajax.php?pid=" + pid;
            const response = await fetch(url);
            const PatientDetailHTML = await response.json();
            console.log(PatientDetailHTML);

            patientDetailNode.innerHTML = PatientDetailHTML;

        }


        const tbodyNode = document.querySelector('table#pt_table tbody');
        tbodyNode.addEventListener("click", function (event) {
            if (event.target.classList.contains("dt-control")) {
                event.stopPropagation();
                showhidePatientData(event.target);
            }
        });




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
                top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
            }
        });

    function postDataTableInitialization ( settings, json) {
        // Format DataTable Columns
        pt_tableNode = document.querySelector("table#pt_table");
        pt_tableHeaders = pt_tableNode.querySelectorAll("th");
        var colwidth = <?php echo json_encode($colwidth); ?>;
        pt_tableHeaders.forEach( function(header, index) {
            header.style.width = colwidth[index];
        });
        var pt_table_w_auto = <?php echo json_encode($pt_table_w_auto); ?>;
        if (pt_table_w_auto) {
            pt_tableNode.classList.add('w-auto');
        } else {
            pt_tableNode.classList.remove('w-auto');
        }
        $('#pt_table tfoot td').removeClass('dt-control');
    }

    function wrapInLink(data, type, full) {
        if (type == 'display') {
            return '<a href="" class="text-decoration-none">' + data + "</a>";
        } else {
            return data;
        }
    }

    function openNewTopWindow(pid) {
        document.fnew.patientID.value = pid;
        top.restoreSession();
        document.fnew.submit();
    }

    function persistCriteria(el, e) {
        e.preventDefault();
        let target = uspfx + "patient_finder_exact_search";
        let val = el.checked ? ' checked' : ' ';
        top.restoreSession();
        $.post("../../../library/ajax/user_settings.php",
            {
                target: target,
                setting: val,
                csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
            }
        );
    }
});
</script>

</head>
<body>
    <div id="container_div" class="<?php echo attr($oeContainer); ?> mt-3">
        <div class="w-100">
            <div>
                <div id="dynamic"><!-- TBD: id seems unused, is this div required? -->
                    <!-- Class "display" is defined in demo_table.css -->
                    <div class="table-responsive">
                        <table class="table" class="border-0 display" id="pt_table">
                            <thead class="">
                                <tr>
                                    <td id="header-top" class="border-top-0 pr-1" colspan="<?=$colcount;?>">
                                        <div id="header-buttons">
                                            <?php if (AclMain::aclCheckCore('patients', 'demo', '', array('write','addonly'))) {  ?>
                                                <button id='create_patient_btn1' class='btn btn-primary btn-add'
                                                    onclick='top.restoreSession(); top.RTop.location="<?=$web_root;?>/interface/new/new.php";' 
                                                    style='height: 2rem; line-height: 0;'><?=xlt('Add New Patient');?>
                                                </button>
                                            <?php } ?>
                                            <?php echo $pageHeading ?>
                                        </div>
                                        <div id="search">
                                            <div id="custom-search">
                                                <label>
                                                    Search:
                                                    <input type="search" class="form-control form-control-sm" placeholder="All" aria-controls="pt_table">
                                                </label>
                                            </div>
                                            <div id="select-search">
                                                <button type="button" class="btn-select-search dropdown-toggle dropdown-toggle-split dropdown-toggle-magnify" 
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: none;">
                                                    <i class="fas fa-solid fa-search" style="width:10px; line-height: 1.2; color: rgb(108, 117, 125);"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#" onclick="document.querySelector('#custom-search input').placeholder='Search All';">Search ALL</a>
                                                    <a class="dropdown-item" href="#" onclick="document.querySelector('#custom-search input').placeholder='Search Name';">Search Name</a>
                                                    <a class="dropdown-item" href="#">Search ZIP</a>
                                                    <div role="separator" class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="#">Separated link</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="column-search" class="hideaway"  style="display: none;">
                                    <?php echo $header_middle; ?>
                                </tr>
                                <tr class="header-labels bg-primary text-light">
                                    <?php echo $header_bottom; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="dataTables_empty" colspan="<?php echo attr($colcount); ?>">...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr><td id="footer-top" class="border-top-0 px-1" colspan="<?=$colcount;?>">
                                    <form name='myform'>
                                        <div class='form-check form-check-inline'>
                                            <label id='form_new_window_label' class='form-check-label' for='form_new_window'>
                                                <input type='checkbox' class='form-check-input' id='form_new_window' name='form_new_window' value='1' <?php echo $checked; ?> /><?php echo xlt('Open in New Window'); ?>
                                            </label>
                                        </div>
                                        <div class='form-check form-check-inline'>
                                            <label for='setting-search-exact' id='setting-search-exact_label' class='form-check-label'>
                                                <input type='checkbox' name='setting-search-exact' class='form-check-input' id='setting-search-exact' onchange='persistCriteria(this, event)' value='<?php echo attr($patient_finder_exact_search); ?>'<?php echo text($patient_finder_exact_search); ?>/><?php echo xlt('Search with exact method'); ?>
                                            </label>
                                        </div>
                                    </form>
                                </td></tr>
                                <tr><td id="footer-bottom" class="border-top-0 pt-0 px-1" colspan="<?=$colcount;?>">
                                </td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <!-- form used to open a new top level window when a patient row is clicked -->
        <form name='fnew' method='post' target='_blank' action='../main_screen.php?auth=login&site=<?php echo attr_url($_SESSION['site_id']); ?>'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type='hidden' name='patientID' value='0'/>
        </form>
    </div> <!--End of Container div-->

    <template class="template_patient_detail">
        <tr class='noHover'>
            <td colspan='7'>
                <div class='patient_detail_container'>
                </div>
            </td>
        </tr>
    </template>

    <?php $oemr_ui->oeBelowContainerDiv(); ?>

    <script>
        $(function () {
            $("#exp_cont_icon").click(function () {
                $("#pt_table").removeAttr("style");
            });
        });

        $(window).on("resize", function() { //portrait vs landscape
           $("#pt_table").removeAttr("style");
        });
    </script>

    <script>
        $(function() {
            $("#custom-search").addClass("d-md-initial");
            $("#pt_table_length").addClass("d-md-initial");
            $("#show_hide").addClass("d-md-initial");
            $("#search_hide").addClass("d-md-initial");
            $("#pt_table_length").addClass("d-none");
            $("#show_hide").addClass("d-none");
            $("#search_hide").addClass("d-none");
        });
    </script>

    <script>
        document.addEventListener('touchstart', {});
    </script>

    <script>
        $(function() {
            $('div#custom-search input').focus();
        });
    </script>
</body>
</html>
