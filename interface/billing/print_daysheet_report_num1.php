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


require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/daysheet.inc.php");

use OpenEMR\Billing\BillingReport;
use OpenEMR\Billing\DaySheet\DaySheetAggregator;
use OpenEMR\Billing\DaySheet\DaySheetTotals;
use OpenEMR\Billing\DaySheet\SlotTotals;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

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
<body>
    <div class="container">
        <div class="row">
            <a href="javascript:window.close();" target="Main"><p class="title"><?php echo xlt('Day Sheet Report')?></p></a>
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

            if (isset($_GET["mode"]) && $_GET["mode"] === 'bill') {
                BillingReport::billCodesList($list);
            }

            $res_count = 0;
            $N = 1;
            $k = 1;
            $anypats = 0;
            $the_first_time = 1;
            $itero = [];
            $daySheetTotals = new DaySheetTotals([], [], new SlotTotals(''));

            if ($ret = getBillsBetweendayReport($code_type)) {
            // checking to see if there is any information in the array if not display a message (located after this if statement)
                $anypats = count($ret);
                $run_provider = 0;
                $old_pid = -1;
                $first_time = 1;
                $new_old_pid = -1;

            // $iter has encounter information

            // this loop gathers the user and provider numbers
                foreach ($ret as $iter) {
                    $catch_user[] = $iter['user'];
                    $catch_provider[] = $iter['provider_id'];
                }

            //This statement uniques the arrays removing duplicates

                $user_list = array_unique($catch_user);
                $provider_list = array_unique($catch_provider);

            // reorder the list starting with array element zero
                $user_final_list = array_values($user_list);
                $provider_final_list = array_values($provider_list);
            // sort array in ascending order
                sort($user_final_list);
                sort($provider_final_list);
                $all4 = array_natsort($ret, 'pid', 'fulname', 'asc');

                if ($_POST['end_of_day_provider_only'] == 1) {
                    $run_provider = 1;
                }

                if ($_POST['end_of_day_totals_only'] == 1) {
                    $totals_only = 1;
                }

                $daySheetTotals = (new DaySheetAggregator())->aggregate($all4);

                foreach ($all4 as $iter) {
                    if ($the_first_time === 1) {
                        $user = $iter['user'];
                        $new_old_pid = $iter['pid'];
                        $the_first_time = 0;
                    }

                    if ($totals_only != 1) {
                        if ($old_pid != $iter['pid'] and ($iter['code_type'] != 'payment_info')) {
                            if ($old_pid === $new_old_pid) {
                                $line_total = 0;
                                $line_total_pay = 0;
                            }

                            if ($first_time) {
                                print "<div class='table-responsive'><table class='table'>";     // small table
                                $first_time = 0;
                            } ?>

                            <!-- TODO: Further replace with classes -->
                            <tr>
                                <td class='text text-center font-weight-bold' width='70'>
                                    <?php echo xlt("Date"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='50'>
                                    <?php echo xlt("Acct"); ?>#
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Name"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Source"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("CPT"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("ICD"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Charges"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Payments") . '/' . xlt("Adj"); ?>.
                                </td>
                            </tr>
                            <?php
                            //Next patient
                            $old_pid = $iter['pid'];
                        }

                        // get dollar amounts to appear on pat,ins payments and copays

                        if ($iter['code_type'] != 'payment_info') {
                            if (in_array($iter['code_type'], ['COPAY', 'Patient Payment', 'Insurance Payment'], true)) { ?>
                                <tr>
                                    <td class='text text-center' width='70'>
                                        <?php echo text(date("Y-m-d", strtotime((string) $iter['date']))); ?>
                                    </td>
                                    <td class='text text-center' width='50'>
                                        <?php echo text($iter['pid']); ?>
                                    </td>
                                    <td class='text text-center' width='180'>
                                        <?php echo text($iter['last']) . ", " . text($iter['first']) ?>
                                    </td>

                                <?php if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Adjustment'); ?>
                                    </td>
                                <?php } ?>

                                <?php
                                if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Adjustment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Payment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Payment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Credit'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Credit'); ?>
                                    </td>
                                <?php } ?>

                                <?php if ($iter['paytype'] === 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('COPAY'); ?>
                                    </td>
                                <?php } ?>

                                <td class='text' width='100'>
                                </td>
                                <td class='text' width='100'>
                                </td>
                                <td class='text' width='100'>
                                </td>

                                <?php if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                                    $line_total_pay += $iter['ins_adjust_dollar']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['ins_adjust_dollar']) ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                                    $line_total_pay += $iter['ins_code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['ins_code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['code_type']) != 'Patient Payment' and ($iter['code_type']) != 'Insurance Payment') {
                                    $line_total_pay += $iter['code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                                    $line_total_pay += $iter['pat_adjust_dollar']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['pat_adjust_dollar']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                                    $line_total_pay += $iter['pat_code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['pat_code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['code_type']) != 'Insurance Payment' and ($iter['code_type']) != 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['code_type']); ?>
                                    </td>
                                <?php } ?>

                                <td class='text' width='100'>
                                </td>

                            <?php } else { ?>
                                    <?php if ($iter['fee'] != 0) {
                                        $line_total += $iter['fee']; ?>
                                        <td class='text' width='70'>
                                            <?php echo text(date("Y-m-d", strtotime((string) $iter['date']))); ?>
                                        </td>
                                        <td class='text' width='50'>
                                            <?php echo text($iter['pid']); ?>
                                        </td>
                                        <td class='text' width='180'>
                                            <?php echo text($iter['last']) . ", " . text($iter['first']); ?>
                                        </td>

                                        <?php if (OEGlobalsBag::getInstance()->getString('language_default') === 'English (Standard)') { ?>
                                            <td class='text' width='100'>
                                                <?php echo text(ucwords(strtolower(substr((string) $iter['code_text'], 0, 25)))); ?>
                                            </td>
                                        <?php } else { ?>
                                            <td class='text' width='100'>
                                                <?php echo text(substr((string) $iter['code_text'], 0, 25)); ?>
                                            </td>
                                        <?php } ?>

                                        <td class='text' width='100'>
                                            <?php echo text($iter['code']); ?>
                                        </td>
                                        <td class='small' width='100'>
                                            <?php echo text(substr((string) $iter['justify'], 5, 3)); ?>
                                        </td>
                                        <td class='small' width='100'>
                                            <?php echo text($iter['fee']); ?>
                                        </td>
                                            <?php
                                    }
                            }

                            if (in_array($iter['code_type'], ['COPAY', 'Patient Payment', 'Insurance Payment'], true) || $iter['fee'] != 0) {
                                $res_count++;
                            }

                            if ($res_count === $N) {
                                print "</tr><tr>\n";
                                $res_count = 0;
                            }

                            $itero = $iter;

                            if ($old_pid != $new_old_pid and ($iter['code_type'] != 'payment_info')) {
                                $new_old_pid = $old_pid;
                            }
                        }
                    }

                    // end totals only
                }

            // end for
            }


            if ($anypats === 0) {
                ?><p><?php echo xlt('No Data to Process')?></p><?php
            }

                // TEST TO SEE IF THERE IS INFORMATION IN THE VARIABLES THEN ADD TO AN ARRAY FOR PRINTING
            if ($run_provider != 1) {
                foreach ($daySheetTotals->userTotals as $slot) {
                    $user_info['user'][$k] = $slot->key;
                    $user_info['fee'][$k]  = $slot->fee;
                    $user_info['inspay'][$k]  = $slot->insPay;
                    $user_info['insadj'][$k]  = $slot->insAdj;
                    $user_info['insref'][$k]  = $slot->insRef;
                    $user_info['patadj'][$k]  = $slot->patAdj;
                    $user_info['patpay'][$k]  = $slot->patPay;
                    $user_info['patref'][$k]  = $slot->patRef;
                    ++$k;
                }
            }

            if ($run_provider === 1) {
                foreach ($daySheetTotals->providerTotals as $slot) {
                    $provider_info['user'][$k] = $slot->key;
                    $provider_info['fee'][$k]  = $slot->fee;
                    $provider_info['inspay'][$k]  = $slot->insPay;
                    $provider_info['insadj'][$k]  = $slot->insAdj;
                    $provider_info['insref'][$k]  = $slot->insRef;
                    $provider_info['patadj'][$k]  = $slot->patAdj;
                    $provider_info['patpay'][$k]  = $slot->patPay;
                    $provider_info['patref'][$k]  = $slot->patRef;
                    ++$k;
                }
            }

            if ($totals_only === 1) {
                $from_date = oeFormatShortDate(substr((string) $query_part_day, 37, 10));
                $to_date = oeFormatShortDate(substr((string) $query_part_day, 63, 10));?>
                <br />
                <br />
                <p><?php echo xlt('Totals for ') . text($from_date) . ' ' . xlt('To{{Range}}') . ' ' . text($to_date) ?></p>
            <?php } ?>


            <?php if ($run_provider != 1) { ?>
                <table class='table table-borderless'>
                    <tr>
                        <br />
                        <br />
                        <td class='text' width='25'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("User"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("Charges"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("Payments"); ?>
                        </td>
                        <td class='text' width='25'>

                <?php for ($i = 1; $i < $k;) { ?>
                            <br />
                        </td>
                        <table class='table table-borderless'>
                            <tr>
                                <td class='text' width='25'>
                                </td>
                                <td class='text' width='250'>
                                    <?php echo text($user_info['user'][$i]); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Charges") . ': ' . "%1\$.2f", text($user_info['fee'][$i])); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Payments") . ': ' . "(%1\$.2f)", text($user_info['inspay'][$i] + $user_info['patpay'][$i])); ?>
                                </td>
                                <td class='text' width='25'>
                                    <br />
                                </td>

                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text' width='25'>
                                        </td>
                                        <td class='text' width='250'>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Total Adj") . '.: ' . "(%1\$.2f)", text($user_info['patadj'][$i] + $user_info['insadj'][$i])); ?>
                                        </td>
                                        <td width='125'><span class='text'></span></td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Refund") . ': ' . "(%1\$.2f)", text($user_info['patref'][$i] + $user_info['insref'][$i])); ?>
                                        </td>
                                        <td class='text' width='25'>
                                            <br />
                                        </td>
                                        <table class='table table-borderless'>
                                            <tr>
                                                <td class='text' width='25'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text font-weight-bold' width='175' height='5'>
                                                    <hr />
                                                </td>
                                                <td class='text' width='25'>
                                                    <br />
                                                </td>

                                                <table class='table table-borderless'>
                                                    <tr>
                                                        <td class='text' width='25'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text font-weight-bold' width='250'>
                                                            <?php printf(xlt("Actual Receipts") . ': ' . "(%1\$.2f)", text($user_info['patref'][$i] + $user_info['insref'][$i] + $user_info['inspay'][$i] + $user_info['patpay'][$i])); ?>
                                                        </td>
                                                        <td class='text' width='25'>
                                                            <br />
                                                        </td>

                                                        <table class='table table-borderless'>
                                                            <tr>
                                                                <td  class='text' width='25'>
                                                                </td>
                                                                <td  class='text' width='250'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td  class='text' width='250'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='25'>

                    <?php
                    $gtotal_fee += $user_info['fee'][$i];
                    $gtotal_insadj += $user_info['insadj'][$i];
                    $gtotal_inspay += $user_info['inspay'][$i];
                    $gtotal_patadj += $user_info['patadj'][$i];
                    $gtotal_patpay += $user_info['patpay'][$i];

                    ++$i;

                    print "<br /></td></tr>";
                } ?>

                            <br />
                        </td>
                    </table>
                </div>
            <?php } else { ?>
                <table class='table table-borderless'>
                    <tr>
                        <br />
                        <br />
                        <td class='text' width='25'>
                        </td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Provider"); ?>
                        </td>
                        <td class='text' width='125'></td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Charges"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Payments"); ?>
                        </td>
                        <td class='text' width='25'>

                <?php for ($i = 1; $i < $k;) { ?>
                            <br />
                        </td>
                        <table class='table table-borderless'>
                            <tr>
                                <td class='text' width='25'>
                                </td>
                                <td class='text text-center' width='250'>
                                    <?php echo text($provider_info['user'][$i]); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Charges") . ': ' . " %1\$.2f ", text($provider_info['fee'][$i])); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Payments") . ': ' . "(%1\$.2f)", text($provider_info['inspay'][$i] + $provider_info['patpay'][$i])); ?>
                                </td>
                                <td class='text' width='25'>
                                    <br />
                                </td>

                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text' width='25'>
                                        </td>
                                        <td class='text' width='250'>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Total Adj") . '.: ' . "(%1\$.2f)", text($provider_info['patadj'][$i] + $provider_info['insadj'][$i])); ?>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Refund") . ': ' . "(%1\$.2f)", text($provider_info['patref'][$i] + $provider_info['insref'][$i])); ?>
                                        </td>
                                        <td class='text' width='25'>
                                            <br />
                                        </td>

                                        <table class='table table-borderless'>
                                            <tr>
                                                <td class='text' width='25'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='175' height='5'>
                                                   <hr />
                                                </td>
                                                <td class='text' width='25'>
                                                    <br />
                                                </td>

                                                <table class='table table-borderless'>
                                                    <tr>
                                                        <td class='text' width='25'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text font-weight-bold' width='250'>
                                                            <?php printf(xlt("Actual Receipts") . ': ' . "(%1\$.2f)", text($provider_info['patref'][$i] + $provider_info['insref'][$i] + $provider_info['inspay'][$i] + $provider_info['patpay'][$i])); ?>
                                                        </td>
                                                        <td class='text' width='25'>
                                                        </td>

                                                        <table class='table table-borderless'>
                                                            <tr>
                                                                <td class='text' width='25'>
                                                                </td>
                                                                <td class='text' width='250'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='250'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='25'>

                    <?php
                        $gtotal_fee += $provider_info['fee'][$i];
                        $gtotal_insadj += $provider_info['insadj'][$i];
                        $gtotal_inspay += $provider_info['inspay'][$i];
                        $gtotal_insref += $provider_info['insref'][$i];
                        $gtotal_patadj += $provider_info['patadj'][$i];
                        $gtotal_patpay += $provider_info['patpay'][$i];
                        $gtotal_patref += $provider_info['patref'][$i];

                        ++$i;

                        print "<br /></td></tr>";
                }

                print "<br /></td>";
                print "</table>";
            }
            ?>
        </div>
    </div>
</body>
</html>
