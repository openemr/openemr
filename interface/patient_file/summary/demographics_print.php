<?php

/**
 * This will print a blank form, and if "patientid" is specified then
 * any existing data for the specified patient is included.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

// Option to substitute a custom version of this script.
if (
    !empty($GLOBALS['gbl_rapid_workflow']) &&
    $GLOBALS['gbl_rapid_workflow'] == 'LBFmsivd' &&
    file_exists('../../../custom/demographics_print.php')
) {
    include('../../../custom/demographics_print.php');
    exit();
}

require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");

use Mpdf\Mpdf;
use OpenEMR\Common\Acl\AclMain;

$patientid = empty($_REQUEST['patientid']) ? 0 : 0 + $_REQUEST['patientid'];
if ($patientid < 0) {
    $patientid = (int) $pid; // -1 means current pid
}

// True if to display as a form to complete, false to display as information.
$isform = empty($_REQUEST['isform']) ? 0 : 1;

// Html2pdf fails to generate checked checkboxes properly, so write plain HTML
// if we are doing a patient-specific complete form.
// TODO - now use mPDF, so should test if still need this fix
$PDF_OUTPUT = ($patientid && $isform) ? false : true;

if ($PDF_OUTPUT) {
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => 'Letter',
        'default_font_size' => '9',
        'default_font' => 'dejavusans',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => 'P',
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'autoScriptToLang' => true,
        'keep_table_proportions' => true
    );
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
}

$CPR = 4; // cells per row

$prow = array();
$erow = array();
$irow = array();

if ($patientid) {
    $prow = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    $erow = getEmployerData($pid);
  // Check authorization.
    $thisauth = AclMain::aclCheckCore('patients', 'demo');
    if (!$thisauth) {
        die(xlt('Demographics not authorized'));
    }
    if ($prow['squad'] && ! AclMain::aclCheckCore('squads', $prow['squad'])) {
        die(xlt('You are not authorized to access this squad'));
    }
  // $irow = getInsuranceProviders(); // needed?
}

// Load array of properties for this layout and its groups.
$grparr = array();
getLayoutProperties('DEM', $grparr);

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 " .
  "ORDER BY group_id, seq");

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
 font-size: 9pt;
}
<?php } else { ?>
body, td {
 font-family: Arial, Helvetica, sans-serif;
 font-weight: normal;
 font-size: 9pt;
}
body {
 padding: 5pt 5pt 5pt 5pt;
}
<?php } ?>

p.grpheader {
 font-family: Arial;
 font-weight: bold;
 font-size: 12pt;
 margin-bottom: 4pt;
}

div.section {
 width: 98%;
<?php
  // html2pdf screws up the div borders when a div overflows to a second page.
  // Our temporary solution is to turn off the borders in the case where this
  // is likely to happen (i.e. where all form options are listed).
  // TODO - now use mPDF, so should test if still need this fix
if (!$isform) {
    ?>
border-style: solid;
border-width: 1px;
border-color: #000000;
<?php } ?>
 padding: 5pt;
}
div.section table {
 width: 100%;
}
div.section td.stuff {
 vertical-align: bottom;
 height: 16pt;
}

td.lcols1 { width: 20%; }
td.lcols2 { width: 50%; }
td.lcols3 { width: 70%; }
td.dcols1 { width: 30%; }
td.dcols2 { width: 50%; }
td.dcols3 { width: 80%; }

.mainhead {
 font-weight: bold;
 font-size: 14pt;
 text-align: center;
}

.under {
 border-style: solid;
 border-width: 0 0 1px 0;
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
 font-size: 14pt;
 font-weight: bold;
}
.ftitlecell2 {
 width: 33%;
 vertical-align: top;
 text-align: right;
 font-size: 9pt;
}
.ftitlecellm {
 width: 34%;
 vertical-align: top;
 text-align: center;
 font-size: 9pt;
 font-weight: bold;
}

</style>
</head>

<body bgcolor='#ffffff'>
<form>

<?php
// Generate header with optional logo.
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "$web_root/$ma_logo_path";
}

echo genFacilityTitle(xl('Registration Form'), -1, $logo);

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

function end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        end_row();
        echo " </table>\n";
        echo "</div>\n";
    }
}

function getContent()
{
    global $web_root, $webserver_root;
    $content = ob_get_clean();
    // Fix a nasty html2pdf bug - it ignores document root!
    // TODO - now use mPDF, so should test if still need this fix
    $i = 0;
    $wrlen = strlen($web_root);
    $wsrlen = strlen($webserver_root);
    while (true) {
        $i = stripos($content, " src='/", $i + 1);
        if ($i === false) {
            break;
        }
        if (
            substr($content, $i + 6, $wrlen) === $web_root &&
            substr($content, $i + 6, $wsrlen) !== $webserver_root
        ) {
            $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
        }
    }
    return $content;
}

$last_group = '';
$cell_count = 0;
$item_count = 0;

while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_id'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];
    $currvalue  = '';

    if (strpos($field_id, 'em_') === 0) {
        $tmp = substr($field_id, 3);
        if (isset($erow[$tmp])) {
            $currvalue = $erow[$tmp];
        }
    } else {
        if (isset($prow[$field_id])) {
            $currvalue = $prow[$field_id];
        }
    }

  // Handle a data category (group) change.
    if (strcmp($this_group, $last_group) != 0) {
        end_group();

        // if (strlen($last_group) > 0) echo "<br />\n";

        // This replaces the above statement and is an attempt to work around a
        // nasty html2pdf bug. When a table overflows to the next page, vertical
        // positioning for whatever follows it is off and can cause overlap.
        // TODO - now use mPDF, so should test if still need this fix
        if (strlen($last_group) > 0) {
            echo "</nobreak><br /><div><table><tr><td>&nbsp;</td></tr></table></div><br />\n";
        }

        // This is also for html2pdf. Telling it that the following stuff should
        // start on a new page if there is not otherwise room for it on this page.
        // TODO - now use mPDF, so should test if still need this fix
        echo "<nobreak>\n"; // grasping

        $group_name = $grparr[$this_group]['grp_title'];
        $last_group = $this_group;
        echo "<p class='grpheader'>" . text(xl_layout_label($group_name)) . "</p>\n";
              echo "<div class='section'>\n";
        echo " <table border='0' cellpadding='0'>\n";
        echo "  <tr><td class='lcols1'></td><td class='dcols1'></td><td class='lcols1'></td><td class='dcols1'></td></tr>\n";
    }

  // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
        end_row();
        echo "  <tr>";
    }

    if ($item_count == 0 && $titlecols == 0) {
        $titlecols = 1;
    }

  // Handle starting of a new label cell.
    if ($titlecols > 0) {
        end_cell();
        echo "<td colspan='" . attr($titlecols) . "' ";
        echo "class='lcols" . attr($titlecols) . " stuff " . (($frow['uor'] == 2) ? "required'" : "bold'");
        if ($cell_count == 2) {
            echo " style='padding-left:10pt'";
        }
        echo " nowrap>";
        $cell_count += $titlecols;
    }

    ++$item_count;

    echo "<b>";

    if ($frow['title']) {
        echo text((xl_layout_label($frow['title'])) . ":");
    } else {
        echo "&nbsp;";
    }

    echo "</b>";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
        end_cell();
        echo "<td colspan='" . attr($datacols) . "' class='dcols" . attr($datacols) . " stuff under'";
        /*****************************************************************
        // Underline is wanted only for fill-in-the-blank data types.
        if ($data_type < 21 && $data_type != 1 && $data_type != 3) {
          echo " class='under'";
        }
        *****************************************************************/
        if ($cell_count > 0) {
            echo " style='padding-left:5pt;'";
        }
        echo ">";
        $cell_count += $datacols;
    }

    ++$item_count;

    if ($isform) {
        generate_print_field($frow, $currvalue);
    } else {
        $s = generate_display_field($frow, $currvalue);
        if ($s === '') {
            $s = '&nbsp;';
        }
        echo $s;
    }
}

end_group();

// Ending the last nobreak section for html2pdf.
// TODO - now use mPDF, so should test if still need this fix
if (strlen($last_group) > 0) {
    echo "</nobreak>\n";
}
?>

</form>

<?php
if ($PDF_OUTPUT) {
    $content = getContent();
    $pdf->writeHTML($content);
    $pdf->Output('Demographics_form.pdf', 'I'); // D = Download, I = Inline
} else {
    ?>
<!-- This should really be in the onload handler but that seems to be unreliable and can crash Firefox 3. -->
<script>
opener.top.printLogPrint(window);
</script>
</body>
</html>
<?php } ?>
