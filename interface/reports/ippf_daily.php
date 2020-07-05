<?php

/**
 * This module creates the Barbados Daily Record.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Might want something different here.
//
if (! AclMain::aclCheckCore('acct', 'rep')) {
    die("Unauthorized access.");
}

$facilityService = new FacilityService();

$from_date     = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');

$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

$report_title = xl('Clinic Daily Record');
$report_col_count = 12;

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

$cellcount = 0;

function genStartRow($att)
{
    global $cellcount, $form_output;
    if ($form_output != 3) {
        echo " <tr $att>\n";
    }

    $cellcount = 0;
}

function genEndRow()
{
    global $form_output;
    if ($form_output == 3) {
        echo "\n";
    } else {
        echo " </tr>\n";
    }
}

// Usually this generates one cell, but allows for two or more.
//
function genAnyCell($data, $right = false, $class = '')
{
    global $cellcount, $form_output;
    if (!is_array($data)) {
        $data = array(0 => $data);
    }

    foreach ($data as $datum) {
        if ($form_output == 3) {
            if ($cellcount) {
                echo ',';
            }

            echo '"' . $datum . '"';
        } else {
            echo "  <td";
            if ($class) {
                echo " class='" . attr($class) . "'";
            }

            if ($right) {
                echo " align='right'";
            }

            echo ">" . text($datum) . "</td>\n";
        }

        ++$cellcount;
    }
}

function genHeadCell($data, $right = false)
{
    genAnyCell($data, $right, 'dehead');
}

// Create an HTML table cell containing a numeric value, and track totals.
//
function genNumCell($num, $cnum)
{
    global $atotals, $form_output;
    $atotals[$cnum] += $num;
    if (empty($num) && $form_output != 3) {
        $num = '&nbsp;';
    }

    genAnyCell($num, true, 'detail');
}

// If we are doing the CSV export then generate the needed HTTP headers.
// Otherwise generate HTML.
//
if ($form_output == 3) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=service_statistics_report.csv");
    header("Content-Description: File Transfer");
} else { // not export
    ?>
<html>
<head>
<title><?php echo text($report_title); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

<style>
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php echo text($report_title); ?></h2>

<form name='theform' method='post' action='ippf_daily.php?t=<?php echo attr_url($report_type); ?>' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table border='0' cellspacing='5' cellpadding='1'>
 <tr>
  <td valign='top' class='detail' nowrap>
    <?php echo xlt('Facility'); ?>:
  </td>
  <td valign='top' class='detail'>
    <?php
 // Build a drop-down list of facilities.
 //
    $fres = $facilityService->getAllFacility();
    echo "   <select name='form_facility'>\n";
    echo "    <option value=''>-- All Facilities --\n";
    foreach ($fres as $frow) {
        $facid = $frow['id'];
        echo "    <option value='" . attr($facid) . "'";
        if ($facid == $_POST['form_facility']) {
            echo " selected";
        }

        echo ">" . text($frow['name']) . "\n";
    }

    echo "   </select>\n";
    ?>
  </td>
  <td colspan='2' class='detail' nowrap>
    <?php echo xlt('Date'); ?>
   <input type='text' class='datepicker' name='form_from_date' id='form_from_date' size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>' />
  </td>
  <td valign='top' class='dehead' nowrap>
    <?php echo xlt('To{{Destination}}'); ?>:
  </td>
  <td colspan='3' valign='top' class='detail' nowrap>
    <?php
    foreach (array(1 => 'Screen', 2 => 'Printer', 3 => 'Export File') as $key => $value) {
        echo "   <input type='radio' name='form_output' value='" . attr($key) . "'";
        if ($key == $form_output) {
            echo ' checked';
        }

        echo " />" . text($value) . " &nbsp;";
    }
    ?>
  </td>
  <td align='right' valign='top' class='detail' nowrap>
   <input type='submit' name='form_submit' value='<?php echo xla('Submit'); ?>'
    title='<?php echo xla('Click to generate the report'); ?>' />
  </td>
 </tr>
 <tr>
  <td colspan='5' height="1">
  </td>
 </tr>
</table>
    <?php
} // end not export

if ($_POST['form_submit']) {
    $lores = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'contrameth' AND activity = 1 ORDER BY title");
    while ($lorow = sqlFetchArray($lores)) {
        $areport[$lorow['option_id']] = array($lorow['title'],
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    }

    $areport['zzz'] = array('Unknown', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

    $sqlBindArray = array();

    // This gets us all MA codes, with encounter and patient
    // info attached and grouped by patient and encounter.
    $query = "SELECT " .
    "fe.pid, fe.encounter, fe.date AS encdate, fe.pc_catid, " .
    "pd.regdate, b.code_type, b.code " .
    "FROM form_encounter AS fe " .
    "JOIN patient_data AS pd ON pd.pid = fe.pid " .
    "LEFT JOIN billing AS b ON " .
    "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
    "AND b.code_type = 'MA' " .
    "WHERE fe.date >= ? AND " .
    "fe.date <= ? ";
    array_push($sqlBindArray, $from_date . ' 00:00:00', $from_date . ' 23:59:59');

    if ($form_facility) {
        $query .= "AND fe.facility_id = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= "ORDER BY fe.pid, fe.encounter, b.code";
    $res = sqlStatement($query, $sqlBindArray);

    $last_pid = '0';
    $last_contra_pid = '0';
    $last_encounter = '0';
    $method = '';

    while ($row = sqlFetchArray($res)) {
        if ($row['code_type'] === 'MA') {
            // Logic for individual patients.
            //
            if ($row['pid'] != $last_pid) { // new patient
                $last_pid = $row['pid'];

                $crow = sqlQuery("SELECT lc.new_method " .
                "FROM lists AS l, lists_ippf_con AS lc WHERE " .
                "l.pid = ? AND l.begdate <= ? AND " .
                "( l.enddate IS NULL OR l.enddate > ? ) AND " .
                "l.activity = 1 AND l.type = 'contraceptive' AND lc.id = l.id " .
                "ORDER BY l.begdate DESC LIMIT 1", array($last_pid, $from_date, $from_date));
                $amethods = explode('|', empty($crow) ? 'zzz' : $crow['new_method']);

                // TBD: We probably want to select the method with highest CYP here,
                // but for now we'll settle for the first one that appears.
                $method = $amethods[0];

                if (empty($areport[$method])) {
                        // This should not happen.
                        $areport[$method] = array("Unlisted method '$method'",
                          0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                }

                // Count total clients.
                ++$areport[$method][3];

                // Count as new or old client.
                if ($row['regdate'] == $from_date) {
                          ++$areport[$method][1];
                } else {
                          ++$areport[$method][2];
                }

                /*************************************************************
              // Maybe count as old Client First Visit this year.
              $regyear = substr($row['regdate'], 0, 4);
              $thisyear = substr($from_date, 0, 4);
              if ($regyear && $regyear < $thisyear) {
                $trow = sqlQuery("SELECT count(*) AS count FROM form_encounter " .
                  "WHERE date >= '$thisyear-01-01 00:00:00' AND " .
                  "date < '" . $row['encdate'] . " 00:00:00'");
                if (empty($trow['count'])) ++$areport[$method][5];
              }
                *************************************************************/
            } // end new patient

            // Logic for visits.
            //
            if ($row['encounter'] != $last_encounter) { // new visit
                $last_encounter = $row['encounter'];

                // Count unique clients coming for supply or re-supply.
                if ($row['pc_catid'] == '10' && $last_pid != $last_contra_pid) {
                    $last_contra_pid = $last_pid;
                    ++$areport[$method][4];
                }
            }

            // Logic for specific services.
            //
            $code = 0 + $row['code'];
            if ($code == 255004) {
                ++$areport[$method][5];  // pap smear
            }

            if ($code == 256101) {
                ++$areport[$method][6];  // preg test
            }

            if ($code == 375008) {
                ++$areport[$method][7];  // dr's check
            }

            if ($code == 375015) {
                ++$areport[$method][8];  // dr's visit (was 375014)
            }

            if ($code == 375011) {
                ++$areport[$method][9];  // advice
            }

            if ($code == 19916) {
                ++$areport[$method][10]; // couns by method
            }

            if ($code == 39916) {
                ++$areport[$method][11]; // infert couns
            }

            if ($code == 19911) {
                ++$areport[$method][12]; // std/aids couns
            }
        }
    } // end while

    if ($form_output != 3) {
        echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

  // Generate headings.
    genStartRow("bgcolor='#dddddd'");
    genHeadCell(xl('Method'));
    genHeadCell(xl('New Clients'), true);
    genHeadCell(xl('Old Clients'), true);
    genHeadCell(xl('Total Clients'), true);
    genHeadCell(xl('Contra Clients'), true);
  // genHeadCell(xl('O.A.F.V.'       ), true);
    genHeadCell(xl('Pap Smear'), true);
    genHeadCell(xl('Preg Test'), true);
    genHeadCell(xl('Dr Check'), true);
    genHeadCell(xl('Dr Visit'), true);
    genHeadCell(xl('Advice'), true);
    genHeadCell(xl('Couns by Method'), true);
    genHeadCell(xl('Infert Couns'), true);
    genHeadCell(xl('STD/AIDS Couns'), true);
    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
        genStartRow("bgcolor='" . attr($bgcolor) . "'");
        genAnyCell($varr[0], false, 'detail');
        // Generate data and accumulate totals for this row.
        for ($cnum = 0; $cnum < $report_col_count; ++$cnum) {
            genNumCell($varr[$cnum + 1], $cnum);
        }

        genEndRow();
    } // end foreach

    if ($form_output != 3) {
        // Generate the line of totals.
        genStartRow("bgcolor='#dddddd'");
        genHeadCell(xl('Totals'));
        for ($cnum = 0; $cnum < $report_col_count; ++$cnum) {
            genHeadCell($atotals[$cnum], true);
        }

        genEndRow();
        // End of table.
        echo "</table>\n";
    }
} // end if submit

if ($form_output != 3) {
    ?>
</form>
</center>

<script>
    <?php if ($form_output == 2) { ?>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
<?php } ?>
</script>

</body>
</html>
    <?php
} // end not export
?>
