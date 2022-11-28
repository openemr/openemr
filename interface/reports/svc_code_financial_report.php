<?php

/**
 * This is a report of Financial Summary by Service Code.
 *
 * This is a summary of service code charge/pay/adjust and balance,
 * with the ability to pick "important" codes to either highlight or
 * limit to list to. Important codes can be configured in
 * Administration->Service section by assigning code with
 * 'Service Reporting'.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Visolve
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Financial Summary by Service Code")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$grand_total_units  = 0;
$grand_total_amt_billed  = 0;
$grand_total_amt_paid  = 0;
$grand_total_amt_adjustment  = 0;
$grand_total_amt_balance  = 0;

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'] ?? null;
$form_provider  = $_POST['form_provider'] ?? null;

if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=svc_financial_report_" . attr($form_from_date) . "--" . attr($form_to_date) . ".csv");
    header("Content-Description: File Transfer");
    // CSV headers:
} else { // end export
    ?>
<html>
<head>
    <title><?php echo xlt('Financial Summary by Service Code') ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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

    <script>
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
    </script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">
<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Financial Summary by Service Code'); ?></span>
<form method='post' action='svc_code_financial_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
<tr>
<td width='70%'>
  <div style='float:left'>
  <table class='text'>
      <tr>
          <td class='col-form-label'>
            <?php echo xlt('Facility'); ?>:
          </td>
          <td>
        <?php dropdown_facility($form_facility, 'form_facility', true); ?>
          </td>
                    <td class='col-form-label'><?php echo xlt('Provider'); ?>:</td>
            <td><?php
                    // Build a drop-down list of providers.
                            //
                            $query = "SELECT id, lname, fname FROM users WHERE " .
                              "authorized = 1 ORDER BY lname, fname"; //(CHEMED) facility filter
                            $ures = sqlStatement($query);
                            echo "   <select name='form_provider' class='form-control'>\n";
                            echo "    <option value=''>-- " . xlt('All') . " --\n";
            while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='" . attr($provid) . "'";
                if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                    echo " selected";
                }

                echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
            }

                            echo "   </select>\n";
            ?>
                </td>
        </tr><tr>
                 <td class='col-form-label'>
                            <?php echo xlt('From'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;
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
                        <td>
                          <div class="checkbox">
                           <label><input type='checkbox' name='form_details'<?php
                            if (!empty($_POST['form_details'])) {
                                    echo ' checked';
                            } ?>>
                            <?php echo xlt('Important Codes'); ?></label>
                          </div>
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
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('CSV Export'); ?>
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
}

   // end not export

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    $rows = array();
    $from_date = $form_from_date;
    $to_date   = $form_to_date;
    $sqlBindArray = array();
    $query = "select b.code,sum(b.units) as units,sum(b.fee) as billed,sum(ar_act.paid) as PaidAmount, " .
    "sum(ar_act.adjust) as AdjustAmount,(sum(b.fee)-(sum(ar_act.paid)+sum(ar_act.adjust))) as Balance, " .
    "c.financial_reporting " .
    "FROM form_encounter as fe " .
    "JOIN billing as b on b.pid=fe.pid and b.encounter=fe.encounter " .
    "JOIN (select pid, encounter, code, sum(pay_amount) as paid, sum(adj_amount) as adjust " .
    "from ar_activity WHERE deleted IS NULL group by pid, encounter, code) as ar_act " .
    "ON ar_act.pid=b.pid and ar_act.encounter=b.encounter and ar_act.code=b.code " .
    "LEFT OUTER JOIN codes AS c ON c.code = b.code " .
    "INNER JOIN code_types AS ct ON ct.ct_key = b.code_type AND ct.ct_fee = '1' " .
    "WHERE b.code_type != 'COPAY' AND b.activity = 1 /* AND b.fee != 0 */ AND " .
    "fe.date >=  ? AND fe.date <= ?";
    array_push($sqlBindArray, "$from_date 00:00:00", "$to_date 23:59:59");
    // If a facility was specified.
    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    // If a provider was specified.
    if ($form_provider) {
        $query .= " AND b.provider_id = ?";
        array_push($sqlBindArray, $form_provider);
    }

    // If selected important codes
    if (!empty($_POST['form_details'])) {
        $query .= " AND c.financial_reporting = '1'";
    }

    $query .= " GROUP BY b.code ORDER BY b.code, fe.date, fe.id ";
    $res = sqlStatement($query, $sqlBindArray);
    $grand_total_units  = 0;
    $grand_total_amt_billed  = 0;
    $grand_total_amt_paid  = 0;
    $grand_total_amt_adjustment  = 0;
    $grand_total_amt_balance  = 0;

    while ($erow = sqlFetchArray($res)) {
        $row = array();
        $row['pid'] = $erow['pid'] ?? null;
        $row['provider_id'] = $erow['provider_id'] ?? null;
        $row['Procedure codes'] = $erow['code'];
        $row['Units'] = $erow['units'];
        $row['Amt Billed'] = $erow['billed'];
        $row['Paid Amt'] = $erow['PaidAmount'];
        $row['Adjustment Amt'] = $erow['AdjustAmount'];
        $row['Balance Amt'] = $erow['Balance'];
        $row['financial_reporting'] = $erow['financial_reporting'];
        $rows[($erow['pid'] ?? null) . '|' . $erow['code'] . '|' . $erow['units']] = $row;
    }

    if ($_POST['form_csvexport']) {
      // CSV headers:
        if (true) {
            echo csvEscape("Procedure codes") . ',';
            echo csvEscape("Units") . ',';
            echo csvEscape("Amt Billed") . ',';
            echo csvEscape("Paid Amt") . ',';
            echo csvEscape("Adjustment Amt") . ',';
            echo csvEscape("Balance Amt") . "\n";
        }
    } else {
        ?>
<div id="report_results">
<table class='table' id='mymaintable'>
<thead class='thead-light'>
<th>
        <?php echo xlt('Procedure Codes'); ?>
</th>
<th >
        <?php echo xlt('Units'); ?>
</th>
<th>
        <?php echo xlt('Amt Billed'); ?>
</th>
<th>
        <?php echo xlt('Paid Amt'); ?>
</th>
<th >
        <?php echo xlt('Adjustment Amt'); ?>
</th>
<th >
        <?php echo xlt('Balance Amt'); ?>
</th>
</thead>
        <?php
    }

            $orow = -1;

    foreach ($rows as $key => $row) {
        $print = '';
        $csv = '';

        if ($row['financial_reporting']) {
            $bgcolor = "#FFFFDD";
        } else {
            $bgcolor = "#FFDDDD";
        }

        $print = "<tr bgcolor='" . attr($bgcolor) . "'><td class='detail'>" . text($row['Procedure codes']) . "</td><td class='detail'>" . text($row['Units']) . "</td><td class='detail'>" . text(oeFormatMoney($row['Amt Billed'])) . "</td><td class='detail'>" . text(oeFormatMoney($row['Paid Amt'])) . "</td><td class='detail'>" . text(oeFormatMoney($row['Adjustment Amt'])) . "</td><td class='detail'>" . text(oeFormatMoney($row['Balance Amt'])) . "</td>";

        $csv = csvEscape($row['Procedure codes']) . ',' . csvEscape($row['Units']) . ',' . csvEscape(oeFormatMoney($row['Amt Billed'])) . ',' . csvEscape(oeFormatMoney($row['Paid Amt'])) . ',' . csvEscape(oeFormatMoney($row['Adjustment Amt'])) . ',' . csvEscape(oeFormatMoney($row['Balance Amt'])) . "\n";

        $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
                       $grand_total_units  += $row['Units'];
                                       $grand_total_amt_billed  += $row['Amt Billed'];
                                       $grand_total_amt_paid  += $row['Paid Amt'];
                                       $grand_total_amt_adjustment  += $row['Adjustment Amt'];
                                       $grand_total_amt_balance  += $row['Balance Amt'];

        if ($_POST['form_csvexport']) {
            echo $csv;
        } else {
            echo $print;
        }
    }

    if (!$_POST['form_csvexport']) {
        echo "<tr class='bg-white'>\n";
        echo " <td class='detail'>" . xlt("Grand Total") . "</td>\n";
        echo " <td class='detail'>" . text($grand_total_units) . "</td>\n";
        echo " <td class='detail'>" .
        text(oeFormatMoney($grand_total_amt_billed)) . "</td>\n";
        echo " <td class='detail'>" .
        text(oeFormatMoney($grand_total_amt_paid)) . "</td>\n";
        echo " <td class='detail'>" .
        text(oeFormatMoney($grand_total_amt_adjustment)) . "</td>\n";
        echo " <td class='detail'>" .
        text(oeFormatMoney($grand_total_amt_balance)) . "</td>\n";
        echo " </tr>\n";
        ?>
     </table>    </div>
                <?php
    }
}

if (empty($_POST['form_csvexport'])) {
    if (!empty($_POST['form_refresh']) && empty($print)) {
        echo "<span style='font-size:10pt;'>";
           echo xlt('No matches found. Try search again.');
           echo "</span>";
        echo '<script>document.getElementById("report_results").style.display="none";</script>';
    }

    if (empty($_POST['form_refresh']) && empty($_POST['form_csvexport'])) { ?>
        <div class='text'>
        <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
        </div><?php
    } ?>
</form>
</body>

</html>
    <?php
} // End not csv export
?>
