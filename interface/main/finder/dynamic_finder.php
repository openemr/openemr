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
require_once($GLOBALS['srcdir'] . "/user.inc.php");
require_once($GLOBALS['srcdir'] . "/options.inc.php");

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
$current_state_patient_finder_exact_search = prevSetting($uspfx, 'patient_finder_exact_search', 'patient_finder_exact_search', '0');
$current_state_expand = prevSetting($uspfx, 'patient_finder_expand', 'dynamic_finder_xpd', '0');
//Is prevSetting deprecated?   does it work?

$oeContainer = ($current_state_expand) ? 'container-fluid' : 'container';

$expand_title = ($current_state_expand) ? xl('Click to contract page to center view') : xl('Click to expand page to full width');
$expand_icon = ($current_state_expand) ? 'fa-compress' : 'fa-expand';
$expand_class = ($current_state_expand) ? 'oe-center' : 'oe-expand';
$pt_table_w_auto = str_contains($oeContainer, "fluid") ? false : true;

$popup = empty($_REQUEST['popup']) ? 0 : 1;
$searchAny = empty($_GET['search_any']) ? "" : $_GET['search_any'];
unset($_GET['search_any']);
// Generate some code based on the list of columns.
//
$colcount = 1;
$table_header_search_boxes = "<td><input type='hidden'></td>";
$table_header_labels = "<th></th>";
$coljson = "{ name: 'fname', className: 'dt-control', orderable: false, data: null, searchable: false, defaultContent: '' }";
$orderjson = "";

$res = sqlStatement("SELECT option_id, title, toggle_setting_1, subtype FROM list_options WHERE " .
    "list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title");

$sort_dir_map = generate_list_map('Sort_Direction');

$colwidth = array();
$colwidth[] = "0.5rem";
$colindex = array();

while ($row = sqlFetchArray($res)) {
    $colname = $row['option_id'];
    $colindex[$colname] = $colcount;
    $colorder = $sort_dir_map[$row['toggle_setting_1']]; // Get the title 'asc' or 'desc' using the value
    $colwidth[] = $row['subtype'];
    $title = xl_list_label($row['title']);
    $title1 = ($title == xl('Full Name')) ? xl('Name') : $title;

    $table_header_labels .= "   <th>";
    $table_header_labels .= text($title);
    $table_header_labels .= "</th>\n";
    $table_header_search_boxes .= "   <td class='pl-1 pr-3'><input type='text' size='20' ";
    $table_header_search_boxes .= "value='' class='form-control search_init pl-2' placeholder='" . $title1 . "'/></td>\n";

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
$loading = "";
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs']); ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/main/finder/dynamic_finder.css">
    <title><?php echo xlt("Patient Finder"); ?></title>

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
                var searchExact = $("#setting_search_exact:checked").length > 0;
                aoData.push({"name": "searchExact", "value": searchExact});
            },
            // dom invokes ColReorderWithResize and allows inclusion of a custom div
            "dom": 'lrtip',
            // These column names come over as $_GET['sColumns'], a comma-separated list of the names.
            // See: http://datatables.net/usage/columns and
            // http://datatables.net/release-datatables/extras/ColReorder/server_side.html
            "columns": [ <?php echo $coljson; ?> ],
            "order": [ <?php echo $orderjson; ?> ],
            "lengthMenu": [10, 25, 50, 100, 250],
            "pageLength": <?php echo empty($GLOBALS['gbl_pt_list_page_size']) ? '10' : $GLOBALS['gbl_pt_list_page_size']; ?>,
            "initComplete": postDataTableInitialization,
            <?php // Bring in the translations ?>
            <?php $translationsDatatablesOverride = array('search' => (xla('Search all columns') . ':')); ?>
            <?php $translationsDatatablesOverride = array('processing' => $loading); ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });


        <?php
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
        header_rightNode = document.getElementById("header-right");
        select_searchNode = document.getElementById("select-search");
        custom_searchNode = document.getElementById("custom-search");
        pt_table_lengthNode = document.getElementById("pt_table_length");

        //searchNode.insertBefore(custom-searchNode, select_searchNode);
        //header_topNode.insertBefore(pt_table_lengthNode, header_buttonsNode);

        pt_table_infoNode = document.getElementById("pt_table_info");
        pt_table_paginateNode = document.getElementById("pt_table_paginate");
        footer_bottomNode = document.getElementById("footer-bottom");
        footer_bottomNode.append(pt_table_infoNode);
        footer_bottomNode.append(pt_table_paginateNode);        

        let colindex = <?php echo json_encode($colindex); ?>

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

        // Force styling on OemrUI buttons after they're rendered

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
                    let pid = rowNode.id.substring(4); 

                    let patientDetailNode = rowNode.nextElementSibling.querySelector(".patient_detail_container");
                    getPatientDetail(patientDetailNode, pid);

                }
            }
        }

        async function getPatientDetail(patientDetailNode, pid) {
            let url = "patient_data_ajax.php?pid=" + pid;
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

        // After DataTable initialization, move the length selector
        // Wait for DataTable to fully initialize
        $('#pt_table').on('init.dt', function() {
            // Move the length selector to be inline with the tabs
            var lengthSelector = $('#pt_table_length');
            var header_rightContainer = $('#header-right');
            
            // Create a wrapper div if needed to keep them on the same line
            if (header_rightContainer.length && lengthSelector.length) {
                // Detach the length selector from its current position
                lengthSelector.detach();
                
                // Insert it right after the tabs, but within the same flex container
                lengthSelector.addClass('ms-3 d-flex align-items-center');
                header_rightContainer.before(lengthSelector);
                
                // Ensure proper styling
                lengthSelector.find('label').addClass('mb-0');
                lengthSelector.find('select').addClass('form-control-sm');
            }
        });


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

        $('#expand_icon').click(function (e) {
            e.preventDefault();
            var elementTitle;
            var expandTitle = <?php echo xlj("Click to contract page to center view"); ?>;
            var contractTitle = <?php echo xlj("Click to expand page to full width"); ?>;
            var webroot = '<?php echo $GLOBALS['webroot']; ?>';
            var collectToken = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
            
            if ($(this).is('.oe-expand')) {
                elementTitle = expandTitle;
                $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
                $(this).removeClass('oe-expand').addClass('oe-center');
                $('#container_div').removeClass('container').addClass('container-fluid');
                $('#pt_table').removeClass('w-auto');
                
                // Save setting
                $.post(webroot + "/library/ajax/user_settings.php", {
                    target: 'dynamic_finder_xpd',
                    setting: 1,
                    csrf_token_form: collectToken
                });
            } else if ($(this).is('.oe-center')) {
                elementTitle = contractTitle;
                $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                $(this).removeClass('oe-center').addClass('oe-expand');
                $('#container_div').removeClass('container-fluid').addClass('container');
                $('#pt_table').addClass('w-auto');
                
                // Save setting
                $.post(webroot + "/library/ajax/user_settings.php", {
                    target: 'dynamic_finder_xpd',
                    setting: 0,
                    csrf_token_form: collectToken
                });
            }      
            $(this).attr('title', elementTitle);
        });
    
        // Search toggle functionality (replaces OemrUI search functionality)
        $('#search_icon').click(function(e) {
            e.preventDefault();
            var $icon = $(this).find('i');
            var $table_header_labels = $('#pt_table_header_labels')
            var showTitle = <?php echo xlj('Click to show search'); ?>;
            var hideTitle = <?php echo xlj('Click to hide search'); ?>;
            
            // Toggle the search row
            $('.hideaway').toggleClass('d-none');
            
            // Toggle icon and title
            if ($icon.hasClass('fa-search')) {
                $icon.removeClass('fa-search').addClass('fa-search-minus');
                $(this).attr('title', hideTitle);
                $table_header_labels.removeClass('d-none');
            } else {
                $icon.removeClass('fa-search-minus').addClass('fa-search');
                $(this).attr('title', showTitle);
                $table_header_labels.addClass('d-none');
            }
        });
    });

   // $(document).ready(function() {
            //$('#pt_table').DataTable({
            //    "searching": false, // Disables the global search input
            //    "lengthChange": true // Enables the "Show Entries" dropdown
            //});
   //     });

    function postDataTableInitialization ( settings, json) {
        // Format DataTable Columns
        let pt_tableNode = document.querySelector("table#pt_table");
        let pt_table_header_labels_th = pt_tableNode.querySelectorAll("th");

        let pt_table_header_search_boxes = document.querySelector("tr#pt_table_header_search_boxes");
        let pt_table_header_search_boxes_td = pt_table_header_search_boxes.querySelectorAll("td")

        var colwidth = <?php echo json_encode($colwidth); ?>;
        pt_table_header_labels_th.forEach( function(header, index) {
            header.style.width = colwidth[index];
        });
        pt_table_header_search_boxes_td.forEach( function(header, index) {
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

</script>

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

$acl_addpatient = AclMain::aclCheckCore('patients', 'demo', '', array('write','addonly'));
$rp = rp();

$templateVars = [
    'oeContainer' => $oeContainer,
    'expand_title' => $expand_title,
    'expand_icon' => $expand_icon,
    'expand_class' => $expand_class,
    'table_header_search_boxes' => $table_header_search_boxes,
    'table_header_labels' => $table_header_labels,
    'colcount' => $colcount,
    'headers' => $rp['headers'],
    'checked' => $checked,
    'acl_addpatient' => $acl_addpatient,
    'patient_finder_exact_search' => $current_state_patient_finder_exact_search,
    'rp' => $rp['rp']
];

$twig = new TwigContainer(null, $GLOBALS['kernel']);
$t = $twig->getTwig();
echo $t->render('patient_finder/finder.html.twig', $templateVars);

?>
</body>
</html>
