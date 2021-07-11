<?php

/**
 * 2012 - Refactored extensively to allow for creating multiple feesheets on demand
 * uses a session array of PIDS by Medical Information Integration, LLC - mi-squared.com
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ron Pulcer <rspulcer_2k@yahoo.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2007-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ron Pulcer <rspulcer_2k@yahoo.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/appointments.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/user.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

function genColumn($ix)
{
    global $html;
    global $SBCODES;
    for ($imax = count($SBCODES); $ix < $imax; ++$ix) {
        $a = explode('|', $SBCODES[$ix], 2);
        $cmd = trim($a[0]);
        if ($cmd == '*C') { // column break
            return++$ix;
        }

        if ($cmd == '*B') { // Borderless and empty
            $html .= " <tr><td colspan='5' class='fscode' style='border-width:0 1px 0 0;padding-top:1px;' nowrap>&nbsp;</td></tr>\n";
        } elseif ($cmd == '*G') {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $html .= " <tr><td colspan='5' align='center' class='fsgroup' style='vertical-align:middle' nowrap>$title</td></tr>\n";
        } elseif ($cmd == '*H') {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $html .= " <tr><td colspan='5' class='fshead' style='vertical-align:middle' nowrap>$title</td></tr>\n";
        } else {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $b = explode(':', $cmd);
            $html .= " <tr>\n";
            $html .= " <td class='fscode' style='vertical-align:middle;width:14pt' nowrap>&nbsp;</td>\n";
            if (count($b) <= 1) {
                $code = text($b[0]);
                if (!$code) {
                    $code = '&nbsp;';
                }

                $html .= " <td class='fscode' style='vertical-align:middle' nowrap>$code</td>\n";
                $html .= " <td colspan='3' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
            } else {
                $html .= " <td colspan='2' class='fscode' style='vertical-align:middle' nowrap>" . text($b[0]) . '/' . text($b[1]) . "</td>\n";
                $html .= " <td colspan='2' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
            }

            $html .= " </tr>\n";
        }
    }

    return $ix;
}

// MAIN Body
//
// Build output to handle multiple pids and and superbill for each patient.
// This value is initially a maximum, and will be recomputed to
// distribute lines evenly among the pages.  (was 55)
$lines_per_page = 55;

$lines_in_stats = 8;

$header_height = 44; // height of page headers in points
// This tells us if patient/encounter data is to be filled in.
// 1 = single PID from popup, 2=array of PIDs for session

if (empty($_GET['fill'])) {
    $form_fill = 0;
} else {
    $form_fill = $_GET['fill'];
}

// Show based on session array or single pid?
$pid_list = array();
$apptdate_list = array();


if (!empty($_SESSION['pidList']) and $form_fill == 2) {
    $pid_list = $_SESSION['pidList'];
    // If PID list is in Session, then Appt. Date list is expected to be a parallel array
    $apptdate_list = $_SESSION['apptdateList'];
} elseif ($form_fill == 1) {
    array_push($pid_list, $pid); //get from active PID
} else {
    array_push($pid_list, ''); // empty element for blank form
}

// This file is optional. You can create it to customize how the printed
// fee sheet looks, otherwise you'll get a mirror of your actual fee sheet.
//
if (file_exists("../../custom/fee_sheet_codes.php")) {
    include_once("../../custom/fee_sheet_codes.php");
}

// TBD: Move these to globals.php, or make them user-specific.
$fontsize = 7;
$page_height = 700;

$padding = 0;

// The $SBCODES table is a simple indexed array whose values are
// strings of the form "code|text" where code may be either a billing
// code or one of the following:
//
// *H - A main heading, where "text" is its title (to be centered).
// *G - Specifies a new category, where "text" is its name.
// *B - A borderless blank row.
// *C - Ends the current column and starts a new one.
// If $SBCODES is not provided, then manufacture it from the Fee Sheet.
//
if (empty($SBCODES)) {
    $SBCODES = array();
    $last_category = '';

    // Create entries based on the fee_sheet_options table.
    $res = sqlStatement("SELECT * FROM fee_sheet_options " .
            "ORDER BY fs_category, fs_option");
    while ($row = sqlFetchArray($res)) {
        $fs_category = $row['fs_category'];
        $fs_option = $row['fs_option'];
        $fs_codes = $row['fs_codes'];
        if ($fs_category !== $last_category) {
            $last_category = $fs_category;
            $SBCODES[] = '*G|' . substr($fs_category, 1);
        }

        $SBCODES[] = " |" . substr($fs_option, 1);
    }

    // Create entries based on categories defined within the codes.
    $pres = sqlStatement("SELECT option_id, title FROM list_options " .
            "WHERE list_id = 'superbill' AND activity = 1 ORDER BY seq");
    while ($prow = sqlFetchArray($pres)) {
        $SBCODES[] = '*G|' . xl_list_label($prow['title']);
        $res = sqlStatement("SELECT code_type, code, code_text FROM codes " .
                "WHERE superbill = ? AND active = 1 " .
                "ORDER BY code_text", array($prow['option_id']));
        while ($row = sqlFetchArray($res)) {
            $SBCODES[] = $row['code'] . '|' . $row['code_text'];
        }
    }

    // Create one more group, for Products.
    if ($GLOBALS['sell_non_drug_products']) {
        $SBCODES[] = '*G|' . xl('Products');
        $tres = sqlStatement("SELECT " .
                "dt.drug_id, dt.selector, d.name, d.ndc_number " .
                "FROM drug_templates AS dt, drugs AS d WHERE " .
                "d.drug_id = dt.drug_id AND d.active = 1 " .
                "ORDER BY d.name, dt.selector, dt.drug_id");
        while ($trow = sqlFetchArray($tres)) {
            $tmp = $trow['selector'];
            if ($trow['name'] !== $trow['selector']) {
                $tmp .= ' ' . $trow['name'];
            }

            $prodcode = empty($trow['ndc_number']) ? ('(' . $trow['drug_id'] . ')') :
                    $trow['ndc_number'];
            $SBCODES[] = "$prodcode|$tmp";
        }
    }

    // Extra stuff for the labs section.
    $SBCODES[] = '*G|' . xl('Notes');
    $percol = intval((count($SBCODES) + 2) / 3);
    while (count($SBCODES) < $percol * 3) {
        $SBCODES[] = '*B|';
    }

    // Adjust lines per page to distribute lines evenly among the pages.
    $pages = intval(($percol + $lines_in_stats + $lines_per_page - 1) / $lines_per_page);
    $lines_per_page = intval(($percol + $lines_in_stats + $pages - 1) / $pages);

    // Figure out page and column breaks.
    $pages = 1;
    $lines = $percol;
    $page_start_index = 0;
    while ($lines + $lines_in_stats > $lines_per_page) {
        ++$pages;
        $lines_this_page = $lines > $lines_per_page ? $lines_per_page : $lines;
        $lines -= $lines_this_page;
        array_splice($SBCODES, $lines_this_page * 3 + $page_start_index, 0, '*C|');
        array_splice($SBCODES, $lines_this_page * 2 + $page_start_index, 0, '*C|');
        array_splice($SBCODES, $lines_this_page * 1 + $page_start_index, 0, '*C|');
        $page_start_index += $lines_this_page * 3 + 3;
    }

    array_splice($SBCODES, $lines * 2 + $page_start_index, 0, '*C|');
    array_splice($SBCODES, $lines * 1 + $page_start_index, 0, '*C|');
}

$lheight = sprintf('%d', ($page_height - $header_height) / $lines_per_page);

// Common HTML Header information

$html = "<html>
<head>";

$html .= "
<style>
body {
font-family: sans-serif;
font-weight: normal;
}
.bordertbl {
width: 100%;
border-style: solid;
border-width: 0 0 1px 1px;
border-spacing: 0;
border-collapse: collapse;
border-color: #999999;
}
td.toprow {
height: 1px;
padding: 0;
border-style: solid;
border-width: 0 0 0 0;
border-color: #999999;
}
td.fsgroup {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: bold;
font-size: " . attr($fontsize) . " pt;
background-color: #cccccc;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}
td.fshead {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: bold;
font-size: " . attr($fontsize) . "pt;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}
td.fscode {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: normal;
font-size: " . attr($fontsize) . "pt;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}

.ftitletable {
width: 100%;
height: " . attr($header_height) . "pt;
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
 font-size: 14pt;
 font-weight: bold;
}

div.pagebreak {
page-break-after: always;
height: " . attr($page_height) . "pt;
}
</style>";

$html .= "<title>" . text($frow['name'] ?? '') . "</title>" .
    Header::setupHeader(['opener', 'topdialog'], false) .
    "<script>";

$html .= "
$(function () {
 var win = top.printLogSetup ? top : opener.top;
 win.printLogSetup(document.getElementById('printbutton'));
});

// Process click on Print button.
function printlog_before_print() {
 var divstyle = document.getElementById('hideonprint').style;
 divstyle.display = 'none';
}

</script>
</head>
<body bgcolor='#ffffff'>
<form name='theform' method='post' action='printed_fee_sheet.php?fill=" . attr_url($form_fill) . "'
onsubmit='return opener.top.restoreSession()'>
<div style='text-align: center;'>";

$today = date('Y-m-d');

$alertmsg = ''; // anything here pops up in an alert box

// Get details for the primary facility.
$frow = $facilityService->getPrimaryBusinessEntity();

// If primary is not set try to old method of guessing...for backward compatibility
if (empty($frow)) {
    $frow = $facilityService->getPrimaryBusinessEntity(array("useLegacyImplementation" => true));
}

// Still missing...
if (empty($frow)) {
    $alertmsg = xl("No Primary Business Entity selected in facility list");
}

$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . round(9 * 5.14) . "pt' />";
} else {
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}

// Loop on array of PIDS
$saved_pages = $pages; //Save calculated page count of a single fee sheet
$loop_idx = 0; // counter for appt list

foreach ($pid_list as $pid) {
    $apptdate = $apptdate_list[$loop_idx] ?? null; // parallel array to pid_list
    $appointment = fetchAppointments($apptdate, $apptdate, $pid);  // Only expecting one row for pid
    // Set Pagebreak for multi forms
    if ($form_fill == 2) {
        $html .= "<div class=pagebreak>\n";
    } else {
        $html .= "<div>\n";
    }

    if ($form_fill) {
        // Get the patient's name and chart number.
        $patdata = getPatientData($pid);
        // Get the referring providers info
        $referDoc = getUserIDInfo($patdata['ref_providerID']);
    }

// This tracks our position in the $SBCODES array.
    $cindex = 0;

    while (--$pages >= 0) {
        $html .= genFacilityTitle(xl('Superbill/Fee Sheet'), -1, $logo);
        $html .= '<table style="width: 100%"><tr>' .
            '<td>' . xlt('Patient') . ': <span style="font-weight: bold;">' . text($patdata['fname'] ?? '') . ' ' . text($patdata['mname'] ?? '') . ' ' . text($patdata['lname'] ?? '') . '</span></td>' .
            '<td>' . xlt('DOB') . ': <span style="font-weight: bold;">' . text(oeFormatShortDate($patdata['DOB'] ?? '')) . '</span></td>' .
            '<td>' . xlt('Date of Service') . ': <span style="font-weight: bold;">' . text(oeFormatShortDate($appointment[0]['pc_eventDate'] ?? '')) . ' ' . text(oeFormatTime($appointment[0]['pc_startTime'] ?? '')) . '</span></td>' .
            '<td>' . xlt('Ref Prov') . ': <span style="font-weight: bold;">' . text($referDoc['fname'] ?? '') . ' ' . text($referDoc['lname'] ?? '') . '</span></td>' .
            '</tr></table>';
        $html .= "
<table class='bordertbl' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 1

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td colspan='3' valign='top' class='fshead' style='height:" . $lheight * 2 . "pt'>";
            $html .= xlt('Patient') . ": ";

            if ($form_fill) {
                $html .= text($patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname']) . "<br />\n";
                $html .= text($patdata['street']) . "<br />\n";
                $html .= text($patdata['city'] . ', ' . $patdata['state'] . ' ' . $patdata['postal_code']) . "\n";
            }

            $html .= "</td>
<td valign='top' class='fshead'>";
            $html .= xlt('DOB');
            $html .= ": ";

            if ($form_fill) {
                $html .= text($patdata['DOB']);
                $html .= "<br />";
            }

            $html .= xlt('ID');
            $html .= ": ";

            if ($form_fill) {
                $html .= text($patdata['pubpid']);
            }

            $html .= "</td>
</tr>
<tr>
<td colspan='3' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Provider');
            $html .= ": ";

            $encdata = false;
            if ($form_fill && $encounter) {
                $query = "SELECT fe.reason, fe.date, u.fname, u.mname, u.lname, u.username " .
                        "FROM forms AS f " .
                        "JOIN form_encounter AS fe ON fe.id = f.form_id " .
                        "LEFT JOIN users AS u ON u.username = f.user " .
                        "WHERE f.pid = ? AND f.encounter = ? AND f.formdir = 'newpatient' AND f.deleted = 0 " .
                        "ORDER BY f.id LIMIT 1";
                $encdata = sqlQuery($query, array($pid, $encounter));
                if (!empty($encdata['username'])) {
                    $html .= $encdata['fname'] . ' ' . $encdata['mname'] . ' ' . $encdata['lname'];
                }
            }

            $html .= "</td>
<td valign='top' class='fshead'>";
            $html .= xlt('Reason');
            $html .= ":<br />";

            if (!empty($encdata)) {
                $html .= text($encdata['reason']);
            }

            // Note: You would think that pc_comments would have the Appt. comments,
            // but it is actually stored in pc_hometext in DB table (openemr_postcalendar_events).
            $html .= $appointment['pc_hometext'] ?? '';

            $html .= "</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";

            if (empty($GLOBALS['ippf_specific'])) {
                $html .= xlt('Insurance') . ":";
                if ($form_fill) {
                    foreach (array('primary', 'secondary', 'tertiary') as $instype) {
                        $query = "SELECT * FROM insurance_data WHERE " .
                                "pid = ? AND type = ? " .
                                "ORDER BY date DESC LIMIT 1";
                        $row = sqlQuery($query, array($pid, $instype));
                        if (!empty($row['provider'])) {
                            $icobj = new InsuranceCompany($row['provider']);
                            $adobj = $icobj->get_address();
                            $insco_name = trim($icobj->get_name());
                            if ($instype != 'primary') {
                                $html .= ",";
                            }

                            if ($insco_name) {
                                $html .= "&nbsp;" . text($insco_name);
                            } else {
                                $html .= "&nbsp;<font color='red'><b>Missing Name</b></font>";
                            }
                        }
                    }
                }
            } else {
                // IPPF wants a visit date box with the current date in it.
                $html .= xlt('Visit date');
                $html .= ":<br />\n";
                if (!empty($encdata)) {
                    $html .= text(substr($encdata['date'], 0, 10));
                } else {
                    $html .= text(oeFormatShortDate(date('Y-m-d'))) . "\n";
                }
            }

            $html .= "</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Prior Visit');
            $html .= ":<br />
</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Today\'s Charges');
            $html .= ":<br />
</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Today\'s Balance');
            $html .= ":<br />
</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Notes');
            $html .= ":<br />
</td>
</tr>";
        } // end if last page

        $html .= "</table>
</td>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 2

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td colspan='4' valign='top' class='fshead' style='height:" . $lheight * 8 . "pt'>";
            $html .= xlt('Notes');
            $html .= ":<br />
</td>
</tr>";
        } // end if last page

        $html .= "</table>
</td>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 3

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td valign='top' colspan='4' class='fshead' style='height:" . $lheight * 6 . "pt;border-width:0 1px 0 0'>
&nbsp;
</td>
</tr>
<tr>
<td valign='top' colspan='4' class='fshead' style='height:" . $lheight * 2 . "pt'>";
            $html .= xlt('Signature');
            $html .= ":<br />
</td>
</tr>";
        } // end if last page

        $html .= "</table>
</td>
</tr>

</table>";

        $html .= "</div>";  // end of div.pageLetter
    } // end while
    $pages = $saved_pages; // reset
    $loop_idx++; // appt list counter
} // end foreach

// Common End Code
if ($form_fill != 2) {   //use native browser 'print' for multipage
    $html .= "<div id='hideonprint'>
<p>
<input type='button' class='btn btn-secondary btn-print mt-3' value='";

    $html .= xla('Print');
    $html .= "' id='printbutton' />
</div>";
}

$html .= "
</div>
</form>
</body>
</html>";

// Send final result to display
echo $html;
