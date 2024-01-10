<?php

/**
 * LBF form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com> contributed the header and footer only
 * @copyright Copyright (c) 2009-2019 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/encounter.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use Mpdf\Mpdf;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Pdf\Config_Mpdf;

// Font size in points for table cell data.
$FONTSIZE = 9;

// The form name is passed to us as a GET parameter.
$formname = isset($_GET['formname']) ? $_GET['formname'] : '';

$patientid = empty($_REQUEST['patientid']) ? 0 : (0 + $_REQUEST['patientid']);
if ($patientid < 0) {
    $patientid = (int) $pid; // -1 means current pid
}
// PDF header information
$patientname = getPatientName($patientid);
$patientdob = getPatientData($patientid, "DOB");
$dateofservice = fetchDateService($encounter);

$visitid = empty($_REQUEST['visitid']) ? 0 : (0 + $_REQUEST['visitid']);
if ($visitid < 0) {
    $visitid = (int) $encounter; // -1 means current encounter
}

$formid = empty($_REQUEST['formid']) ? 0 : (0 + $_REQUEST['formid']);

// True if to display as a form to complete, false to display as information.
$isblankform = empty($_REQUEST['isform']) ? 0 : 1;

$CPR = 4; // cells per row

$grparr = array();
getLayoutProperties($formname, $grparr, '*');
$lobj = $grparr[''];
$formtitle = $lobj['grp_title'];
$grp_last_update = $lobj['grp_last_update'];

if (!empty($lobj['grp_columns'])) {
    $CPR = intval($lobj['grp_columns']);
}
if (!empty($lobj['grp_size'   ])) {
    $FONTSIZE = intval($lobj['grp_size']);
}
if ($lobj['grp_services']) {
    $LBF_SERVICES_SECTION = $lobj['grp_services'] == '*' ? '' : $lobj['grp_services'];
}
if ($lobj['grp_products']) {
    $LBF_PRODUCTS_SECTION = $lobj['grp_products'] == '*' ? '' : $lobj['grp_products'];
}
if ($lobj['grp_diags'   ]) {
    $LBF_DIAGS_SECTION    = $lobj['grp_diags'   ] == '*' ? '' : $lobj['grp_diags'   ];
}

// Check access control.
if (!empty($lobj['aco_spec'])) {
    $LBF_ACO = explode('|', $lobj['aco_spec']);
}
if (!AclMain::aclCheckCore('admin', 'super') && !empty($LBF_ACO)) {
    if (!AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1])) {
        die(xlt('Access denied'));
    }
}

// Html2pdf fails to generate checked checkboxes properly, so write plain HTML
// if we are doing a visit-specific form to be completed.
// TODO - now use mPDF, so should test if still need this fix
$PDF_OUTPUT = ($formid && $isblankform) ? false : true;
//$PDF_OUTPUT = false; // debugging

if ($PDF_OUTPUT) {
    $config_mpdf = Config_Mpdf::getConfigMpdf();
    $config_mpdf['margin_top'] = $config_mpdf['margin_top'] * 1.5;
    $config_mpdf['margin_bottom'] = $config_mpdf['margin_bottom'] * 1.5;
    $config_mpdf['margin_header'] = $GLOBALS['pdf_top_margin'];
    $config_mpdf['margin_footer'] =  $GLOBALS['pdf_bottom_margin'];
    $pdf = new mPDF($config_mpdf);
    $pdf->SetDisplayMode('real');
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
}

if ($visitid && (isset($LBF_SERVICES_SECTION) || isset($LBF_DIAGS_SECTION) || isset($LBF_PRODUCTS_SECTION))) {
    require_once("$srcdir/FeeSheetHtml.class.php");
    $fs = new FeeSheetHtml($pid, $encounter);
}

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = ? AND uor > 0 " .
  "ORDER BY group_id, seq", array($formname));
?>
<?php if (!$PDF_OUTPUT) { ?>
<html>
<head>
<?php } ?>

<style>

<?php if ($PDF_OUTPUT) { ?>
td {
 font-family: Arial;
 font-weight: normal;
 font-size: <?php echo text($FONTSIZE); ?>pt;
}
<?php } else { ?>
body, td {
 font-family: Arial, Helvetica, sans-serif;
 font-weight: normal;
 font-size: <?php echo text($FONTSIZE); ?>pt;
}
body {
 padding: 5pt 5pt 5pt 5pt;
}
<?php } ?>

p.grpheader {
 font-family: Arial;
 font-weight: bold;
 font-size: <?php echo round($FONTSIZE * 1.00); ?>pt;
 margin-bottom: <?php echo round($FONTSIZE * 0.00); ?>pt;
 margin-top: <?php echo round($FONTSIZE * 0.44); ?>pt;
}

div.grpheader {
 font-family: Arial;
 font-weight: bold;
 font-size: <?php echo round($FONTSIZE * 1.00); ?>pt;
 color: #000000;
 background-color: #cccccc;
 padding: 2pt;
 margin-bottom: <?php echo round($FONTSIZE * 0.22); ?>pt;
 margin-top: <?php echo round($FONTSIZE * 0.22); ?>pt;
}

div.section {
 width: 98%;
<?php
  // html2pdf screws up the div borders when a div overflows to a second page.
  // Our temporary solution is to turn off the borders in the case where this
  // is likely to happen (i.e. where all form options are listed).
  // TODO - now use mPDF, so should test if still need this fix
if (!$isblankform) {
    ?>
border-style: solid;
border-width: 1px;
border-color: #ffffff #ffffff #ffffff #ffffff;
<?php } // below was 2 5 5 5 ?>
 padding: 0pt 5pt 0pt 5pt;
}
div.section table {
 border-collapse: collapse;
 width: 100%;
}
div.section td.stuff {
 vertical-align: top;
<?php if ($isblankform) { ?>
 height: 16pt;
<?php } ?>
}

<?php
// Generate widths for the various numbers of label columns and data columns.
for ($lcols = 1; $lcols < $CPR; ++$lcols) {
    $dcols = $CPR - $lcols;
    $lpct = intval(100 * $lcols / $CPR);
    $dpct = 100 - $lpct;
    echo "td.lcols$lcols { width: $lpct%; text-align: right; }\n";
    echo "td.dcols$dcols { width: $dpct%; }\n";
}
?>

.mainhead {
 font-weight: bold;
 font-size: <?php echo round($FONTSIZE * 1.56); ?>pt;
 text-align: center;
}

.subhead {
 font-weight: bold;
 font-size: <?php echo round($FONTSIZE * 0.89); ?>pt;
}

.under {
 border-style: solid;
 border-width: 0 0 0px 0;
 border-color: #999999;
}

.RS {
 border-style: solid;
 border-width: 0 0 1px 0;
 border-color: #999999;
}

.RO {
 border-style: solid;
 border-width: 1px 1px 1px 1px !important;
 border-color: #999999;
}

.ftitletable {
 width: 100%;
 margin: 0 0 8pt 0;
}
.ftitlecell1 {
 width: 33%;
 vertical-align: top;
 text-align: left;
 font-size: <?php echo round($FONTSIZE * 1.56); ?>pt;
 font-weight: bold;
}
.ftitlecell2 {
 width: 33%;
 vertical-align: top;
 text-align: right;
 font-size: <?php echo text($FONTSIZE); ?>pt;
}
.ftitlecellm {
 width: 34%;
 vertical-align: top;
 text-align: center;
 font-size: <?php echo round($FONTSIZE * 1.56); ?>pt;
 font-weight: bold;
}
</style>

<?php if (!$PDF_OUTPUT) { ?>
</head>
<body bgcolor='#ffffff'>
<?php } ?>

<form>

<?php
// Generate header with optional logo.
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "$web_root/$ma_logo_path";
}

echo genFacilityTitle($formtitle, -1, $logo);

if ($PDF_OUTPUT) {
    echo genPatientHeaderFooter($pid, $DOS = $dateofservice);
}
?>

<?php if ($isblankform) { ?>
<span class='subhead'>
    <?php echo xlt('Patient') ?>: ________________________________________ &nbsp;
    <?php echo xlt('Clinic') ?>: ____________________ &nbsp;
    <?php echo xlt('Date') ?>: ____________________<br />&nbsp;<br />
</span>
<?php } ?>

<?php

function end_cell()
{
    global $item_count, $cell_count;
    if ($item_count > 0) {
        echo "</td>";
        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function getContent()
{
    $content = ob_get_clean();
    return $content;
}

$cell_count = 0;
$item_count = 0;

// This string is the active group levels. Each leading substring represents an instance of nesting.
$group_levels = '';

// This indicates if </table> will need to be written to end the fields in a group.
$group_table_active = false;

while ($frow = sqlFetchArray($fres)) {
    $this_group   = $frow['group_id'];
    $titlecols    = $frow['titlecols'];
    $datacols     = $frow['datacols'];
    $data_type    = $frow['data_type'];
    $field_id     = $frow['field_id'];
    $list_id      = $frow['list_id'];
    $edit_options = $frow['edit_options'];
    $jump_new_row      = isOption($edit_options, 'J');
    $prepend_blank_row = isOption($edit_options, 'K');

    $currvalue = '';
    if ($formid || $visitid) {
        $currvalue = lbf_current_value($frow, $formid, $visitid);
    }

    // Skip this field if it's a form with data and skip conditions call for that.
    // Note this also accumulates info for subsequent skip tests.
    if (!$isblankform && isSkipped($frow, $currvalue) == 'skip') {
        continue;
    }

    if ($currvalue === false) {
        // Should not happen.
        error_log("Function lbf_current_value() failed for field '" . errorLogEscape($field_id) . "'.");
        continue;
    }

    // Skip this field if its do-not-print option is set.
    if (isOption($edit_options, 'X') !== false) {
        continue;
    }

    $this_levels = $this_group;
    $i = 0;
    $mincount = min(strlen($this_levels), strlen($group_levels));
    while ($i < $mincount && $this_levels[$i] == $group_levels[$i]) {
        ++$i;
    }
    // $i is now the number of initial matching levels.

    // If ending a group or starting a subgroup, terminate the current row and its table.
    if ($group_table_active && ($i != strlen($group_levels) || $i != strlen($this_levels))) {
        end_row();
        echo " </table>\n";
        $group_table_active = false;
    }

    // Close any groups that we are done with.
    while (strlen($group_levels) > $i) {
        $gname = $grparr[$group_levels]['grp_title'];
        $group_levels = substr($group_levels, 0, -1); // remove last character
        echo "</div>\n";
        echo "</nobreak>\n";
    }

    // If there are any new groups, open them.
    while ($i < strlen($this_levels)) {
        end_row();
        if ($group_table_active) {
            echo " </table>\n";
            $group_table_active = false;
        }

        $group_levels .= $this_levels[$i++];
        $gname = $grparr[substr($group_levels, 0, $i)]['grp_title'];
        $subtitle = xl_layout_label($grparr[substr($group_levels, 0, $i)]['grp_subtitle']);

        // This is also for mPDF. Telling it that the following stuff should
        // start on a new page if there is not otherwise room for it on this page.
        echo "<nobreak>\n";
        echo "<div class='grpheader'>" . text(xl_layout_label($gname)) . "</div>\n";
        echo "<div class='section'>\n";
        echo " <table border='0' cellpadding='0'>\n";
        echo "  <tr>";
        for ($i = 1; $i <= $CPR; ++$i) {
            $tmp = $i % 2 ? 'lcols1' : 'dcols1';
            echo "<td class='" . attr($tmp) . "'></td>";
        }
        echo "</tr>\n";
        if ($subtitle) {
            // There is a group subtitle so show it.
            echo "<tr><td class='bold' style='color:#0000ff' colspan='" . attr($CPR) . "'>" . text($subtitle) . "</td></tr>\n";
            echo "<tr><td class='bold' style='height:4pt' colspan='" . attr($CPR) . "'></td></tr>\n";
        }
        $group_table_active = true;
    }

    // Handle starting of a new row.
    if (($cell_count + $titlecols + $datacols) > $CPR || $cell_count == 0 || $prepend_blank_row || $jump_new_row) {
        end_row();
        if ($prepend_blank_row) {
            echo "  <tr><td class='text' style='font-size:25%' colspan='" . attr($CPR) . "'>&nbsp;</td></tr>\n";
        }
        if (isOption($edit_options, 'RS')) {
            echo " <tr class='RS'>";
        } elseif (isOption($edit_options, 'RO')) {
            echo " <tr class='RO'>";
        } else {
            echo " <tr>";
        }
    }

    if ($item_count == 0 && $titlecols == 0) {
        $titlecols = 1;
    }

    // Handle starting of a new label cell.
    if ($titlecols > 0) {
        end_cell();
        if (isOption($edit_options, 'SP')) {
            $datacols = 0;
            $titlecols = $CPR;
        }
        echo "<td colspan='" . attr($titlecols) . "' ";
        echo "class='lcols" . attr($titlecols) . " stuff " . (($frow['uor'] == 2) ? "required'" : "bold'");
        if ($cell_count == 2) {
            echo " style='padding-left:10pt'";
        }
        // echo " nowrap>"; // html2pdf misbehaves with this.
        // TODO - now use mPDF, so should test if still need this fix
        echo ">";
        $cell_count += $titlecols;
    }
    ++$item_count;

    echo "<b>";

    if ($frow['title']) {
        echo (text(xl_layout_label($frow['title'])));
    } else {
        echo "&nbsp;";
    }

    echo "</b>";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
        end_cell();
        if (isOption($edit_options, 'DS')) {
            echo "<td colspan='" . attr($datacols) . "' class='dcols" . attr($datacols) . " stuff under RS' style='";
        } elseif (isOption($edit_options, 'DO')) {
            echo "<td colspan='" . attr($datacols) . "' class='dcols" . attr($datacols) . " stuff under RO' style='";
        } else {
            echo "<td colspan='" . attr($datacols) . "' class='dcols" . attr($datacols) . " stuff under' style='";
        }

        if ($cell_count > 0) {
            echo "padding-left:5pt;";
        }
        if (in_array($data_type, array(21,27,40))) {
            // Omit underscore for checkboxes, radio buttons and images.
            echo "border-width:0 0 0 0;";
        }
        echo "'>";
        $cell_count += $datacols;
    }

    ++$item_count;

    if ($isblankform) {
        generate_print_field($frow, $currvalue);
    } else {
        $s = generate_display_field($frow, $currvalue);
        if ($s === '') {
            $s = '&nbsp;';
        }
        echo $s;
    }
}

// Close all open groups.
if ($group_table_active) {
    end_row();
    echo " </table>\n";
    $group_table_active = false;
}
while (strlen($group_levels)) {
    $gname = $grparr[$group_levels]['grp_title'];
    $group_levels = substr($group_levels, 0, -1); // remove last character
    echo "</div>\n";
    echo "</nobreak>\n";
}

$fs = false;
if ($fs && (isset($LBF_SERVICES_SECTION) || isset($LBF_DIAGS_SECTION))) {
    $fs->loadServiceItems();
}

if ($fs && isset($LBF_SERVICES_SECTION)) {
    $s = '';
    foreach ($fs->serviceitems as $lino => $li) {
        // Skip diagnoses; those would be in the Diagnoses section below.
        if ($code_types[$li['codetype']]['diag']) {
            continue;
        }
        $s .= "  <tr>\n";
        $s .= "   <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
        $s .= "   <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
        $s .= "  </tr>\n";
    }
    if ($s) {
        echo "<nobreak>\n";
        // echo "<p class='grpheader'>" . xlt('Services') . "</p>\n";
        echo "<div class='grpheader'>" . xlt('Services') . "</div>\n";
        echo "<div class='section'>\n";
        echo " <table border='0' cellpadding='0' style='width:'>\n";
        echo $s;
        echo " </table>\n";
        echo "</div>\n";
        echo "</nobreak>\n";
    }
} // End Services Section

if ($fs && isset($LBF_PRODUCTS_SECTION)) {
    $s = '';
    $fs->loadProductItems();
    foreach ($fs->productitems as $lino => $li) {
        $s .= "  <tr>\n";
        $s .= "   <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
        $s .= "   <td class='text' align='right'>" . text($li['units']) . "&nbsp;</td>\n";
        $s .= "  </tr>\n";
    }
    if ($s) {
        echo "<nobreak>\n";
        // echo "<p class='grpheader'>" . xlt('Products') . "</p>\n";
        echo "<div class='grpheader'>" . xlt('Products') . "</div>\n";
        echo "<div class='section'>\n";
        echo " <table border='0' cellpadding='0' style='width:'>\n";
        echo $s;
        echo " </table>\n";
        echo "</div>\n";
        echo "</nobreak>\n";
    }
} // End Products Section

if ($fs && isset($LBF_DIAGS_SECTION)) {
    $s = '';
    foreach ($fs->serviceitems as $lino => $li) {
        // Skip anything that is not a diagnosis; those are in the Services section above.
        if (!$code_types[$li['codetype']]['diag']) {
            continue;
        }
        $s .= "  <tr>\n";
        $s .= "   <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
        $s .= "   <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
        $s .= "  </tr>\n";
    }
    if ($s) {
        echo "<nobreak>\n";
        // echo "<p class='grpheader'>" . xlt('Diagnoses') . "</p>\n";
        echo "<div class='grpheader'>" . xlt('Diagnoses') . "</div>\n";
        echo "<div class='section'>\n";
        echo " <table border='0' cellpadding='0' style='width:'>\n";
        echo $s;
        echo " </table>\n";
        echo "</div>\n";
        echo "</nobreak>\n";
    }
} // End Services Section

?>

<p style='text-align:center' class='small'>
  <?php echo text(xl('Rev.') . ' ' . substr($grp_last_update, 0, 10)); ?>
</p>

</form>
<?php
if ($PDF_OUTPUT) {
    $content = getContent();
    if (isset($_GET['return_content'])) {
        echo js_escape($content);
        exit();
    }
    $pdf->writeHTML($content);
    $pdf->Output('form.pdf', 'I'); // D = Download, I = Inline
} else {
    ?>
<script>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
</script>
</body>
</html>
<?php } ?>
