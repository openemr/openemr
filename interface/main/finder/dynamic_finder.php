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
 * @copyright Copyright (c) 2012-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once "$srcdir/user.inc";
require_once "$srcdir/options.inc.php";
use OpenEMR\Core\Header;

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
?>
<html>
<head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Patient Finder"); ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt/css/jquery.dataTables.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt/css/colReorder.dataTables.css" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder/js/dataTables.colReorder.js"></script>

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
                "sAjaxSource": "dynamic_finder_ajax.php?csrf_token_form=" + <?php echo js_escape(urlencode(collectCsrfToken())); ?>,
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

        function persistCriteria(el, e){
            e.preventDefault();
            let target = uspfx + "patient_finder_exact_search";
            let val = el.checked ? ' checked' : ' ';
            $.post( "../../../library/ajax/user_settings.php",
                {
                    target: target,
                    setting: val,
                    csrf_token_form: "<?php echo attr(collectCsrfToken()); ?>"
                }
            );
        }

    </script>
    <?php
    //to determine and set the page to open in the desired state - expanded or centered, any selection the user makes will
    //become the user-specific default for that page. collectAndOrganizeExpandSetting() takes a single indexed array as an
    //argument, containing one or more elements, the name of the current file is the first element, if there are linked
    //files they should be listed thereafter, please add _xpd suffix to the file name
    $arr_files_php = array("dynamic_finder_xpd");
    $current_state = collectAndOrganizeExpandSetting($arr_files_php);
    require_once("$srcdir/expand_contract_inc.php");
    ?>
    <script>
    <?php require_once("$include_root/expand_contract_js.php");//jQuery to provide expand/contract icon toggle if page is expandable ?>
    </script>

</head>
<body class="body_top">
    <div class="<?php echo $container;?> expandable">
        <div class="row">
            <div class="col-sm-12">
                <h2>
                <?php echo xlt('Patient Finder') ?> <i id="exp_cont_icon" class="fa <?php echo attr($expand_icon_class);?> oe-superscript-small expand_contract"
                title="<?php echo attr($expand_title); ?>" aria-hidden="true"></i> <i id="show_hide" class="fa fa-search-plus fa-2x small" title="<?php echo xla('Click to show advanced search'); ?>"></i>
                </h2>
            </div>
        </div>
        <br>
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
                <form name='fnew' method='post' target='_blank' action='../main_screen.php?auth=login&site=<?php echo attr(urlencode($_SESSION['site_id'])); ?>'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
                    <input type='hidden' name='patientID' value='0'/>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
    <script>
    $(document).ready(function(){
        $("#pt_table").removeAttr("style");
        $("#exp_cont_icon").click(function(){
            $("#pt_table").removeAttr("style");
        });
    });
    </script>
    <script>
    $('#show_hide').click(function () {
        var elementTitle = $('#show_hide').prop('title');
        var hideTitle = '<?php echo xla('Click to hide advanced search'); ?>';
        var showTitle = '<?php echo xla('Click to show advanced search'); ?>';
        $('.hideaway').toggle();
        $(this).toggleClass('fa-search-plus fa-search-minus');
        if (elementTitle == hideTitle) {
            elementTitle = showTitle;
        } else if (elementTitle == showTitle) {
            elementTitle = hideTitle;
        }
        $('#show_hide').prop('title', elementTitle);
    });
    </script>
</body>
</html>
