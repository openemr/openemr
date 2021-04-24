<?php

/**
 * This report lists patients that were seen within a given date
 * range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-12-31');

if (!empty($_POST['form_labels'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=labels.txt");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

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
   }
   #report_results {
      margin-top: 30px;
   }
}

/* specifically exclude some from the screen */
@media screen {
   #report_parameters_daterange {
       visibility: hidden;
       display: none;
   }
}
</style>
<title><?php echo xlt('Front Office Receipts'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

<script>

$(function () {
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));

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
   }
}

/* specifically exclude some from the screen */
@media screen {
   #report_parameters_daterange {
       visibility: hidden;
       display: none;
   }
}

</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Unique Seen Patients'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt("to{{Range}}") . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' method='post' action='unique_seen_patients_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_labels' id='form_labels' value=''/>

<table>
<tr>
 <td width='410px'>
   <div style='float:left'>

   <table class='text'>
       <tr>
           <td class='col-form-label'>
                <?php echo xlt('Visits From'); ?>:
           </td>
           <td>
             <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
           </td>
           <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
           </td>
           <td>
             <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
           </td>
       </tr>
   </table>

   </div>

 </td>
 <td class='h-100' align='left' valign='middle' height="100%">
   <table class='w-100 h-100' style='border-left:1px solid'>
       <tr>
           <td>
               <div class="text-center">
         <div class="btn-group" role="group">
                     <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_labels").val(""); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                     </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_labels").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Labels'); ?>
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

<div id="report_results">
<table class='table'>

<thead class='thead-light'>
<th> <?php echo xlt('Last Visit'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th align='right'> <?php echo xlt('Visits'); ?> </th>
<th align='right'> <?php echo xlt('Age'); ?> </th>
<th> <?php echo xlt('Sex'); ?> </th>
<th> <?php echo xlt('Race'); ?> </th>
<th> <?php echo xlt('Primary Insurance'); ?> </th>
<th> <?php echo xlt('Secondary Insurance'); ?> </th>
</thead>
<tbody>
    <?php
} // end not generating labels

if (!empty($_POST['form_refresh']) || !empty($_POST['form_labels'])) {
    $totalpts = 0;

    $query = "SELECT " .
    "p.pid, p.fname, p.mname, p.lname, p.DOB, p.sex, p.ethnoracial, " .
    "p.street, p.city, p.state, p.postal_code, " .
    "count(e.date) AS ecount, max(e.date) AS edate, " .
    "i1.date AS idate1, i2.date AS idate2, " .
    "c1.name AS cname1, c2.name AS cname2 " .
    "FROM patient_data AS p " .
    "JOIN form_encounter AS e ON " .
    "e.pid = p.pid AND " .
    "e.date >= ? AND " .
    "e.date <= ? " .
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
    $res = sqlStatement($query, array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59'));

    $prevpid = 0;
    while ($row = sqlFetchArray($res)) {
        if ($row['pid'] == $prevpid) {
            continue;
        }

        $prevpid = $row['pid'];

        $age = '';
        if ($row['DOB']) {
            $dob = $row['DOB'];
            $tdy = $row['edate'];
            $ageInMonths = (substr($tdy, 0, 4) * 12) + substr($tdy, 5, 2) -
                   (substr($dob, 0, 4) * 12) - substr($dob, 5, 2);
            $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
            if ($dayDiff < 0) {
                --$ageInMonths;
            }

            $age = intval($ageInMonths / 12);
        }

        if ($_POST['form_labels']) {
            echo '"' . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . '","' .
             $row['street'] . '","' . $row['city'] . '","' . $row['state'] . '","' .
             $row['postal_code'] . '"' . "\n";
        } else { // not labels
            ?>
       <tr>
        <td>
            <?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?>
   </td>
   <td>
            <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>
   </td>
   <td style="text-align:center">
            <?php echo text($row['ecount']); ?>
   </td>
   <td>
            <?php echo text($age); ?>
   </td>
   <td>
            <?php echo text($row['sex']); ?>
   </td>
   <td>
            <?php echo text($row['ethnoracial']); ?>
   </td>
   <td>
            <?php echo text($row['cname1']); ?>
   </td>
   <td>
            <?php echo text($row['cname2']); ?>
   </td>
  </tr>
            <?php
        } // end not labels
        ++$totalpts;
    }

    if (!$_POST['form_labels']) {
        ?>
   <tr class='report_totals'>
    <td colspan='2'>
        <?php echo xlt('Total Number of Patients'); ?>
  </td>
  <td style="padding-left: 20px;">
        <?php echo text($totalpts); ?>
  </td>
  <td colspan='5'>&nbsp;</td>
 </tr>

        <?php
    } // end not labels
} // end refresh or labels

if (empty($_POST['form_labels'])) {
    ?>
</tbody>
</table>
</div>
</form>
</body>

</html>
    <?php
} // end not labels
?>
