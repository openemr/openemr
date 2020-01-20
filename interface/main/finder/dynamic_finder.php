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
 * @copyright Copyright (c) 2012-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . "/../../globals.php");
require_once "$srcdir/user.inc";
require_once "$srcdir/options.inc.php";

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
$colcount = 0;
$header0 = "";
$header = "";
$coljson = "";
$res = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title");
while ($row = sqlFetchArray($res)) {
    $colname = $row['option_id'];
    $title = xl_list_label($row['title']);
    $title1 = ($title == xl('Full Name'))? xl('Name'): $title;
    $header .= "   <th>";
    $header .= text($title);
    $header .= "</th>\n";
    $header0 .= "   <td ><input type='text' size='20' ";
    $header0 .= "value='' class='search_init' placeholder='".xla("Search by"). " " . $title1 ."'/></td>\n";
    if ($coljson) {
        $coljson .= ", ";
    }
    $coljson .= "{\"sName\": \"" . addcslashes($colname, "\t\r\n\"\\") . "\"}";
    ++$colcount;
}
$loading = "<i class='fa fa-refresh fa-2x fa-spin'></i>";
?>
<html>
<head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Patient Finder"); ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt/css/jquery.dataTables.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt/css/colReorder.dataTables.min.css" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder/js/dataTables.colReorder.min.js"></script>
<style>
    /* Finder Processing style */
    div.dataTables_wrapper div.dataTables_processing {
        top: -20px;
        width: auto;
        margin: 0;
        color: var(--danger);
        transform: translateX(-50%);
    }
    
    @media screen and (max-width: 640px) {
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter {
            float: inherit;
            text-align: justify;
        }
    }
    
/* Color Overrides for jQuery-DT */
table.dataTable thead th,
table.dataTable thead td {
    border-bottom: 1px solid var(--gray900) !important;
}
table.dataTable tfoot th,
table.dataTable tfoot td {
    border-top: 1px solid var(--gray900) !important;
}
table.dataTable tbody tr {
  background-color: var(--white) !important;
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

table.dataTable.hover tbody tr:hover,
table.dataTable.display tbody tr:hover {
  background-color: var(--light) !important;
}
table.dataTable.order-column tbody tr > .sorting_1,
table.dataTable.order-column tbody tr > .sorting_2,
table.dataTable.order-column tbody tr > .sorting_3,
table.dataTable.display tbody tr > .sorting_1,
table.dataTable.display tbody tr > .sorting_2,
table.dataTable.display tbody tr > .sorting_3 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.odd > .sorting_1,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.odd > .sorting_2,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_2 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.odd > .sorting_3,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_3 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.even > .sorting_1,
table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.even > .sorting_2,
table.dataTable.order-column.stripe tbody tr.even > .sorting_2 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr.even > .sorting_3,
table.dataTable.order-column.stripe tbody tr.even > .sorting_3 {
  background-color: var(--light) !important;
}
table.dataTable.display tbody tr:hover > .sorting_1,
table.dataTable.order-column.hover tbody tr:hover > .sorting_1 {
  background-color: var(--gray200) !important;
}
table.dataTable.display tbody tr:hover > .sorting_2,
table.dataTable.order-column.hover tbody tr:hover > .sorting_2 {
  background-color: var(--gray200) !important;
}
table.dataTable.display tbody tr:hover > .sorting_3,
table.dataTable.order-column.hover tbody tr:hover > .sorting_3 {
  background-color: var(--gray200) !important;
}
table.dataTable.no-footer {
  border-bottom: 1px solid var(--gray900) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
  color: var(--dark) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  color: var(--dark) !important;
  border: 1px solid var(--gray600) !important;
  background-color: var(--white) !important;
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, var(--white)), color-stop(100%, var(--gray300))) !important;
  background: -webkit-linear-gradient(top, var(--white) 0%, var(--gray300) 100%) !important;
  background: -moz-linear-gradient(top, var(--white) 0%, var(--gray300) 100%) !important;
  background: -ms-linear-gradient(top, var(--white) 0%, var(--gray300) 100%) !important;
  background: -o-linear-gradient(top, var(--white) 0%, var(--gray300) 100%) !important;
  background: linear-gradient(to bottom, var(--white) 0%, var(--gray300) 100%) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled.
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
  color: var(--gray700) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  color: var(--white) !important;
  border: 1px solid var(--gray900) !important;
  background-color: var(--gray700) !important;
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #585858), color-stop(100%, var(--gray900))) !important;
  background: -webkit-linear-gradient(top, var(--gray700) 0%, var(--gray900) 100%) !important;
  background: -moz-linear-gradient(top, var(--gray700) 0%, var(--gray900) 100%) !important;
  background: -ms-linear-gradient(top, var(--gray700) 0%, var(--gray900) 100%) !important;
  background: -o-linear-gradient(top, var(--gray700) 0%, var(--gray900) 100%) !important;
  background: linear-gradient(to bottom, var(--gray700) 0%, var(--gray900) 100%) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:active {
  background-color: var(--dark) !important;
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, var(--dark)), color-stop(100%, var(--gray900))) !important;
  background: -webkit-linear-gradient(top, var(--dark) 0%, var(--gray900) 100%) !important;
  background: -moz-linear-gradient(top, var(--dark) 0%, var(--gray900) 100%) !important;
  background: -ms-linear-gradient(top, var(--dark) 0%, var(--gray900) 100%) !important;
  background: -o-linear-gradient(top, var(--dark) 0%, var(--gray900) 100%) !important;
  background: linear-gradient(to bottom, var(--dark) 0%, var(--gray900) 100%) !important;
  box-shadow: inset 0 0 3px var(--gray900) !important;
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
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
    color: var(--dark) !important;
}
.dataTables_wrapper.no-footer .dataTables_scrollBody {
    border-bottom: 1px solid var(--gray900) !important;
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
            <?php $translationsDatatablesOverride = array('processing' => $loading); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });


        $("div.mytopdiv").html("<form name='myform'><label for='form_new_window' id='form_new_window_label'><input type='checkbox' id='form_new_window' name='form_new_window' value='1'<?php
        if (!empty($GLOBALS['gbl_pt_list_new_window'])) {
            echo ' checked';
        }
        ?> /><?php echo xlt('Open in New Window'); ?></label><label for='setting_search_type' id='setting_search_type_label'><input type='checkbox' name='setting_search_type'  id='setting_search_type' onchange='persistCriteria(this, event)' value='<?php echo attr($patient_finder_exact_search); ?>'<?php echo text($patient_finder_exact_search); ?>/><?php echo xlt('Search with exact method'); ?></label></form>");

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
                top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
            }
        });
    });

    function openNewTopWindow(pid) {
        document.fnew.patientID.value = pid;
        top.restoreSession();
        document.fnew.submit();
    }

    function persistCriteria(el, e) {
        e.preventDefault();
        let target = uspfx + "patient_finder_exact_search";
        let val = el.checked ? ' checked' : ' ';
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
    $arrOeUiSettings = array(
    'heading_title' => xl('Patient Finder'),
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
    ?>
</head>
<body class="body_top">
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
         <div class="row">
            <div class="col-sm-12">
                <div class="page-header">
                    <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php if (acl_check('patients', 'demo', '', array('write','addonly'))) { ?>
                    <button id="create_patient_btn1" class="btn btn-secondary btn-add" onclick="top.restoreSession();top.RTop.location = '<?php echo $web_root ?>/interface/new/new.php'"><?php echo xlt('Add New Patient'); ?></button>
                <?php } ?>
            </div>
            </div>
        <br />
        <div class="row">
            <div class="col-sm-12">
                <div id="dynamic"><!-- TBD: id seems unused, is this div required? -->
                    <!-- Class "display" is defined in demo_table.css -->
                    <table border="0" cellpadding="0" cellspacing="0" class="display" id="pt_table">
                        <thead>
                            <tr id="advanced_search" class="hideaway"  style="display: none;">
                                <?php echo $header0; ?>
                            </tr>
                            <tr class="head">
                                <?php echo $header; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Class "dataTables_empty" is defined in jquery.dataTables.css -->
                                <td class="dataTables_empty" colspan="<?php echo attr($colcount); ?>">...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <!-- form used to open a new top level window when a patient row is clicked -->
                <form name='fnew' method='post' target='_blank' action='../main_screen.php?auth=login&site=<?php echo attr_url($_SESSION['site_id']); ?>'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <input type='hidden' name='patientID' value='0'/>
                </form>
            </div>
        </div>
    </div> <!--End of Container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>

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
        $(window).on('resize', function() {//hide superfluous elements on Smartphones
            var winWidth = $(this).width();
            if (winWidth <  750) {
                $("#pt_table_filter").addClass ("hidden");
                $("#pt_table_length").addClass ("hidden");
                $("#show_hide").addClass ("hidden");
                $("#search_hide").addClass ("hidden");
                
                
            } else {
                $("#pt_table_filter").removeClass ("hidden");
                $("#pt_table_length").removeClass ("hidden");
                $("#show_hide").removeClass ("hidden");
                $("#search_hide").addClass ("hidden");
            }
        });
    </script>
    
    <script>
        document.addEventListener('touchstart', {});
    </script>
</body>
</html>
