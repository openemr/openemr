<?php

/*
 * interface/billing/print_daysheet_report.php Genetating an end of day report.
 *
 * Program for Generating an End of Day report
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2014 Terry Hill <terry@lillysystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* TODO: Code Cleanup */


require_once("../globals.php");

use OpenEMR\Billing\BillingReport;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

require_once OEGlobalsBag::getInstance()->getSrcDir() . '/patient.inc.php';
require_once OEGlobalsBag::getInstance()->getSrcDir() . '/daysheet.inc.php';

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'eob', '', 'write') && !AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/eob or acct/bill: Billing Manager", xl("Billing Manager"));
}

//global variables:
if (!isset($_GET["mode"])) {
    $from_date = !isset($_GET["from_date"]) ? date("Y-m-d") : $_GET["from_date"];

    $to_date = !isset($_GET["to_date"]) ? date("Y-m-d") : $_GET["to_date"];

    $code_type = !isset($_GET["code_type"]) ? "all" : $_GET["code_type"];

    $unbilled = !isset($_GET["unbilled"]) ? "on" : $_GET["unbilled"];

    $my_authorized = !isset($_GET["authorized"]) ? "on" : $_GET["authorized"];
} else {
    $from_date = $_GET["from_date"];
    $to_date = $_GET["to_date"];
    $code_type = $_GET["code_type"];
    $unbilled = $_GET["unbilled"];
    $my_authorized = $_GET["authorized"];
}

?>

<html>
<head>

<?php Header::setupHeader(); ?>

</head>
<body topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">

<a href="javascript:window.close();" target="Main"><font class="title"><?php echo xlt('Day Sheet Report')?></font></a>
<br />

<?php
$my_authorized = $my_authorized === 'on' ? true : '%';

$unbilled = $unbilled === 'on' ? '0' : '%';

if ($code_type === 'all') {
    $code_type = '%';
}

if (!isset($_GET["mode"])) {
    $from_date = !isset($_GET["from_date"]) ? date("Y-m-d") : $_GET["from_date"];

    $to_date = !isset($_GET["to_date"]) ? date("Y-m-d") : $_GET["to_date"];

    $code_type = !isset($_GET["code_type"]) ? "all" : $_GET["code_type"];

    $unbilled = !isset($_GET["unbilled"]) ? "on" : $_GET["unbilled"];

    $my_authorized = !isset($_GET["authorized"]) ? "on" : $_GET["authorized"];
} else {
    $from_date = $_GET["from_date"];
    $to_date = $_GET["to_date"];
    $code_type = $_GET["code_type"];
    $unbilled = $_GET["unbilled"];
    $my_authorized = $_GET["authorized"];
}

$my_authorized = $my_authorized === 'on' ? true : '%';

$unbilled = $unbilled === 'on' ? '0' : '%';

if ($code_type === 'all') {
    $code_type = '%';
}

$list = [];
if (isset($_GET["mode"]) && $_GET["mode"] === 'bill') {
    BillingReport::billCodesList($list);
}

$res_count = 0;
$N = 1;
$anypats = 0;
$the_first_time = 1;
$itero = [];
$totals_only = 0;
$user = '';
$first_user = '';
/** @var array<string, array{user: string, fee: float, inspay: float, insadj: float, patadj: float, patpay: float}> */
$user_totals = [];

if ($ret = getBillsBetweendayReport($code_type)) {
// checking to see if there is any information in the array if not display a message (located after this if statement)
    $anypats = count($ret);


    $old_pid = -1;
    $first_time = 1;


// $iter has encounter information

    $all4 = array_natsort($ret, 'pid', 'fulname', 'asc');

    if (filter_input(INPUT_POST, 'end_of_day_totals_only', FILTER_VALIDATE_INT) === 1) {
        $totals_only = 1;
    }

    foreach ($all4 as $iter) {
        // Tally information by user. Legacy code capped at the first 20
        // distinct users (a per-slot $us0..$us19 accumulator); this map
        // accumulates every distinct user.
        $userVal = $iter['user'] ?? null;
        $u = is_string($userVal) ? $userVal : '';
        $feeVal = $iter['fee'] ?? null;
        $insCodeVal = $iter['ins_code'] ?? null;
        $insAdjVal = $iter['ins_adjust_dollar'] ?? null;
        $patAdjVal = $iter['pat_adjust_dollar'] ?? null;
        $patCodeVal = $iter['pat_code'] ?? null;
        $user_totals[$u] ??= [
            'user' => $u,
            'fee' => 0.0,
            'inspay' => 0.0,
            'insadj' => 0.0,
            'patadj' => 0.0,
            'patpay' => 0.0,
        ];
        $user_totals[$u]['fee']    += is_numeric($feeVal) ? (float) $feeVal : 0.0;
        $user_totals[$u]['inspay'] += is_numeric($insCodeVal) ? (float) $insCodeVal : 0.0;
        $user_totals[$u]['insadj'] += is_numeric($insAdjVal) ? (float) $insAdjVal : 0.0;
        $user_totals[$u]['patadj'] += is_numeric($patAdjVal) ? (float) $patAdjVal : 0.0;
        $user_totals[$u]['patpay'] += is_numeric($patCodeVal) ? (float) $patCodeVal : 0.0;

        if ($the_first_time == 1) {
              $user = $iter['user'];
              $first_user = $iter['user'];
              $the_first_time = 0;
        }

        if ($totals_only != 1) {
            if ($old_pid != $iter['pid'] and ($iter['code_type'] != 'payment_info')) {
               // $name has patient information
                $name = getPatientData($iter["pid"]);

               // formats the displayed text
               //
                if ($first_time) {
                     print "<table border=0><tr>\n";     // small table
                     $first_time = 0;
                }

                // Displays name
                print "<tr><td colspan=50><hr><span class=bold>" . "     " . text($name["fname"]) . " " . text($name["lname"]) . "</span><br /><br /></td></tr><tr>\n";
                //==================================

                if (in_array($iter['code_type'], ['COPAY', 'Patient Payment', 'Insurance Payment'], true)) {
                      print "<td width=40><span class=text><center><b>" . xlt("Units") . "</b></center>";
                      print "</span></td><td width=100><span class=text><center><b>" . xlt("Fee") . "</b></center>" ;
                      print "</span></td><td width=100><span class=text><center><b>" . xlt("Code") . "</b></center>" ;
                      print "</span></td><td width=100><span class=text><b>";
                      print "</span></td><td width=100><span class=text><center><b>" . xlt("User") . "</b></center>";
                      print "</span></td><td width=100><span class=small><b>";
                      print "</span></td><td width=100><span class=small><center><b>" . xlt("Post Date") . "</b></center>";
                      print "</span></td><td></tr><tr>\n";
                } else {
                    print "<td width=40><span class=text><b><center>" . xlt("Units") . "</b></center>";
                    print "</span></td><td width=100><span class=text><center><b>" . xlt("Fee") . "</b></center>";
                    print "</span></td><td width=100><span class=text><center><b>" . xlt("Code") . "</b></center>";
                    print "</span></td><td width=100><span class=text><b><center>" . xlt("Provider Id") . "</b></center>";
                    print "</span></td><td width=100><span class=text><b><center>" . xlt("User") . "</b></center>";
                    print "</span></td><td width=100><span class=small><center><b>" . xlt("Bill Date") . "</b></center>";
                    print "</span></td><td width=100><span class=small><center><b>" . xlt("Date of Service") . "</b></center>";
                    print "</span></td><td width=100><span class=small><center><b>" . xlt("Encounter") . "</b></center>";
                    print "</span></td><td></tr><tr>\n";
                }

                //Next patient
                $old_pid = $iter["pid"];
            }

            // get dollar amounts to appear on pat,ins payments and copays

            if ($iter['code_type'] != 'payment_info') {
                if (in_array($iter['code_type'], ['COPAY', 'Patient Payment', 'Insurance Payment'], true)) {
                       print "<td width=40><span class=text><center>" . "1" . "</center>" ;

                      // start fee output
                      //    [pat_code] => 0.00
                      //    [ins_code] => 0.00
                      //    [pat_adjust_dollar] => 0.00
                      //    [ins_adjust_dollar] => 0.00
                    if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                        print  "</span></td><td width=100><span class=text><center>" . text("(" . $iter['ins_adjust_dollar'] . ")") . "</center>";
                    }

                    if (($iter['ins_code']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                        print  "</span></td><td width=100><span class=text><center>" . text("(" . $iter['ins_code'] . ")") . "</center>";
                    }

                    if (($iter['code_type']) != "Patient Payment" and ($iter['code_type']) != 'Insurance Payment') {
                        print  "</span></td><td width=100><span class=text><center>" . text("(" . $iter["code"] . ")") . "</center>";
                    }

                    if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                        print  "</span></td><td width=100><span class=text><center>" . text("(" . $iter['pat_adjust_dollar'] . ")") . "</center>";
                    }

                    if (($iter['pat_code']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                        print  "</span></td><td width=100><span class=text><center>" . text("(" . $iter['pat_code'] . ")") . "</center>";
                    }

                      // end fee output

                    if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Insurance Adjustment') . "</center>";
                    }

                    if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Patient Adjustment') . "</center>";
                    }

                    if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Insurance Payment') . "</center>";
                    }

                    if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Patient Payment') . "</center>";
                    }

                    if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Insurance Credit') . "</center>";
                    }

                    if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width=250><span class=text><center>" . xlt('Patient Credit') . "</center>";
                    }

                    if ($iter['paytype'] === 'PCP') {
                        print  "</span></td><td width=250><span class=text><center>" . xlt('COPAY') . "</center>";
                    }

                    if (($iter['code_type']) != 'Insurance Payment' and ($iter['code_type']) != 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width=100><span class=text><center>" . text($iter['code_type']) . "</center>";
                    }

                      print  "</span></td><td width=100><span class=text><center>" . text($iter['provider_id']) . "</center>";
                      print  "</span></td><td width=100><span class=text><center>" . text($iter['user']) . "</center>" ;
                      print  "</span></td><td width=100><span class=text>";
                      print  "</span></td><td width=100><span class=small><center>" . text(date("Y-m-d", strtotime((string) $iter["date"]))) . "</center>";
                      print  "</span></td>\n";
                } else {
                    if (date("Y-m-d", strtotime((string) $iter['bill_date'])) === '1969-12-31') {
                        print "<td width=40><span class=text><center>" . text($iter['units']) . "</center>" ;
                        print "</span></td><td width=100><span class=text><center>" . text($iter['fee']) . "</center>";
                        if (OEGlobalsBag::getInstance()->getString('language_default') === 'English (Standard)') {
                            print "</span></td><td width=250><span class=text><center>" . text(ucwords(strtolower(substr((string) $iter['code_text'], 0, 38)))) . "</center>";
                        } else {
                            print "</span></td><td width=250><span class=text><center>" . text(substr((string) $iter['code_text'], 0, 38)) . "</center>";
                        }

                        print "</span></td><td width=100><span class=text><center>" . text($iter['provider_id']) . "</center>" ;
                        print "</span></td><td width=100><span class=text><center>" . text($iter['user']) . "</center>" ;
                        print "</span></td><td width=100><span class=text><center>" . xlt("Not Billed") . "</center>";
                        print "</span></td><td width=100><span class=small><center>" . text(date("Y-m-d", strtotime((string) $iter['date']))) . "</center>";
                        print "</span></td><td width=100><span class=small><center>" . text($iter['encounter']) . "</center>";
                        print "</span></td>\n";
                    } else {
                        if ($iter['fee'] != 0) {
                            print "<td width=40><span class=text><center>" . text($iter["units"]) . "</center>";
                            print "</span></td><td width=100><span class=text><center>" . text($iter['fee']) . "</center>";
                            if (OEGlobalsBag::getInstance()->getString('language_default') === 'English (Standard)') {
                                  print "</span></td><td width=250><span class=text><center>" . text(ucwords(strtolower(substr((string) $iter['code_text'], 0, 38)))) . "</center>";
                            } else {
                                  print "</span></td><td width=250><span class=text><center>" . text(substr((string) $iter['code_text'], 0, 38)) . "</center>";
                            }

                            print "</span></td><td width=100><span class=text><center>" . text($iter['provider_id']) . "</center>";
                            print "</span></td><td width=100><span class=text><center>" . text($iter['user']) . "</center>";
                            print "</span></td><td width=100><span class=small><center>" . text(date("Y-m-d", strtotime((string) $iter['bill_date']))) . "</center>";
                            print "</span></td><td width=100><span class=small><center>" . text(date("Y-m-d", strtotime((string) $iter['date']))) . "</center>";
                            print "</span></td><td width=100><span class=small><center>" . text($iter['encounter']) . "</center>";
                            print "</span></td>\n";
                        }
                    }
                }

                $res_count++;

                if ($res_count == $N) {
                      print "</tr><tr>\n";
                      $res_count = 0;
                }

                $itero = $iter;
            }
        }

        // end totals only
    }

// end for
}


if ($anypats == 0) {
    ?><font size = 5 ><?php echo xlt('No Data to Process')?></font><?php
}

// Filter to only users with non-zero totals, then render and accumulate
// grand totals.

$user_info = array_values(array_filter(
    $user_totals,
    static fn (array $t): bool => $t['fee'] != 0
        || $t['inspay'] != 0
        || $t['insadj'] != 0
        || $t['patadj'] != 0
        || $t['patpay'] != 0,
));

if ($totals_only == 1) {
    $query_part_day = OEGlobalsBag::getInstance()->getString('query_part_day');
    $from_date = oeFormatShortDate(substr($query_part_day, 37, 10));
    $to_date = oeFormatShortDate(substr($query_part_day, 63, 10));
    print "<br /><br />";
    ?><font size = 5 ><?php echo xlt('Totals for ') . text($from_date) . ' ' . xlt('To{{Range}}') . ' ' . text($to_date) ?></font><?php
}

$gtotal_fee = 0.0;
$gtotal_insadj = 0.0;
$gtotal_inspay = 0.0;
$gtotal_patadj = 0.0;
$gtotal_patpay = 0.0;

$userLabel = xlt('User ');
$chargesLabel = xlt('Charges');
$insadjLabel = xlt('Insurance Adj');
$inspayLabel = xlt('Insurance Payments');
$patadjLabel = xlt('Patient Adj');
$patpayLabel = xlt('Patient Payments');

foreach ($user_info as $row) {
    $user = text($row['user']);
    $fee = sprintf('%.2f', $row['fee']);
    $insadj = sprintf('%.2f', $row['insadj']);
    $inspay = sprintf('%.2f', $row['inspay']);
    $patadj = sprintf('%.2f', $row['patadj']);
    $patpay = sprintf('%.2f', $row['patpay']);

    echo <<<HTML
        <table border=1><tr>
        <br /><br /><td width=70><span class=text><b>{$userLabel}</center></b><center>{$user}<td width=140><span class=text><b><center>{$chargesLabel} </center></b><center> {$fee}<td width=140><span class=text><b><center>{$insadjLabel}. </center></b><center>{$insadj}<td width=140><span class=text><b><center>{$inspayLabel} </center></b><center>{$inspay}<td width=140><span class=text><b><center>{$patadjLabel}. </center></b><center>{$patadj}<td width=140><span class=text><b><center>{$patpayLabel} </center></b><center>{$patpay}<br /></td>
        HTML;

    $gtotal_fee += $row['fee'];
    $gtotal_insadj += $row['insadj'];
    $gtotal_inspay += $row['inspay'];
    $gtotal_patadj += $row['patadj'];
    $gtotal_patpay += $row['patpay'];
}

$grandTotalsLabel = xlt('Grand Totals');
$totalChargesLabel = xlt('Total Charges');
$gtotalFee = sprintf('%.2f', $gtotal_fee);
$gtotalInsadj = sprintf('%.2f', $gtotal_insadj);
$gtotalInspay = sprintf('%.2f', $gtotal_inspay);
$gtotalPatadj = sprintf('%.2f', $gtotal_patadj);
$gtotalPatpay = sprintf('%.2f', $gtotal_patpay);

echo <<<HTML
    <table border=1><tr>
    <br /><br /><td width=70><span class=text><b><center>{$grandTotalsLabel} <td width=140><span class=text><b><center>{$totalChargesLabel} </center></b><center> {$gtotalFee}<td width=140><span class=text><b><center>{$insadjLabel}. </center></b><center>{$gtotalInsadj}<td width=140><span class=text><b><center>{$inspayLabel} </center></b><center>{$gtotalInspay}<td width=140><span class=text><b><center>{$patadjLabel}. </center></b><center>{$gtotalPatadj}<td width=140><span class=text><b><center>{$patpayLabel} </center></b><center>{$gtotalPatpay}<br /></td></table>
    HTML;

?>
</body>
</html>
