<?php

/**
 * This module creates statistical reports related to lab tests and
 * other procedure orders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../custom/code_types.inc.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'lab')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Procedure Statistics Report")]);
    exit;
}

$from_date     = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : '0000-00-00';
$to_date       = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_by       = $_POST['form_by'] ?? null;     // this is a scalar
$form_show     = $_POST['form_show'] ?? null;   // this is an array
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_sexes    = isset($_POST['form_sexes']) ? $_POST['form_sexes'] : '3';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

if (empty($form_by)) {
    $form_by = '4';
}

if (empty($form_show)) {
    $form_show = array('1');
}

// One of these is chosen as the left column, or Y-axis, of the report.
//
$report_title = xl('Procedure Statistics Report');
$arr_by = array(
  4  => xl('Specific Result'),
  5  => xl('Followups Indicated'),
);

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

$arr_show   = array(
  // '.total' => array('title' => 'Total Positives'),
  '.tneg'  => array('title' => 'Total Negatives'),
  '.age'   => array('title' => 'Age Category'),
); // info about selectable columns

$arr_titles = array(); // will contain column headers

// Query layout_options table to generate the $arr_show table.
// Table key is the field ID.
$lres = sqlStatement("SELECT field_id, title, data_type, list_id, description " .
  "FROM layout_options WHERE " .
  "form_id = 'DEM' AND uor > 0 AND field_id NOT LIKE 'em%' " .
  "ORDER BY group_id, seq, title");
while ($lrow = sqlFetchArray($lres)) {
    $fid = $lrow['field_id'];
    if ($fid == 'fname' || $fid == 'mname' || $fid == 'lname') {
        continue;
    }

    $arr_show[$fid] = $lrow;
    $arr_titles[$fid] = array();
}

// Compute age in years given a DOB and "as of" date.
//
function getAge($dob, $asof = '')
{
    if (empty($asof)) {
        $asof = date('Y-m-d');
    }

    $a1 = explode('-', substr($dob, 0, 10));
    $a2 = explode('-', substr($asof, 0, 10));
    $age = $a2[0] - $a1[0];
    if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
        --$age;
    }

  // echo "<!-- $dob $asof $age -->\n"; // debugging
    return $age;
}

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

function getListTitle($list, $option)
{
    $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = ? AND option_id = ? AND activity = 1", array($list, $option));
    if (empty($row['title'])) {
        return $option;
    }

    return $row['title'];
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

            echo '"' . attr($datum) . '"';
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

// Helper function called after the reporting key is determined for a row.
//
function loadColumnData($key, $row)
{
    global $areport, $arr_titles, $arr_show;

  // If no result, do nothing.
    if (empty($row['abnormal'])) {
        return;
    }

  // If first instance of this key, initialize its arrays.
    if (empty($areport[$key])) {
        $areport[$key] = array();
        $areport[$key]['.prp'] = 0;       // previous pid
        $areport[$key]['.wom'] = 0;       // number of positive results for women
        $areport[$key]['.men'] = 0;       // number of positive results for men
        $areport[$key]['.neg'] = 0;       // number of negative results
        $areport[$key]['.age'] = array(0,0,0,0,0,0,0,0,0); // age array
        foreach ($arr_show as $askey => $dummy) {
            if (substr($askey, 0, 1) == '.') {
                continue;
            }

            $areport[$key][$askey] = array();
        }
    }

  // Flag this patient as having been encountered for this report row.
    $areport[$key]['.prp'] = $row['pid'];

  // Collect abnormal results only, except for a column of total negatives.
    if ($row['abnormal'] == 'no') {
        ++$areport[$key]['.neg'];
        return;
    }

  // Increment the correct sex category.
    if (strcasecmp($row['sex'], 'Male') == 0) {
        ++$areport[$key]['.men'];
    } else {
        ++$areport[$key]['.wom'];
    }

  // Increment the correct age category.
    $age = getAge($row['DOB'], $row['date_ordered']);
    $i = min(intval(($age - 5) / 5), 8);
    if ($age < 11) {
        $i = 0;
    }

    ++$areport[$key]['.age'][$i];

  // For each patient attribute to report, this increments the array item
  // whose key is the attribute's value.  This works well for list-based
  // attributes.  A key of "Unspecified" is used where the attribute has
  // no assigned value.
    foreach ($arr_show as $askey => $dummy) {
        if (substr($askey, 0, 1) == '.') {
            continue;
        }

        $status = empty($row[$askey]) ? 'Unspecified' : $row[$askey];
        $areport[$key][$askey][$status] += 1;
        $arr_titles[$askey][$status] += 1;
    }
}

// This is called for each row returned from the query.
//
function process_result_code($row)
{
    global $areport, $arr_titles, $form_by;

  // Specific Results.  One row for each result name.
  //
    if ($form_by === '4') {
        $key = $row['order_name'] . ' / ' . $row['result_name'];
        loadColumnData($key, $row);
    } elseif ($form_by === '5') {  // Recommended followup services.
        if (!empty($row['related_code'])) {
            $relcodes = explode(';', $row['related_code']);
            foreach ($relcodes as $codestring) {
                if ($codestring === '') {
                    continue;
                }

                // list($codetype, $code) = explode(':', $codestring);
                // if ($codetype !== 'IPPF') continue;
                $key = $codestring;
                loadColumnData($key, $row);
            }
        }
    }
} // end function process_result_code()

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
} else {
    ?>
<html>
<head>
<title><?php echo text($report_title); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'textformat', 'jquery']); ?>

<style>
.dehead    {
    font-family: sans-serif;
    font-weight: bold;
}
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

<body class='m-0'>

<center>

<h2><?php echo text($report_title); ?></h2>

<form name='theform' method='post' action='procedure_stats.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div class="col-8 col-md-8">
    <div class="row">
        <div class="col">
            <?php echo xlt('Facility'); ?>:
            <?php dropdown_facility($form_facility, 'form_facility', false); ?>
        </div>
        <div class="col">
            <?php echo xlt('From'); ?>
            <input type='text' class='form-control datepicker' name='form_from_date' id='form_from_date' size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>' />
        </div>
        <div class="col">
            <?php echo xlt('To{{Range}}'); ?>
            <input type='text' class='form-control datepicker' name='form_to_date' id='form_to_date' size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>' />
        </div>
    </div>
</div>
<br>
<div class="col-8 col-md-8">
    <div class="row">
        <div class="col">
            <?php echo xlt('Rows'); ?>:
        </div>
        <div class="col">
            <select name='form_by' class='form-control' title='Left column of report'>
                <?php
                foreach ($arr_by as $key => $value) {
                    echo "    <option value='" . attr($key) . "'";
                    if ($key == $form_by) {
                        echo " selected";
                    }

                    echo ">" . text($value) . "</option>\n";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <?php echo xlt('Sex'); ?>:
        </div>
        <div class="col">
            <select class='form-control' name='form_sexes' title='<?php echo xla('To filter by sex'); ?>'>
                <?php
                foreach (array(3 => xl('Men and Women'), 1 => xl('Women Only'), 2 => xl('Men Only')) as $key => $value) {
                    echo "       <option value='" . attr($key) . "'";
                    if ($key == $form_sexes) {
                        echo " selected";
                    }

                    echo ">" . text($value) . "</option>\n";
                }
                ?>
            </select>
        </div>
    </div>
</div>
<br>
<div class="col-8 col-md-8">
    <div class="row">
        <div class="col">
            <?php echo xlt('Columns'); ?>:
            <select class='form-control' name='form_show[]' size='4' multiple title='<?php echo xla('Hold down Ctrl to select multiple items'); ?>'>
                <?php
                foreach ($arr_show as $key => $value) {
                    $title = $value['title'];
                    if (empty($title) || $key == 'title') {
                        $title = $value['description'];
                    }

                    echo "    <option value='" . attr($key) . "'";
                    if (is_array($form_show) && in_array($key, $form_show)) {
                        echo " selected";
                    }

                    echo ">" . text($title) . "</option>\n";
                }
                ?>
            </select>
        </div>
    </div>
</div>
<br>
<div class="col-8 col-md-8">
    <div class="row">
        <div class="col">
            <?php echo xlt('To{{Destination}}'); ?>:

            <?php
            foreach (array(1 => xl('Screen'), 2 => xl('Printer'), 3 => xl('Export File')) as $key => $value) {
                echo "   <input type='radio' name='form_output' value='" . attr($key) . "'";
                if ($key == $form_output) {
                    echo ' checked';
                }

                echo " />" . text($value) . " &nbsp;";
            }
            ?>
        </div>
    </div>
</div>
<br>
<div class="col-4 col-md-4">
    <div class="col">
         <button type='submit' class='btn btn-primary btn-save' name='form_submit' value='form_submit' title='<?php echo xla('Click to generate the report'); ?>'><?php echo xla('Submit') ?></button>
    </div>
</div>
<br>

    <?php
} // end not export

if (!empty($_POST['form_submit'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $pd_fields = '';
    foreach ($arr_show as $askey => $asval) {
        if (substr($askey, 0, 1) == '.') {
            continue;
        }

        if (
            $askey == 'regdate' || $askey == 'sex' || $askey == 'DOB' ||
            $askey == 'lname' || $askey == 'fname' || $askey == 'mname' ||
            $askey == 'contrastart' || $askey == 'referral_source'
        ) {
            continue;
        }

        $pd_fields .= ', pd.' . escape_sql_column_name($askey, array('patient_data'));
    }

    $sexcond = '';
    if ($form_sexes == '1') {
        $sexcond = "AND pd.sex NOT LIKE 'Male' ";
    } elseif ($form_sexes == '2') {
        $sexcond = "AND pd.sex LIKE 'Male' ";
    }

    // This gets us all results, with encounter and patient
    // info attached and grouped by patient and encounter.

    $sqlBindArray = array();

    $query = "SELECT " .
    "po.patient_id, po.encounter_id, po.date_ordered, " .
    "po.provider_id, pd.regdate, " .
    "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
    "pd.contrastart, pd.referral_source$pd_fields, " .
    "ps.abnormal, " .
    // "pto.name AS order_name, ptr.name AS result_name, ptr.related_code " .
    "pc.procedure_name AS order_name, ptr.name AS result_name, ptr.related_code " .
    "FROM procedure_order AS po " .
    "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "JOIN patient_data AS pd ON pd.pid = po.patient_id $sexcond" .
    "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
    "JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
    "AND pr.procedure_order_seq = pc.procedure_order_seq " .
    "AND pr.date_report IS NOT NULL " .
    "JOIN procedure_result AS ps ON ps.procedure_report_id = pr.procedure_report_id " .
    "AND ps.result_status = 'final' " .
    // "JOIN procedure_type AS pto ON pto.procedure_type_id = pc.procedure_type_id " .
    "JOIN procedure_type AS ptr ON ptr.lab_id = po.lab_id AND ptr.procedure_code = ps.result_code " .
    "AND ptr.procedure_type LIKE 'res%' " .
    "WHERE po.date_ordered IS NOT NULL AND po.date_ordered >= ? " .
    "AND po.date_ordered <= ? ";

    array_push($sqlBindArray, $from_date, $to_date);

    if ($form_facility) {
        $query .= "AND fe.facility_id = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= "ORDER BY fe.pid, fe.encounter, ps.result_code"; // needed?

    $res = sqlStatement($query, $sqlBindArray);

    while ($row = sqlFetchArray($res)) {
        process_result_code($row);
    }

    // Sort everything by key for reporting.
    ksort($areport);
    foreach ($arr_titles as $atkey => $dummy) {
        ksort($arr_titles[$atkey]);
    }

    if ($form_output != 3) {
        echo "<table class='table' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

    genStartRow("bgcolor='#dddddd'");

    // genHeadCell($arr_by[$form_by]);
    // If the key is an MA or IPPF code, then add a column for its description.
    if ($form_by === '5') {
        genHeadCell(array($arr_by[$form_by], xl('Description')));
    } else {
        genHeadCell($arr_by[$form_by]);
    }

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
      // if ($value == '.total') { // Total Positives
      //   genHeadCell(xl('Total'));
      // }
        if ($value == '.tneg') { // Total Negatives
            genHeadCell(xl('Negatives'));
        } elseif ($value == '.age') { // Age
            genHeadCell(xl('0-10'), true);
            genHeadCell(xl('11-14'), true);
            genHeadCell(xl('15-19'), true);
            genHeadCell(xl('20-24'), true);
            genHeadCell(xl('25-29'), true);
            genHeadCell(xl('30-34'), true);
            genHeadCell(xl('35-39'), true);
            genHeadCell(xl('40-44'), true);
            genHeadCell(xl('45+'), true);
        } elseif (!empty($arr_show[$value]['list_id'])) {
            foreach ($arr_titles[$value] as $key => $dummy) {
                genHeadCell(getListTitle($arr_show[$value]['list_id'], $key), true);
            }
        } elseif (!empty($arr_titles[$value])) {
            foreach ($arr_titles[$value] as $key => $dummy) {
                genHeadCell($key, true);
            }
        }
    }

    if ($form_output != 3) {
        genHeadCell(xl('Positives'), true);
    }

    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

        $dispkey = $key;

      // If the key is an MA or IPPF code, then get its description.
        if ($form_by === '5') {
            list($codetype, $code) = explode(':', $key);
            $type = $code_types[$codetype]['id'];
            $dispkey = array($key, '');
            $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
            "code_type = ? AND code = ? ORDER BY id LIMIT 1", array($type, $code));
            if (!empty($crow['code_text'])) {
                $dispkey[1] = $crow['code_text'];
            }
        }

        genStartRow("bgcolor='" . attr($bgcolor) . "'");

        genAnyCell($dispkey, false, 'detail');

      // This is the column index for accumulating column totals.
        $cnum = 0;
        $totalsvcs = $areport[$key]['.wom'] + $areport[$key]['.men'];

      // Generate data for this row.
        foreach ($form_show as $value) {
            // if ($value == '.total') { // Total Positives
            //   genNumCell($totalsvcs, $cnum++);
            // }
            if ($value == '.tneg') { // Total Negatives
                genNumCell($areport[$key]['.neg'], $cnum++);
            } elseif ($value == '.age') { // Age
                for ($i = 0; $i < 9; ++$i) {
                    genNumCell($areport[$key]['.age'][$i], $cnum++);
                }
            } elseif (!empty($arr_titles[$value])) {
                foreach ($arr_titles[$value] as $title => $dummy) {
                    genNumCell($areport[$key][$value][$title], $cnum++);
                }
            }
        }

      // Write the Total column data.
        if ($form_output != 3) {
            $atotals[$cnum] += $totalsvcs;
            genAnyCell($totalsvcs, true, 'dehead');
        }

        genEndRow();
    } // end foreach

    if ($form_output != 3) {
      // Generate the line of totals.
        genStartRow("bgcolor='#dddddd'");

      // genHeadCell(xl('Totals'));
      // If the key is an MA or IPPF code, then add a column for its description.
        if ($form_by === '5') {
            genHeadCell(array(xl('Totals'), ''));
        } else {
            genHeadCell(xl('Totals'));
        }

        for ($cnum = 0; $cnum < count($atotals); ++$cnum) {
            genHeadCell($atotals[$cnum], true);
        }

        genEndRow();
      // End of table.
        echo "</table>\n";
    }
} // end of if refresh or export

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
