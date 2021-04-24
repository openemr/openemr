<?php

/**
 * This report lists referrals for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
    <title><?php echo xlt('Referrals'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
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

         // The OnClick handler for referral display.

        function show_referral(transid) {
            dlgopen('../patient_file/transaction/print_referral.php?transid=' + encodeURIComponent(transid),
                '_blank', 550, 400,true); // Force new window rather than iframe because of the dynamic generation of the content in print_referral.php
            return false;
        }
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
        }
    </style>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Referrals'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='referrals_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility(($form_facility), 'form_facility', true); ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'
         class='datepicker form-control'>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'
         class='datepicker form-control'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                     <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                     </a>
                        <?php if (!empty($_POST['form_refresh'])) { ?>
                       <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
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
if (!empty($_POST['form_refresh'])) {
    ?>
<div id="report_results">
<table class='table' width='98%' id='mymaintable'>
<thead class='thead-light'>
<th> <?php echo xlt('Refer To'); ?> </th>
<th> <?php echo xlt('Refer Date'); ?> </th>
<th> <?php echo xlt('Reply Date'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('ID'); ?> </th>
<th> <?php echo xlt('Reason'); ?> </th>
</thead>
<tbody>
    <?php
    if ($_POST['form_refresh']) {
        $query = "SELECT t.id, t.pid, " .
        "d1.field_value AS refer_date, " .
        "d3.field_value AS reply_date, " .
        "d4.field_value AS body, " .
        "ut.organization, uf.facility_id, p.pubpid, " .
        "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
        "CONCAT(ut.fname,' ', ut.lname) AS referer_to, " .
        "CONCAT(p.fname,' ', p.lname) AS patient_name " .
        "FROM transactions AS t " .
        "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
        "JOIN      lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
        "LEFT JOIN lbt_data AS d3 ON d3.form_id = t.id AND d3.field_id = 'reply_date' " .
        "LEFT JOIN lbt_data AS d4 ON d4.form_id = t.id AND d4.field_id = 'body' " .
        "LEFT JOIN lbt_data AS d7 ON d7.form_id = t.id AND d7.field_id = 'refer_to' " .
        "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
        "LEFT JOIN users AS ut ON ut.id = d7.field_value " .
        "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
        "WHERE t.title = 'LBTref' AND " .
        "d1.field_value >= ? AND d1.field_value <= ? " .
        "ORDER BY ut.organization, d1.field_value, t.id";
        $res = sqlStatement($query, array($form_from_date, $form_to_date));

        while ($row = sqlFetchArray($res)) {
            // If a facility is specified, ignore rows that do not match.
            if ($form_facility !== '') {
                if ($form_facility) {
                    if ($row['facility_id'] != $form_facility) {
                        continue;
                    }
                } else {
                    if (!empty($row['facility_id'])) {
                        continue;
                    }
                }
            }

            ?>
   <tr>
    <td>
            <?php
            if ($row['organization'] != null || $row['organization'] != '') {
                echo text($row['organization']);
            } else {
                echo text($row['referer_to']);
            }
            ?>
    </td>
    <td>
     <a href='#' onclick="return show_referral(<?php echo js_escape($row['id']); ?>)">
            <?php echo text(oeFormatShortDate($row['refer_date'])); ?>&nbsp;
     </a>
    </td>
    <td>
            <?php echo text(oeFormatShortDate($row['reply_date'])) ?>
    </td>
    <td>
            <?php echo text($row['patient_name']) ?>
    </td>
    <td>
            <?php echo text($row['pubpid']) ?>
    </td>
    <td>
            <?php echo text($row['body']) ?>
    </td>
   </tr>
            <?php
        }
    }
    ?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>
</form>

</body>
</html>
