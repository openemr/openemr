<?php

/**
 * This report lists patients that were seen within a given date
 * range, or all patients if no date range is entered.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$from_date  = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$to_date    = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

$form_provider = empty($_POST['form_provider']) ? 0 : intval($_POST['form_provider']);

// In the case of CSV export only, a download will be forced.
if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list.csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

<title><?php echo xlt('Patient List'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<script>

$(function () {
    oeFixedHeaderSetup(document.getElementById('mymaintable'));
    top.printLogSetup(document.getElementById('printbutton'));

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

<style>

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
        margin-bottom: 10px;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
    #report_results {
        width: 100%;
    }
}

</style>

</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient List'); ?></span>

<div id="report_parameters_daterange">
    <?php if (!(empty($to_date) && empty($from_date))) { ?>
        <?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
<?php } ?>
</div>

<form name='theform' id='theform' method='post' action='patient_list.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='60%'>
    <div style='float:left'>

    <table class='text'>
        <tr>
      <td class='col-form-label'>
        <?php echo xlt('Provider'); ?>:
      </td>
      <td>
            <?php
            generate_form_field(array('data_type' => 10, 'field_id' => 'provider', 'empty_title' => '-- All --'), ($_POST['form_provider'] ?? ''));
            ?>
      </td>
            <td class='col-form-label'>
                <?php echo xlt('Visits From'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class="h-100" align='left' valign='middle'>
    <table class="w-100 h-100" style='border-left: 1px solid;'>
        <tr>
            <td>
        <div class="text-center">
                  <div class="btn-group" role="group">
                    <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                    <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Export to CSV'); ?>
                    </a>
                      <a href='#' id='printbutton' class='btn btn-secondary btn-print'>
                            <?php echo xlt('Print'); ?>
                      </a>
                    <?php } ?>
              </div>
        </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

    <?php
} // end not form_csvexport

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    if ($_POST['form_csvexport']) {
        // CSV headers:
        echo csvEscape(xl('Last Visit')) . ',';
        echo csvEscape(xl('First{{Name}}')) . ',';
        echo csvEscape(xl('Last{{Name}}')) . ',';
        echo csvEscape(xl('Middle{{Name}}')) . ',';
        echo csvEscape(xl('ID')) . ',';
        echo csvEscape(xl('Street')) . ',';
        echo csvEscape(xl('City')) . ',';
        echo csvEscape(xl('State')) . ',';
        echo csvEscape(xl('Zip')) . ',';
        echo csvEscape(xl('Home Phone')) . ',';
        echo csvEscape(xl('Work Phone')) . "\n";
    } else {
        ?>

  <div id="report_results">
  <table class='table' id='mymaintable'>
   <thead class='thead-light'>
    <th> <?php echo xlt('Last Visit'); ?> </th>
    <th> <?php echo xlt('Patient'); ?> </th>
    <th> <?php echo xlt('ID'); ?> </th>
    <th> <?php echo xlt('Street'); ?> </th>
    <th> <?php echo xlt('City'); ?> </th>
    <th> <?php echo xlt('State'); ?> </th>
    <th> <?php echo xlt('Zip'); ?> </th>
    <th> <?php echo xlt('Home Phone'); ?> </th>
    <th> <?php echo xlt('Work Phone'); ?> </th>
 </thead>
 <tbody>
        <?php
    } // end not export
    $totalpts = 0;
    $sqlArrayBind = array();
    $query = "SELECT " .
    "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
    "p.postal_code, p.phone_home, p.phone_biz, p.pid, p.pubpid, " .
    "count(e.date) AS ecount, max(e.date) AS edate, " .
    "i1.date AS idate1, i2.date AS idate2, " .
    "c1.name AS cname1, c2.name AS cname2 " .
    "FROM patient_data AS p ";
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON " .
        "e.pid = p.pid AND " .
        "e.date >= ? AND " .
        "e.date <= ? ";
        array_push($sqlArrayBind, $from_date . ' 00:00:00', $to_date . ' 23:59:59');
        if ($form_provider) {
            $query .= "AND e.provider_id = ? ";
            array_push($sqlArrayBind, $form_provider);
        }
    } else {
        if ($form_provider) {
            $query .= "JOIN form_encounter AS e ON " .
            "e.pid = p.pid AND e.provider_id = ? ";
            array_push($sqlArrayBind, $form_provider);
        } else {
            $query .= "LEFT OUTER JOIN form_encounter AS e ON " .
            "e.pid = p.pid ";
        }
    }

    $query .=
    "LEFT OUTER JOIN insurance_data AS i1 ON " .
    "i1.pid = p.pid AND i1.type = 'primary' " .
    "LEFT OUTER JOIN insurance_companies AS c1 ON " .
    "c1.id = i1.provider " .
    "LEFT OUTER JOIN insurance_data AS i2 ON " .
    "i2.pid = p.pid AND i2.type = 'secondary' " .
    "LEFT OUTER JOIN insurance_companies AS c2 ON " .
    "c2.id = i2.provider " .
    "GROUP BY p.lname, p.fname, p.mname, p.pid, i1.date, i2.date " .
    "ORDER BY p.lname, p.fname, p.mname, p.pid, i1.date DESC, i2.date DESC";
    $res = sqlStatement($query, $sqlArrayBind);

    $prevpid = 0;
    while ($row = sqlFetchArray($res)) {
        if ($row['pid'] == $prevpid) {
            continue;
        }

        $prevpid = $row['pid'];
        $age = '';
        if (!empty($row['DOB'])) {
            $dob = $row['DOB'];
            $tdy = $row['edate'] ? $row['edate'] : date('Y-m-d');
            $ageInMonths = (substr($tdy, 0, 4) * 12) + substr($tdy, 5, 2) -
                   (substr($dob, 0, 4) * 12) - substr($dob, 5, 2);
            $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
            if ($dayDiff < 0) {
                --$ageInMonths;
            }

            $age = intval($ageInMonths / 12);
        }

        if ($_POST['form_csvexport']) {
            echo csvEscape(oeFormatShortDate(substr($row['edate'], 0, 10))) . ',';
            echo csvEscape($row['lname']) . ',';
            echo csvEscape($row['fname']) . ',';
            echo csvEscape($row['mname']) . ',';
            echo csvEscape($row['pubpid']) . ',';
            echo csvEscape(xl($row['street'])) . ',';
            echo csvEscape(xl($row['city'])) . ',';
            echo csvEscape(xl($row['state'])) . ',';
            echo csvEscape($row['postal_code']) . ',';
            echo csvEscape($row['phone_home']) . ',';
            echo csvEscape($row['phone_biz']) . "\n";
        } else {
            ?>
       <tr>
        <td>
            <?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?>
   </td>
   <td>
            <?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?>
   </td>
   <td>
            <?php echo text($row['pubpid']); ?>
   </td>
   <td>
            <?php echo xlt($row['street']); ?>
   </td>
   <td>
            <?php echo xlt($row['city']); ?>
   </td>
   <td>
            <?php echo xlt($row['state']); ?>
   </td>
   <td>
            <?php echo text($row['postal_code']); ?>
   </td>
   <td>
            <?php echo text($row['phone_home']); ?>
   </td>
   <td>
            <?php echo text($row['phone_biz']); ?>
   </td>
  </tr>
            <?php
        } // end not export
        ++$totalpts;
    } // end while
    if (!$_POST['form_csvexport']) {
        ?>

   <tr class="report_totals">
    <td colspan='9'>
        <?php echo xlt('Total Number of Patients'); ?>
   :
        <?php echo text($totalpts); ?>
  </td>
 </tr>

</tbody>
</table>
</div> <!-- end of results -->
        <?php
    } // end not export
} // end if refresh or export

if (empty($_POST['form_refresh']) && empty($_POST['form_csvexport'])) {
    ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
    <?php
}

if (empty($_POST['form_csvexport'])) {
    ?>

</form>
</body>

</html>
    <?php
} // end not export
?>
