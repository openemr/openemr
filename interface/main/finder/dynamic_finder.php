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
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../globals.php");
require_once "$srcdir/user.inc.php";
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\UserInterface\PageHeadingRenderEvent;
use OpenEMR\Menu\BaseMenuItem;
use OpenEMR\OeUI\OemrUI;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OpenEMR\Services\PatientService;

$uspfx = 'patient_finder.'; //substr(__FILE__, strlen($webserver_root)) . '.';
$patient_finder_exact_search = prevSetting($uspfx, 'patient_finder_exact_search', 'patient_finder_exact_search', ' ');

$popup = empty($_REQUEST['popup']) ? 0 : 1;
$searchAny = empty($_GET['search_any']) ? "" : $_GET['search_any'];
unset($_GET['search_any']);
// Generate some code based on the list of columns.
//
$colcount = 0;
$header0 = "";
$header = "";
$coljson = "";
$orderjson = "";
$res = sqlStatement("SELECT option_id, title, toggle_setting_1 FROM list_options WHERE list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title");
$sort_dir_map = generate_list_map('Sort_Direction');
while ($row = sqlFetchArray($res)) {
    $colname = $row['option_id'];
    $colorder = $sort_dir_map[$row['toggle_setting_1']]; // Get the title 'asc' or 'desc' using the value
    $title = xl_list_label($row['title']);
    $title1 = ($title == xl('Full Name')) ? xl('Name') : $title;
    $header .= "   <th>";
    $header .= text($title);
    $header .= "</th>\n";
    $header0 .= "   <td ><input type='text' size='20' ";
    $header0 .= "value='' class='form-control search_init' placeholder='" . xla("Search by") . " " . $title1 . "'/></td>\n";
    if ($coljson) {
        $coljson .= ", ";
    }

    $coljson .= "{\"sName\": \"" . addcslashes($colname, "\t\r\n\"\\") . "\"";
    if ($title1 == xl('Name')) {
        $coljson .= ", \"mRender\": wrapInLink";
    }
    $coljson .= "}";
    if ($orderjson) {
        $orderjson .= ", ";
    }
    $orderjson .= "[\"$colcount\", \"" . addcslashes($colorder, "\t\r\n\"\\") . "\"]";
    ++$colcount;
}
$loading = "";
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
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper #custom-search {
            float: inherit;
            text-align: justify;
        }
        /* remove later .dataTables_wrapper .dataTables_filter */
    }

    /* Color Overrides for jQuery-DT */
    table.dataTable thead th,
    table.dataTable thead td {
        /*border-bottom: 1px solid var(--gray900) !important;*/
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
        /*border-top: 1px solid var(--gray900) !important;*/
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
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper #custom-search,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: var(--dark) !important;
    }
    /* remove later  .dataTables_wrapper .dataTables_filter,*/

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
        let serverUrl = "dynamic_finder_ajax.php";
        let srcAny = <?php echo js_url($searchAny); ?>;
        if (srcAny) {
            serverUrl += "?search_any=" + srcAny;
        }
        var oTable = $('#pt_table').dataTable({
            "processing": true,
            // next 2 lines invoke server side processing
            "serverSide": true,
            // NOTE kept the legacy command 'sAjaxSource' here for now since was unable to get
            // the new 'ajax' command to work.
            "sAjaxSource": serverUrl,
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
            "order": [ <?php echo $orderjson; ?> ],
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": <?php echo empty($GLOBALS['gbl_pt_list_page_size']) ? '10' : $GLOBALS['gbl_pt_list_page_size']; ?>,
            <?php // Bring in the translations ?>
            <?php $translationsDatatablesOverride = array('search' => (xla('Search all columns') . ':')); ?>
            <?php $translationsDatatablesOverride = array('processing' => $loading); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });


        <?php
        $checked = (!empty($GLOBALS['gbl_pt_list_new_window'])) ? 'checked' : '';
        ?>
        $("div.mytopdiv").html("<form name='myform'><div class='form-check form-check-inline'><label for='form_new_window' class='form-check-label' id='form_new_window_label'><input type='checkbox' class='form-check-input' id='form_new_window' name='form_new_window' value='1' <?php echo $checked; ?> /><?php echo xlt('Open in New Browser Tab'); ?></label></div><div class='form-check form-check-inline'><label for='setting_search_type' id='setting_search_type_label' class='form-check-label'><input type='checkbox' name='setting_search_type' class='form-check-input' id='setting_search_type' onchange='persistCriteria(this, event)' value='<?php echo attr($patient_finder_exact_search); ?>'<?php echo text($patient_finder_exact_search); ?>/><?php echo xlt('Search with exact method'); ?></label></div></form>");

        // This is to support column-specific search fields.
        // Borrowed from the multi_filter.html example.
        $("thead input").keyup(function () {
            // Filter on the column (the index) of this element
            oTable.fnFilter(this.value, $("thead input").index(this));
        });

        $('#pt_table').on('mouseenter', 'tbody tr', function() {
            $(this).find('a').css('text-decoration', 'underline');
        });
        $('#pt_table').on('mouseleave', 'tbody tr', function() {
            $(this).find('a').css('text-decoration', '');
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
                top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
            }
        });
    });

    function wrapInLink(data, type, full) {
        if (type == 'display') {
            return '<a href="">' + data + "</a>";
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

</script>
<?php
    /** @var EventDispatcher */
    $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
    $arrOeUiSettings = array(
    'heading_title' => xl('Patient Finder'),
    'include_patient_name' => false,
    'expandable' => true,
    'expandable_files' => array('dynamic_finder_xpd'),//all file names need suffix _xpd
    'action' => "search",//conceal, reveal, search, reset, link or back
    'action_title' => "",//only for action link, leave empty for conceal, reveal, search
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => "",
    'page_id' => 'dynamic_finder',
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);

    $eventDispatcher->addListener(PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER, function ($event): void {
        if ($event->getPageId() !== 'dynamic_finder') {
            return;
        }

        $event->setPrimaryMenuItem(new BaseMenuItem([
            'displayText' => xl('Add New Patient'),
            'linkClassList' => ['btn-add'],
            'id' => $GLOBALS['webroot'] . '/interface/new/new.php',
            'acl' => ['patients', 'demo', ['write', 'addonly']]
        ]));
    });
    ?>
</head>
<body>
<?php

function rp()
{
    $sql = "SELECT option_id, title FROM list_options WHERE list_id = 'recent_patient_columns' AND activity = '1' ORDER BY seq ASC";
    $res = sqlStatement($sql);
    $headers = [];
    while ($row = sqlFetchArray($res)) {
        $headers[] = $row;
    }
    $patientService = new PatientService();
    $rp = $patientService->getRecentPatientList();
    // Get a list of the columns in patient_data that are either date or datetime:
    $sql_dtCols = "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = DATABASE() AND TABLE_NAME = 'patient_data' AND (data_type = 'datetime' OR data_type = 'date')";
    $res_dtCols = sqlStatement($sql_dtCols);
    $pd_dtCols = [];
    while ($row = sqlFetchArray($res_dtCols)) {
        $pd_dtCols[] = $row;
    }
    $date_cols = [];
    $datetime_cols = [];
    foreach ($pd_dtCols as $v) {
        if ($v['data_type'] == "datetime") {
            $datetime_cols[] = $v['column_name'];
        } else if ($v['data_type'] == "date") {
            $date_cols[] = $v['column_name'];
        }
    }
    // Build SQL statement to pull desired columns from patient_data table...
    $pd_sql = "SELECT pid";
    foreach ($headers as $v) {
        $pd_sql .= ', ';
        $col_name = $v['option_id'];
        $dt_format = '';
        if (in_array($col_name, $date_cols) || in_array($col_name, $datetime_cols)) {
            switch ($GLOBALS['date_display_format']) {
                case 0: // mysql YYYY-MM-DD format
                    $dt_format = "'%Y-%m-%d";
                    break;
                case 1: // MM/DD/YYYY format
                    $dt_format = "'%m/%d/%Y";
                    break;
                case 2: // DD/MM/YYYY format
                    $dt_format = "'%d/%m/%Y";
                    break;
            }
            if (in_array($col_name, $datetime_cols)) {
                switch ($GLOBALS['time_display_format']) {
                    case 0: // 24 Hr fmt
                        $dt_format .= " %T";
                        break;
                    case 1: // AM PM fmt
                        $dt_format .= " %r";
                        break;
                }
            }
            $dt_format .= "'";  // Don't forget the closing '!
            $pd_sql .= "DATE_FORMAT(" . $col_name . ", " . $dt_format . ") AS " . $col_name;
        } else {
            $pd_sql .= $col_name;
        }
    }
    $pd_sql .= " FROM patient_data WHERE pid = ?";
    $pd_data = [];
    foreach ($rp as $v) {
        $pd_data[] = sqlQuery($pd_sql, $v['pid']);
    }
    return ['headers' => $headers, 'rp' => $pd_data];
}

$rp = rp();

$templateVars = [
    'oeContainer' => $oemr_ui->oeContainer(),
    'oeBelowContainerDiv' => $oemr_ui->oeBelowContainerDiv(),
    'pageHeading' => $oemr_ui->pageHeading(),
    'header0' => $header0,
    'header' => $header,
    'colcount' => $colcount,
    'headers' => $rp['headers'],
    'rp' => $rp['rp'],
];

$twig = new TwigContainer(null, $GLOBALS['kernel']);
$t = $twig->getTwig();
echo $t->render('patient_finder/finder.html.twig', $templateVars);

?>
</body>
</html>
