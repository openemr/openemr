<?php

/**
 * Checkout Module.
 *
 * This module supports a popup window to handle patient checkout
 * as a point-of-sale transaction.  Support for in-house drug sales
 * is included.
 *
 * <pre>
 * Important notes about system design:
 * (1) Drug sales may or may not be associated with an encounter;
 *     they are if they are paid for concurrently with an encounter, or
 *     if they are "product" (non-prescription) sales via the Fee Sheet.
 *     UPDATE: ENCOUNTER IS NOW ALWAYS REQUIRED.
 * (2) Drug sales without an encounter will have 20YYMMDD, possibly
 *     with a suffix, as the encounter-number portion of their invoice
 *     number.
 * (3) Payments are saved as AR only, don't mess with the billing table.
 *     See library/classes/WSClaim.class.php for posting code.
 * (4) On checkout, the billing and drug_sales table entries are marked
 *     as billed and so become unavailable for further billing.
 * (5) Receipt printing must be a separate operation from payment,
 *     and repeatable.
 *
 * TBD:
 * If this user has 'irnpool' set
 *   on display of checkout form
 *     show pending next invoice number
 *   on applying checkout
 *     save next invoice number to form_encounter
 *     compute new next invoice number
 *   on receipt display
 *     show invoice number
 * </pre>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/checkout_receipt_array.inc.php");
require_once("$srcdir/appointment_status.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

// Change this to get the old appearance.
$TAXES_AFTER_ADJUSTMENT = true;

$currdecimals = intval($GLOBALS['currency_decimals'] ?? 2);

// Details default to yes now.
$details = (!isset($_GET['details']) || !empty($_GET['details'])) ? 1 : 0;

$patient_id   = empty($_GET['ptid']) ? $pid : intval($_GET['ptid']);
$encounter_id = empty($_GET['enid']) ?    0 : intval($_GET['enid']);
$checkout_id  = empty($_GET['coid']) ?   '' : $_GET['coid']; // timestamp of checkout

// This flag comes from the Fee Sheet form and perhaps later others.
$rapid_data_entry = empty($_GET['rde']) ? 0 : 1;

if (
    !AclMain::aclCheckCore('admin', 'super') &&
    !AclMain::aclCheckCore('acct', 'bill') &&
    !AclMain::aclCheckCore('acct', 'disc')
) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Client Receipt")]);
    exit;
}

// This will be used for SQL timestamps that we write.
$this_bill_date = date('Y-m-d H:i:s');

// Get the patient's name and chart number.
$patdata = getPatientData($patient_id, 'fname,mname,lname,pubpid,street,city,state,postal_code');

// Adjustments from the ar_activity table.
$aAdjusts = array();

// Holds possible javascript error messages.
$alertmsg = '';

// Format a money amount with decimals but no other decoration.
// Second argument is used when extra precision is required.
function formatMoneyNumber($value, $extradecimals = 0)
{
    return sprintf('%01.' . ($GLOBALS['currency_decimals'] + $extradecimals) . 'f', $value);
}

// Get a list item's title, translated if appropriate.
//
function getListTitle($list, $option)
{
    $row = sqlQuery(
        "SELECT title FROM list_options WHERE list_id = ? AND option_id = ? AND activity = 1",
        array($list, $option)
    );
    if (empty($row['title'])) {
        return $option;
    }
    return xl_list_label($row['title']);
}

function generate_layout_display_field($formid, $fieldid, $currvalue)
{
    $frow = sqlQuery(
        "SELECT * FROM layout_options WHERE form_id = ? AND field_id = ? LIMIT 1",
        array($formid, $fieldid)
    );
    if (empty($frow)) {
        return $currvalue;
    }
    return generate_display_field($frow, $currvalue);
}

// This creates and loads the array $aAdjusts of adjustment data for this encounter.
//
function load_adjustments($patient_id, $encounter_id)
{
    global $aAdjusts;
    // Create array aAdjusts from ar_activity rows for $encounter_id.
    $aAdjusts = array();
    $ares = sqlStatement(
        "SELECT " .
        "a.payer_type, a.adj_amount, a.memo, a.code_type, a.code, a.post_time, a.post_date, " .
        "s.session_id, s.reference, s.check_date, lo.title AS memotitle " .
        "FROM ar_activity AS a " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'adjreason' AND lo.option_id = a.memo AND " .
        "lo.activity = 1 " .
        "LEFT JOIN ar_session AS s ON s.session_id = a.session_id WHERE " .
        "a.pid = ? AND a.encounter = ? AND a.deleted IS NULL AND " .
        "( a.adj_amount != 0 OR a.pay_amount = 0 ) " .
        "ORDER BY s.check_date, a.sequence_no",
        array($patient_id, $encounter_id)
    );
    while ($arow = sqlFetchArray($ares)) {
        if (empty($arow['memotitle'])) {
            $arow['memotitle'] = $arow['memo'];
        }
        $aAdjusts[] = $arow;
    }
}

// Total and clear adjustments in $aAdjusts matching this line item. Should only
// happen for billed items, and matching includes the billing timestamp in order
// to handle the case of multiple checkouts.
function pull_adjustment($code_type, $code, $billtime, &$memo)
{
    global $aAdjusts;
    $adjust = 0;
    $memo = '';
    if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
        for ($i = 0; $i < count($aAdjusts); ++$i) {
            if (
                $aAdjusts[$i]['code_type'] == $code_type && $aAdjusts[$i]['code'] == $code &&
                $aAdjusts[$i]['post_time'] == $billtime &&
                ($aAdjusts[$i]['adj_amount'] != 0 || $aAdjusts[$i]['memotitle'] !== '')
            ) {
                $adjust += $aAdjusts[$i]['adj_amount'];
                if ($memo && $aAdjusts[$i]['memotitle']) {
                    $memo .= ', ';
                }
                $memo .= $aAdjusts[$i]['memotitle'];
                $aAdjusts[$i]['adj_amount'] = 0;
                $aAdjusts[$i]['memotitle'] = '';
            }
        }
    }
    return $adjust;
}

// Generate $aTaxNames = array of tax names, and $aInvTaxes = array of taxes for this invoice.
// For a given tax ID and line ID, $aInvTaxes[$taxID][$lineID] = tax amount.
// $lineID identifies the invoice line item (product or service) that was taxed, and is of the
// form S:<billing.id> or P:<drug_sales.sale_id>
// Taxes may change from time to time and $aTaxNames reflects only the taxes that were present
// for this invoice.
//
function load_taxes($patient_id, $encounter)
{
    global $aTaxNames, $aInvTaxes, $taxes;
    global $num_optional_columns, $rcpt_num_method_columns, $rcpt_num_ref_columns, $rcpt_num_amount_columns;
    global $form_num_type_columns, $form_num_method_columns, $form_num_ref_columns, $form_num_amount_columns;

    $aTaxNames = array();
    $aInvTaxes = array();
    foreach ($taxes as $taxid => $taxarr) {
        $aTaxNames[$taxid] = $taxarr[0];
        $aInvTaxes[$taxid] = array();
    }

    $taxres = sqlStatement(
        "SELECT code, fee, ndc_info FROM billing WHERE " .
        "pid = ? AND encounter = ? AND code_type = 'TAX' AND activity = 1 " .
        "ORDER BY id",
        array($patient_id, $encounter)
    );
    while ($taxrow = sqlFetchArray($taxres)) {
        $aInvTaxes[$taxrow['code']][$taxrow['ndc_info']] = $taxrow['fee'];
    }

    // Knowing the number of tax columns we can now compute the total number of optional
    // columns and from that the colspan values for various things.
    $num_optional_columns = (empty($GLOBALS['gbl_checkout_charges']) ? 0 : 1) +
        (empty($GLOBALS['gbl_charge_categories']) ? 0 : 1) +
        (empty($GLOBALS['gbl_checkout_line_adjustments']) ? 0 : 2) +
        count($aTaxNames);
    // Compute colspans for receipt payment rows.
    // What's in play here are columns for Qty, Price, the optionals, and Total.
    $rcpt_num_method_columns = 1;
    $rcpt_num_ref_columns = 1;
    if ($num_optional_columns == 1) {
        $rcpt_num_method_columns = 2;
    } else if ($num_optional_columns > 1) {
        $rcpt_num_method_columns = 3;
        $rcpt_num_ref_columns = $num_optional_columns - 1;
    }
    $rcpt_num_amount_columns = 3 + $num_optional_columns - $rcpt_num_method_columns - $rcpt_num_ref_columns;
    // Compute colspans for form payment rows.
    $form_num_type_columns = 2;
    $form_num_method_columns = 1;
    $form_num_ref_columns = 1;
    if ($num_optional_columns > 0) {
        $form_num_method_columns = 2;
    }
    if ($num_optional_columns > 1) {
        $form_num_type_columns = 3;
    }
    $form_num_amount_columns = 5 + $num_optional_columns - $form_num_type_columns - $form_num_method_columns - $form_num_ref_columns;
}

// Use $lineid to match up (and delete) entries in $aInvTaxes with the line.
// $lineid looks like: S:<billing.id> or P:<drug_sales.sale_id>.
// This writes to the $aTaxes argument and returns the total tax for the line.
function pull_tax($lineid, &$aTaxes)
{
    global $aInvTaxes;
    $totlinetax = 0;
    foreach ($aInvTaxes as $taxid => $taxarr) {
        $aTaxes[$taxid] = 0;
        if ($lineid !== '') {
            foreach ($taxarr as $taxlineid => $tax) {
                if ($taxlineid === $lineid) {
                    $aTaxes[$taxid] += $tax;
                    $totlinetax += $tax;
                    $aInvTaxes[$taxid][$taxlineid] = 0;
                }
            }
        }
    }
    // $aTaxes now contains the total of each tax type (keyed on tax ID) for this line item,
    // and those matched amounts are removed from $aInvTaxes.
    return $totlinetax;
}

// Output HTML for a receipt line item.
//
function receiptDetailLine(
    $code_type,
    $code,
    $description,
    $quantity,
    $charge,
    &$aTotals = '',
    $lineid = '',
    $billtime = '',
    $postdate = '',
    $chargecat = ''
) {
    global $details, $TAXES_AFTER_ADJUSTMENT;

    // Use $lineid to match up (and delete) entries in $aInvTaxes with the line.
    $aTaxes = array();
    $totlinetax = pull_tax($lineid, $aTaxes);
    // $aTaxes now contains the total of each tax type for this line item, and those matched
    // amounts are removed from $aInvTaxes.

    $adjust = 0;
    $memo = '';
    $isadjust = false;

    // If an adjustment, do appropriate interpretation.
    if ($code_type === '') {
        $isadjust = true;
        $adjust = 0 - $charge;
        $charge = 0;
        list($payer, $code_type, $code) = explode('|', $code);
        $memo = $description;
        $description = $GLOBALS['simplified_demographics'] ? '' : "$payer ";
        $description .= $code ? xl('Item Adjustment') : xl('Invoice Adjustment');
        $quantity = '';
    } else {
        // Total and clear adjustments in $aAdjusts matching this line item.
        $adjust += pull_adjustment($code_type, $code, $billtime, $memo);
    }

    $charge = formatMoneyNumber($charge);
    $total = formatMoneyNumber($charge + $totlinetax - $adjust);
    if (empty($quantity)) {
        $quantity = 1;
    }
    $price = formatMoneyNumber($charge / $quantity, 2);
    $tmp = formatMoneyNumber($price);
    if ($price == $tmp) {
        $price = $tmp;
    }
    if (is_array($aTotals)) {
        $aTotals[0] += $quantity;
        $aTotals[1] += $price;
        $aTotals[2] += $charge;
        $aTotals[3] += $adjust;
        $aTotals[4] += $total;
        // Accumulate columns 5 and beyond for taxes.
        $i = 5;
        foreach ($aTaxes as $tax) {
            $aTotals[$i++] += $tax;
        }
    }

    if (!$details) {
        return;
    }
    if (empty($postdate) || substr($postdate, 0, 4) == '0000') {
        $postdate = $billtime;
    }
    echo " <tr>\n";
    echo "  <td title='" . xla('Entered') . ' ' .
         text(oeFormatShortDate($billtime)) . attr(substr($billtime, 10)) . "'>" .
         text(oeFormatShortDate($postdate)) . "</td>\n";
    echo "  <td>" . text($code) . "</td>\n";
    echo "  <td>" . text($description) . "</td>\n";
    echo "  <td class='text-center'>" . ($isadjust ? '' : $quantity) . "</td>\n";
    echo "  <td class='text-right'>" . text(oeFormatMoney($price, false, true)) . "</td>\n";

    if (!empty($GLOBALS['gbl_checkout_charges'])) {
        echo "  <td class='text-right'>" . text(oeFormatMoney($charge, false, true)) . "</td>\n";
    }

    if (!$TAXES_AFTER_ADJUSTMENT) {
        // Write tax amounts.
        foreach ($aTaxes as $tax) {
            echo "  <td class='text-right'>" . text(oeFormatMoney($tax, false, true)) . "</td>\n";
        }
    }

    // Charge Category
    if (!empty($GLOBALS['gbl_charge_categories'])) {
        echo "  <td class='text-right'>" . text($chargecat) . "</td>\n";
    }

    // Adjustment and its description.
    if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
        echo "  <td class='text-right'>" . text($memo) . "</td>\n";
        echo "  <td class='text-right'>" . text(oeFormatMoney($adjust, false, true)) . "</td>\n";
    }

    if ($TAXES_AFTER_ADJUSTMENT) {
        // Write tax amounts.
        foreach ($aTaxes as $tax) {
            echo "  <td class='text-right'>" . text(oeFormatMoney($tax, false, true)) . "</td>\n";
        }
    }

    echo "  <td class='text-right'>" . text(oeFormatMoney($total)) . "</td>\n";
    echo " </tr>\n";
}

// Output HTML for a receipt payment line.
//
function receiptPaymentLine($paydate, $amount, $description = '', $method = '', $refno = '', $billtime = '')
{
    global $aTaxNames, $num_optional_columns;
    global $rcpt_num_method_columns, $rcpt_num_ref_columns, $rcpt_num_amount_columns;
    $amount = formatMoneyNumber($amount); // make it negative
    if ($description == 'Pt') {
        $description = '';
    }
    // Resolve the payment method portion of the memo to display properly.
    if (!empty($method)) {
        $tmp = explode(' ', $method, 2);
        $method = getListTitle('paymethod', $tmp[0]);
        if (isset($tmp[1])) {
            // If the description is not interesting then let it hold the check number
            // or similar, otherwise append that to the payment method.
            if ($description == '') {
                $description = $tmp[1];
            } else {
                $method .= ' ' . $tmp[1];
            }
        }
    }
    echo " <tr>\n";
    echo "  <td";
    if (!empty($billtime) && substr($billtime, 0, 4) != '0000') {
        echo " title='" . xla('Entered') . ' ' .
            text(oeFormatShortDate($billtime)) . attr(substr($billtime, 10)) . "'";
    }
    echo ">" . text(oeFormatShortDate($paydate)) . "</td>\n";
    echo "  <td colspan='2'>" . text($refno) . "</td>\n";
    echo "  <td colspan='$rcpt_num_method_columns' class='text-left'>" . text($method) . "</td>\n";
    echo "  <td colspan='$rcpt_num_ref_columns' class='text-left'>" . text($description) . "</td>\n";
    echo "  <td colspan='$rcpt_num_amount_columns' class='text-right'>" . text(oeFormatMoney($amount)) . "</td>\n";
    echo " </tr>\n";
}

// Compute a current checksum of this encounter's invoice-related data from the database.
//
function invoiceChecksum($pid, $encounter)
{
    $row1 = sqlQuery(
        "SELECT BIT_XOR(CRC32(CONCAT_WS(',', " .
        "id, code, modifier, units, fee, authorized, provider_id, ndc_info, justify, billed, user, bill_date" .
        "))) AS checksum FROM billing WHERE " .
        "pid = ? AND encounter = ? AND activity = 1",
        array($pid, $encounter)
    );
    $row2 = sqlQuery(
        "SELECT BIT_XOR(CRC32(CONCAT_WS(',', " .
        "sale_id, inventory_id, prescription_id, quantity, fee, sale_date, billed, bill_date" .
        "))) AS checksum FROM drug_sales WHERE " .
        "pid = ? AND encounter = ?",
        array($pid, $encounter)
    );
    $row3 = sqlQuery(
        "SELECT BIT_XOR(CRC32(CONCAT_WS(',', " .
        "sequence_no, code, modifier, payer_type, post_time, post_user, memo, pay_amount, adj_amount, post_date" .
        "))) AS checksum FROM ar_activity WHERE " .
        "pid = ? AND encounter = ?",
        array($pid, $encounter)
    );
    $row4 = sqlQuery(
        "SELECT BIT_XOR(CRC32(CONCAT_WS(',', " .
        "id, date, reason, facility_id, provider_id, supervisor_id, invoice_refno" .
        "))) AS checksum FROM form_encounter WHERE " .
        "pid = ? AND encounter = ?",
        array($pid, $encounter)
    );
    return (0 + $row1['checksum']) ^ (0 + $row2['checksum']) ^ (0 + $row3['checksum']) ^ (0 + $row4['checksum']);
}

//////////////////////////////////////////////////////////////////////
//
// Generate a receipt from the last-billed invoice for this patient,
// or for the encounter specified as a GET parameter.
//
function generate_receipt($patient_id, $encounter = 0)
{
    global $details, $rapid_data_entry, $aAdjusts;
    global $web_root, $webserver_root, $code_types;
    global $aTaxNames, $aInvTaxes, $checkout_times, $current_checksum;
    global $num_optional_columns, $rcpt_num_method_columns, $rcpt_num_ref_columns, $rcpt_num_amount_columns;
    global $TAXES_AFTER_ADJUSTMENT;
    global $facilityService, $alertmsg;

    // Get the most recent invoice data or that for the specified encounter.
    if ($encounter) {
        $ferow = sqlQuery(
            "SELECT id, date, encounter, facility_id, invoice_refno " .
            "FROM form_encounter WHERE pid = ? AND encounter = ?",
            array($patient_id, $encounter)
        );
    } else {
        $ferow = sqlQuery(
            "SELECT id, date, encounter, facility_id, invoice_refno " .
            "FROM form_encounter WHERE pid = ? ORDER BY id DESC LIMIT 1",
            array($patient_id)
        );
    }
    if (empty($ferow)) {
        die(xlt("This patient has no activity."));
    }
    $trans_id = $ferow['id'];
    $encounter = $ferow['encounter'];
    $svcdate = substr($ferow['date'], 0, 10);
    $invoice_refno = $ferow['invoice_refno'];

    // Generate checksum.
    $current_checksum = invoiceChecksum($patient_id, $encounter);

    // Get details for the visit's facility.
    $frow = $facilityService->getById($ferow['facility_id']);

    $patdata = getPatientData($patient_id, 'fname,mname,lname,pubpid,street,city,state,postal_code');

    // Get array of checkout timestamps.
    $checkout_times = craGetTimestamps($patient_id, $encounter);

    // Generate $aTaxNames = array of tax names, and $aInvTaxes = array of taxes for this invoice.
    load_taxes($patient_id, $encounter);
    ?>
<!-- The following is from the php function generate_receipt. -->
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']);?>
    <title><?php echo xlt('Client Receipt'); ?></title>

    <script>

    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

    $(function () {
        var win = top.printLogSetup ? top : opener.top;
        win.printLogSetup(document.getElementById('printbutton'));
    });

    // Process click on Print button.
    function printme(checkout_id) {
    <?php if (!empty($GLOBALS['gbl_custom_receipt'])) { ?>
        // Custom checkout receipt needs to be sent as a PDF in a new window or tab.
        window.open('pos_checkout.php?ptid=' + <?php echo js_url($patient_id); ?>
            + '&enc=' + <?php echo js_url($encounter); ?>
            + '&pdf=1&coid=' + encodeURIComponent(checkout_id),
            '_blank', 'width=750,height=550,resizable=1,scrollbars=1');
<?php } else { ?>
        var divstyle = document.getElementById('hideonprint').style;
        divstyle.display = 'none';
        if (checkout_id != '*') {
            window.print();
        }
<?php } ?>
        return false;
    }

    // Process click on Print button before printing.
    function printlog_before_print() {
        // * means do not call window.print().
        printme('*');
    }

    // Process click on Delete button.
    function deleteme() {
        dlgopen('deleter.php?billing=' + <?php echo js_url($patient_id . "." . $encounter); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
        return false;
    }

    // Called by the deleteme.php window on a successful delete.
    function imdeleted() {
        window.close();
    }

    var voidaction  = ''; // saves action argument from voidme()

    // Submit the form to complete a void operation.
    function voidwrap(form_reason, form_notes) {
        top.restoreSession();
        document.location.href = 'pos_checkout.php?ptid=' + <?php echo js_url($patient_id); ?> +
          '&' + encodeURIComponent(voidaction) + '=' + <?php echo js_url($encounter); ?> +
          '&form_checksum=' + <?php echo js_url($current_checksum); ?> +
          '&form_reason=' + encodeURIComponent(form_reason) +
          '&form_notes='  + encodeURIComponent(form_notes) +
          '<?php if (!empty($_GET['framed'])) {
                echo '&framed=1';} ?>';
        return false;
    }

    // Process click on a void option.
    // action can be 'regen', 'void' or 'voidall'.
    function voidme(action) {
        voidaction = action;
        if (action == 'void' || action == 'voidall') {
           if (!confirm(<?php echo xlj('This will advance the receipt number. Please print the receipt if you have not already done so.'); ?>)) {
              return false;
           }
           dlgopen('void_dialog.php', '_blank', 500, 450);
           return false;
        }
        // TBD: Better defaults for void reason and notes.
        voidwrap('', '');
        return false;
    }

    </script>

    <style>
    @media (min-width: 992px){
        .modal-lg {
            width: 1000px !Important;
        }
    }
    </style>
    <title><?php echo xlt('Patient Checkout'); ?></title>
</head>

<body>
    <div class='container mt-3'>
        <div class='row'>
            <div class='col text-center'>
                <table class='table' width='95%'>
                    <tr>
                        <td width='25%' align='left' valign='top'>
    <?php
    // TBD: Maybe make a global for this file name.
    if ($tmp = UrlIfImageExists('ma_logo.png')) {
        echo "<img src='$tmp' />";
    } else {
        echo "&nbsp;";
    }
    ?>
                        </td>
                        <td width='50%' align='center' valign='top' class='font-weight-bold'>
                            <?php echo text($frow['name']); ?>
                            <br><?php echo text($frow['street']); ?>
                            <br><?php
                            echo text($frow['city']) . ", ";
                            echo text($frow['state']) . " ";
                            echo text($frow['postal_code']); ?>
                            <br><?php echo text($frow['phone']); ?>
                        </td>
                        <td width='25%' align='right' valign='top'>
                            <!-- This space available. -->
                            &nbsp;
                        </td>
                    </tr>
                </table>
                <p class='font-weight-bold'>
    <?php
    echo xlt("Client Receipt");
    if ($invoice_refno) {
        echo " " . xlt("for Invoice") . text(" $invoice_refno");
    }
    ?>
                    <br />&nbsp;
                </p>
    <?php
    // Compute numbers for summary on right side of page.
    $head_begbal = get_patient_balance_excluding($patient_id, $encounter);
    $row = sqlQuery(
        "SELECT SUM(fee) AS amount FROM billing WHERE " .
        "pid = ? AND encounter = ? AND activity = 1 AND " .
        "code_type != 'COPAY'",
        array($patient_id, $encounter)
    );
    $head_charges = $row['amount'];
    $row = sqlQuery(
        "SELECT SUM(fee) AS amount FROM drug_sales WHERE pid = ? AND encounter = ?",
        array($patient_id, $encounter)
    );
    $head_charges += $row['amount'];
    $row = sqlQuery(
        "SELECT SUM(pay_amount) AS payments, " .
        "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
        "pid = ? AND encounter = ? AND deleted IS NULL",
        array($patient_id, $encounter)
    );
    $head_adjustments = $row['adjustments'];
    $head_payments = $row['payments'];
    $row = sqlQuery(
        "SELECT SUM(fee) AS amount FROM billing WHERE " .
        "pid = ? AND encounter = ? AND activity = 1 AND " .
        "code_type = 'COPAY'",
        array($patient_id, $encounter)
    );
    $head_payments -= $row['amount'];
    $head_endbal = $head_begbal + $head_charges - $head_adjustments - $head_payments;
    ?>
                <table class='table' width='95%'>
                    <tr>
                        <td width='50%' class='text-left' valign='top'>
                            <?php echo text($patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname']); ?>
                            <br /><?php echo text($patdata['street']); ?>
                            <br /><?php
                            echo generate_layout_display_field('DEM', 'city', $patdata['city']) . ", ";
                            echo generate_layout_display_field('DEM', 'state', $patdata['state']) . " ";
                            echo text($patdata['postal_code']); ?>
                        </td>
                        <td width='50%' class='text-right' valign='top'>
                            <table>
                                <tr>
                                    <td><?php echo xlt('Beginning Account Balance'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class='text-right'><?php echo text(oeFormatMoney($head_begbal)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo xlt('Total Visit Charges'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class='text-right'><?php echo text(oeFormatMoney($head_charges)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo xlt('Adjustments'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class='text-right'><?php echo text(oeFormatMoney($head_adjustments)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo xlt('Payments'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class='text-right'><?php echo text(oeFormatMoney($head_payments)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo xlt('Ending Account Balance'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class='text-right'><?php echo text(oeFormatMoney($head_endbal)); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table class='table' width='95%'>
    <?php if ($details) { ?>
                    <tr>
                        <td colspan='<?php echo 6 + $num_optional_columns; ?>'
                        style='padding-top:5pt;' class='font-weight-bold'>
                                <?php echo xlt('Charges for') . ' ' . text(oeFormatShortDate($svcdate)); ?>
                        </td>
                    </tr>

                    <tr>
                        <td class='font-weight-bold'><?php echo xlt('Date'); ?></td>
                        <td class='font-weight-bold'><?php echo xlt('Code'); ?></td>
                        <td class='font-weight-bold'><?php echo xlt('Description'); ?></td>
                        <td class='font-weight-bold text-center'><?php echo $details ? xlt('Qty') : '&nbsp;'; ?></td>
                        <td class='font-weight-bold text-right'><?php echo $details ? xlt('Price') : '&nbsp;'; ?></td>
        <?php if (!empty($GLOBALS['gbl_checkout_charges'])) { ?>
                        <td class='font-weight-bold text-right'><?php echo xlt('Charge'); ?></td>
    <?php } ?>
        <?php
        if (!$TAXES_AFTER_ADJUSTMENT) {
            foreach ($aTaxNames as $taxname) {
                echo "  <td class='font-weight-bold text-right'>" . text($taxname) . "</td>\n";
            }
        }
        ?>
        <?php if (!empty($GLOBALS['gbl_charge_categories'])) { ?>
                        <td class='font-weight-bold text-right'><?php echo xlt('Customer'); ?></td>
<?php } ?>
        <?php if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) { ?>
                        <td class='font-weight-bold text-right'><?php echo xlt('Adj Type'); ?></td>
                        <td class='font-weight-bold text-right'><?php echo xlt('Adj Amt'); ?></td>
<?php } ?>
        <?php
        if ($TAXES_AFTER_ADJUSTMENT) {
            foreach ($aTaxNames as $taxname) {
                echo "  <td class='font-weight-bold text-right'>" . text($taxname) . "</td>\n";
            }
        }
        ?>
                        <td class='font-weight-bold text-right'><?php echo xlt('Total'); ?></td>
                    </tr>

<?php } // end if details ?>

                    <tr>
                        <td colspan='<?php echo 6 + $num_optional_columns; ?>'
                        style='border-top:1px solid black; font-size:1px; padding:0;'>
                            &nbsp;
                        </td>
                    </tr>
    <?php
    // Create array aAdjusts from ar_activity rows for $encounter.
    load_adjustments($patient_id, $encounter);

    $aTotals = array(0, 0, 0, 0, 0);
    for ($i = 0; $i < count($aTaxNames); ++$i) {
        $aTotals[5 + $i] = 0;
    }

    // Product sales
    $inres = sqlStatement(
        "SELECT s.sale_id, s.sale_date, s.fee, " .
        "s.quantity, s.drug_id, s.billed, s.bill_date, s.selector, d.name, lo.title " .
        "FROM drug_sales AS s " .
        "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'chargecats' and lo.option_id = s.chargecat AND lo.activity = 1 " .
        "WHERE s.pid = ? AND s.encounter = ? " .
        "ORDER BY s.sale_id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        $billtime = $inrow['billed'] ? $inrow['bill_date'] : '';
        $tmpname = $inrow['name'];
        if ($tmpname !== $inrow['selector']) {
            $tmpname .= ' / ' . $inrow['selector'];
        }
        $units = $inrow['quantity'] / FeeSheet::getBasicUnits($inrow['drug_id'], $inrow['selector']);
        receiptDetailLine(
            'PROD',
            $inrow['drug_id'],
            $tmpname,
            $units,
            $inrow['fee'],
            $aTotals,
            'P:' . $inrow['sale_id'],
            $billtime,
            $svcdate,
            $inrow['title']
        );
    }

    // Service items.
    $inres = sqlStatement(
        "SELECT * FROM billing AS b " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'chargecats' and lo.option_id = b.chargecat AND lo.activity = 1 " .
        "WHERE b.pid = ? AND b.encounter = ? AND " .
        "b.code_type != 'COPAY' AND b.code_type != 'TAX' AND b.activity = 1 " .
        "ORDER BY b.id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        // Write the line item if it allows fees or is not a diagnosis.
        if (!empty($code_types[$inrow['code_type']]['fee']) || empty($code_types[$inrow['code_type']]['diag'])) {
            $billtime = $inrow['billed'] ? $inrow['bill_date'] : '';
            receiptDetailLine(
                $inrow['code_type'],
                $inrow['code'],
                $inrow['code_text'],
                $inrow['units'],
                $inrow['fee'],
                $aTotals,
                'S:' . $inrow['id'],
                $billtime,
                $svcdate,
                $inrow['title']
            );
        }
    }

    // Write any adjustments left in the aAdjusts array.
    foreach ($aAdjusts as $arow) {
        if ($arow['adj_amount'] == 0 && $arow['memotitle'] == '') {
            continue;
        }
        $payer = empty($arow['payer_type']) ? 'Pt' : ('Ins' . $arow['payer_type']);
        receiptDetailLine(
            '',
            "$payer|" . $arow['code_type'] . "|" . $arow['code'],
            $arow['memotitle'],
            1,
            0 - $arow['adj_amount'],
            $aTotals,
            '',
            $arow['post_time'],
            $arow['post_date']
        );
    }
    ?>
                    <tr>
                        <td colspan='<?php echo 6 + $num_optional_columns; ?>'
                        style='border-top:1px solid black; font-size:1px; padding:0;'>
                            &nbsp;
                        </td>
                    </tr>

    <?php
    // Sub-Total line with totals of all numeric columns.
    if ($details) {
        echo " <tr>\n";
        echo "  <td colspan='3' align='right'><b>" . xlt('Sub-Total') . "</b></td>\n";
        echo "  <td align='center'>" . text($aTotals[0]) . "</td>\n";
        echo "  <td align='right'>" . text(oeFormatMoney($aTotals[1])) . "</td>\n";
        // Optional charge amount.
        if (!empty($GLOBALS['gbl_checkout_charges'])) {
            echo "  <td align='right'>" . text(oeFormatMoney($aTotals[2])) . "</td>\n";
        }
        if (!$TAXES_AFTER_ADJUSTMENT) {
            // Put tax columns, if any, in the subtotals.
            for ($i = 0; $i < count($aTaxNames); ++$i) {
                echo "  <td align='right'>" . text(oeFormatMoney($aTotals[5 + $i])) . "</td>\n";
            }
        }
        // Optional charge category empty column.
        if (!empty($GLOBALS['gbl_charge_categories'])) {
            echo "  <td align='right'>&nbsp;</td>\n";
        }
        // Optional adjustment columns.
        if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
            echo "  <td align='right'>&nbsp;</td>\n";
            echo "  <td align='right'>" . text(oeFormatMoney($aTotals[3])) . "</td>\n";
        }
        if ($TAXES_AFTER_ADJUSTMENT) {
            // Put tax columns, if any, in the subtotals.
            for ($i = 0; $i < count($aTaxNames); ++$i) {
                echo "  <td align='right'>" . text(oeFormatMoney($aTotals[5 + $i])) . "</td>\n";
            }
        }
        echo "  <td align='right'>" . text(oeFormatMoney($aTotals[4])) . "</td>\n";
        echo " </tr>\n";
    }

    // Write a line for each tax item that did not match.
    // Should only happen for old invoices before taxes were assigned to line items.
    foreach ($aInvTaxes as $taxid => $taxarr) {
        foreach ($taxarr as $taxlineid => $tax) {
            if ($tax) {
                receiptDetailLine('TAX', $taxid, $aTaxNames[$taxid], 1, $tax, $aTotals);
                $aInvTaxes[$taxid][$taxlineid] = 0;
            }
        }
    }

    // Total Charges line.
    echo " <tr>\n";
    echo "  <td colspan='" . (3 + $num_optional_columns) . "'>&nbsp;</td>\n";
    echo "  <td colspan='" . 2 .
         "' align='right'><b>" . xlt('Total Charges') . "</b></td>\n";
    echo "  <td align='right'>" . text(oeFormatMoney($aTotals[4])) . "</td>\n";
    echo " </tr>\n";
    ?>

                    <tr>
                        <td colspan='<?php echo 6 + $num_optional_columns; ?>' style='padding-top:5pt;'>
                            <b><?php echo xlt('Payments'); ?></b>
                        </td>
                    </tr>

                    <tr>
                        <td><b><?php echo xlt('Date'); ?></b></td>
                        <td colspan='2'><b><?php echo xlt('Checkout Receipt Ref'); ?></b></td>
                        <td colspan="<?php echo text($rcpt_num_method_columns); ?>"
                        align='left'><b><?php echo xlt('Payment Method'); ?></b></td>
                        <td colspan="<?php echo text($rcpt_num_ref_columns); ?>"
                        align='left'><b><?php echo xlt('Ref No'); ?></b></td>
                        <td colspan='<?php echo text($rcpt_num_amount_columns); ?>'
                        align='right'><b><?php echo xlt('Amount'); ?></b></td>
                    </tr>

                    <tr>
                      <td colspan='<?php echo 6 + $num_optional_columns; ?>'
                      style='border-top:1px solid black; font-size:1px; padding:0;'>
                          &nbsp;
                      </td>
                    </tr>

    <?php
    $payments = 0;

    // Get co-pays.
    $inres = sqlStatement(
        "SELECT fee, code_text FROM billing WHERE " .
        "pid = ? AND encounter = ? AND " .
        "code_type = 'COPAY' AND activity = 1 AND fee != 0 " .
        "ORDER BY id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        $payments -= formatMoneyNumber($inrow['fee']);
        receiptPaymentLine($svcdate, 0 - $inrow['fee'], $inrow['code_text'], 'COPAY');
    }

    // Get other payments.
    $inres = sqlStatement(
        "SELECT " .
        "a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, " .
        "a.post_time, IFNULL(a.post_date, a.post_time) AS post_date, " .
        "s.payer_id, s.reference, s.check_date, s.deposit_date " .
        "FROM ar_activity AS a " .
        "LEFT JOIN ar_session AS s ON s.session_id = a.session_id WHERE " .
        "a.pid = ? AND a.encounter = ? AND a.deleted IS NULL AND " .
        "a.pay_amount != 0 " .
        "ORDER BY s.check_date, a.sequence_no",
        array($patient_id, $encounter)
    );
    $payer = empty($inrow['payer_type']) ? 'Pt' : ('Ins' . $inrow['payer_type']);
    while ($inrow = sqlFetchArray($inres)) {
        $payments += formatMoneyNumber($inrow['pay_amount']);
        // Compute invoice number with payment suffix.
        $tmp = array_search($inrow['post_time'], $checkout_times);
        $tmp = $tmp === false ? 0 : ($tmp + 1);
        $refno = $invoice_refno ? "$invoice_refno-$tmp" : "$encounter-$tmp";
        receiptPaymentLine(
            $inrow['post_date'],
            $inrow['pay_amount'],
            trim($payer . ' ' . $inrow['reference']),
            $inrow['memo'],
            $refno,
            $inrow['post_time']
        );
    }
    ?>

                    <tr>
                        <td colspan='<?php echo 6 + $num_optional_columns; ?>'
                        style='border-top:1px solid black; font-size:1px; padding:0;'>
                            &nbsp;
                        </td>
                    </tr>

                    <tr>
                        <td colspan='<?php echo 3 + $num_optional_columns; ?>'>&nbsp;</td>
                        <td colspan='2' align='right'><b><?php echo xlt('Total Payments'); ?></b></td>
                        <td align='right'><?php echo str_replace(' ', '&nbsp;', text(oeFormatMoney($payments, true))); ?></td>
                    </tr>

                </table>
            </div>
        </div>

        <div class='row'>
            <div class='col'>

    <?php
    // The user-customizable note.
    if (!empty($GLOBALS['gbl_checkout_receipt_note'])) {
        echo "<p>";
        echo str_repeat('*', 80) . '<br />';
        echo '&nbsp;&nbsp;' . text($GLOBALS['gbl_checkout_receipt_note']) . '<br />';
        echo str_repeat('*', 80) . '<br />';
        echo "</p>";
    }
    ?>

                <p>
                <b><?php echo xlt("Printed on") . ' ' . text(dateformat()); ?></b>
                </p>

                <div id='hideonprint'>
                    <p>
                        &nbsp;

    <?php
    if (count($checkout_times) > 1 && !empty($GLOBALS['gbl_custom_receipt'])) {
        // Multiple checkouts so allow selection of the one to print.
        // This is only applicable for custom checkout receipts.
        echo "<select onchange='printme(this.value)' >\n";
        echo " <option value=''>" . xlt('Print Checkout') . "</option>\n";
        $i = 0;
        foreach ($checkout_times as $tmp) {
            ++$i;
            echo " <option value='" . attr($tmp) . "'>" . text("$i: $tmp") . "</option>\n";
        }
        echo "</select>\n";
    } else {
        echo "<a href='#' onclick='return printme(\"\");'>" . xlt('Print') . "</a>\n";
    }
    ?>

    <?php if (AclMain::aclCheckCore('acct', 'disc')) { ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href='#' onclick='return voidme("regen");'><?php echo xlt('Generate New Receipt Number'); ?></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href='#' onclick='return voidme("void");' title='<?php echo xla('Applies to this visit only'); ?>'>
                        <?php echo xlt('Void Last Checkout'); ?></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href='#' onclick='return voidme("voidall");' title='<?php echo xla('Applies to this visit only'); ?>'>
                        <?php echo xlt('Void All Checkouts'); ?></a>
    <?php } ?>

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

    <?php if ($details) { ?>
                        <a href='pos_checkout.php?details=0&ptid=<?php echo attr_url($patient_id); ?>&enc=<?php echo attr_url($encounter); ?>'
                            onclick='top.restoreSession()'><?php echo xlt('Hide Details'); ?></a>
    <?php } else { ?>
                        <a href='pos_checkout.php?details=1&ptid=<?php echo attr_url($patient_id); ?>&enc=<?php echo attr_url($encounter); ?>'
                            onclick='top.restoreSession()'><?php echo xlt('Show Details'); ?></a>
    <?php } ?>
                    </p>
                </div><!-- end hideonprint -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- end container -->
    <script>
    <?php
    if ($alertmsg) {
        echo " alert(" . js_escape($alertmsg) . ");\n";
    }
    ?>
    </script>
</body>
</html>
    <?php
}

// end function generate_receipt()
//
//////////////////////////////////////////////////////////////////////

// Function to write the heading lines for the data entry form.
// This is deferred because we need to know which encounter was chosen.
//
$form_headers_written = false;
function write_form_headers()
{
    global $form_headers_written, $patdata, $patient_id, $encounter_id, $aAdjusts;
    global $taxes, $encounter_date, $num_optional_columns, $TAXES_AFTER_ADJUSTMENT;

    if ($form_headers_written) {
        return;
    }
    $form_headers_written = true;

    // Create arrays $aAdjusts, $aTaxNames and $aInvTaxes for this encounter.
    load_adjustments($patient_id, $encounter_id);
    // This also initializes $num_optional_columns and related colspan values.
    load_taxes($patient_id, $encounter_id);

    $ferow = sqlQuery(
        "SELECT date FROM form_encounter WHERE pid = ? AND encounter = ?",
        array($patient_id, $encounter_id)
    );
    $encounter_date = substr($ferow['date'], 0, 10);
    ?>
   <tr>
      <td colspan='<?php echo 5 + $num_optional_columns; ?>' align='center' class='title'>
          <?php echo xlt('Patient Checkout for '); ?><?php echo text($patdata['fname']) . " " .
              text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")" ?>
          <br />&nbsp;
          <p class='bold'>
    <?php
    $prvbal = get_patient_balance_excluding($patient_id, $encounter_id);
    echo xlt('Previous Balance') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<input type='text' value='" . attr(oeFormatMoney($prvbal)) . "' size='6' ";
    echo "style='text-align:right;background-color:transparent' readonly />\n";
    if ($prvbal > 0) {
        echo "&nbsp;<input type='button' value='" . xla('Pay Previous Balance') .
            "' onclick='payprevious()' />\n";
    }
    ?>
                <br />&nbsp;
            </p>
        </td>
    </tr>

    <tr>
    <?php if (!$TAXES_AFTER_ADJUSTMENT) { ?>
        <td colspan='<?php echo 4 + (empty($GLOBALS['gbl_checkout_charges']) ? 0 : 1) + count($taxes); ?>' class='bold'>
    <?php } else { ?>
        <td colspan='<?php echo 4 + (empty($GLOBALS['gbl_checkout_charges']) ? 0 : 1); ?>' class='bold'>
    <?php } ?>
            &nbsp;
        </td>
    <?php if (!empty($GLOBALS['gbl_charge_categories'])) { ?>
        <td align='right' class='bold' nowrap>
            <?php echo xlt('Default Customer'); ?>
        </td>
    <?php } ?>
    <?php if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) { ?>
        <td align='right' class='bold' nowrap>
            <?php echo xlt('Default Adjust Type'); ?>
        </td>
        <td class='bold'>
            &nbsp;
        </td>
    <?php } ?>
    <?php if (!$TAXES_AFTER_ADJUSTMENT) { ?>
        <td class='bold'>
    <?php } else { ?>
        <td colspan='<?php echo 1 + count($taxes); ?>' class='bold'>
    <?php } ?>
            &nbsp;
        </td>
    </tr>

    <tr>
    <?php if (!$TAXES_AFTER_ADJUSTMENT) { ?>
        <td colspan='<?php echo 4 + (empty($GLOBALS['gbl_checkout_charges']) ? 0 : 1) + count($taxes); ?>' class='title'>
    <?php } else { ?>
        <td colspan='<?php echo 4 + (empty($GLOBALS['gbl_checkout_charges']) ? 0 : 1); ?>' class='title'>
    <?php } ?>
            <?php echo xlt('Current Charges'); ?>
        </td>
    <?php if (!empty($GLOBALS['gbl_charge_categories'])) { // charge category default ?>
        <td align='right' class='bold'>
            <?php echo generate_select_list('form_charge_category', 'chargecats', '', '', ' ', '', 'chargeCategoryChanged();'); ?>
        </td>
    <?php } ?>
    <?php if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) { // adjustmenty reason default ?>
        <td align='right' class='bold'>
            <?php echo generate_select_list('form_discount_type', 'adjreason', '', '', ' ', '', 'discountTypeChanged();billingChanged();'); ?>
        </td>
        <td class='bold'>
            &nbsp;
        </td>
    <?php } ?>
    <?php if (!$TAXES_AFTER_ADJUSTMENT) { ?>
        <td class='bold'>
    <?php } else { ?>
        <td colspan='<?php echo 1 + count($taxes); ?>' class='bold'>
    <?php } ?>
            &nbsp;
        </td>
    </tr>

    <tr>
        <td class='bold'><?php echo xlt('Date'); ?></td>
        <td class='bold'><?php echo xlt('Description'); ?></td>
        <td align='right' class='bold'><?php echo xlt('Quantity'); ?></td>
    <?php if (empty($GLOBALS['gbl_checkout_charges'])) { // if no charges column ?>
        <td align='right' class='bold'><?php echo xlt('Charge'); ?></td>
    <?php } else { // charges column needed ?>
        <td align='right' class='bold'><?php echo xlt('Price'); ?></td>
        <td align='right' class='bold'><?php echo xlt('Charge'); ?></td>
    <?php } ?>
    <?php
    if (!$TAXES_AFTER_ADJUSTMENT) {
        foreach ($taxes as $taxarr) {
            echo "  <td align='right' class='bold'>" . text($taxarr[0]) . "</td>";
        }
    }
    ?>
    <?php if (!empty($GLOBALS['gbl_charge_categories'])) { // charge category ?>
        <td align='right' class='bold'><?php echo xlt('Customer'); ?></td>
    <?php } ?>
    <?php if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) { ?>
        <td align='right' class='bold'><?php echo xlt('Adjust Type'); ?></td>
        <td align='right' class='bold'><?php echo xlt('Adj'); ?></td>
    <?php } ?>
    <?php
    if ($TAXES_AFTER_ADJUSTMENT) {
        foreach ($taxes as $taxarr) {
            echo "  <td align='right' class='bold'>" . text($taxarr[0]) . "</td>";
        }
    }
    ?>
        <td align='right' class='bold'><?php echo xlt('Total'); ?></td>
    </tr>
    <?php
}

// Function to output a line item for the input form.
//
$totalchg = 0; // totals charges after adjustments
function write_form_line(
    $code_type,
    $code,
    $id,
    $date,
    $description,
    $amount,
    $units,
    $taxrates,
    $billtime = '',
    $chargecat = ''
) {
    global $lino, $totalchg, $aAdjusts, $taxes, $encounter_date, $TAXES_AFTER_ADJUSTMENT;

    // Write heading rows if that is not already done.
    write_form_headers();
    $amount = formatMoneyNumber($amount);
    if (empty($units)) {
        $units = 1;
    }
    $price = formatMoneyNumber($amount / $units, 2); // should be even cents, but...
    if (substr($price, -2) === '00') {
        $price = formatMoneyNumber($price);
    }

    // Total and clear adjustments in aAdjusts matching this line item. Should only
    // happen for billed items, and matching includes the billing timestamp in order
    // to handle the case of multiple checkouts.
    $memo = '';
    $adjust = pull_adjustment($code_type, $code, $billtime, $memo);
    $total = formatMoneyNumber($amount - $adjust);
    if (empty($GLOBALS['discount_by_money'])) {
        // Convert $adjust to a percentage of the amount, up to 4 decimal places.
        $adjust = round(100 * $adjust / $amount, 4);
    }

    // Compute the string of numeric tax rates to store with the charge line.
    $taxnumrates = '';
    $arates = explode(':', $taxrates);
    foreach ($taxes as $taxid => $taxarr) {
        $rate = $taxarr[1];
        if (empty($arates) || !in_array($taxid, $arates)) {
            $rate = 0;
        }
        $taxnumrates .= $rate . ':';
    }

    echo " <tr>\n";
    echo "  <td class='text'>" . text(oeFormatShortDate($encounter_date));
    echo "<input type='hidden' name='line[$lino][code_type]' value='" . attr($code_type) . "'>";
    echo "<input type='hidden' name='line[$lino][code]' value='" . attr($code) . "'>";
    echo "<input type='hidden' name='line[$lino][id]' value='" . attr($id) . "'>";
    echo "<input type='hidden' name='line[$lino][description]' value='" . attr($description) . "'>";
    // String of numeric tax rates is written here as a form field only for JavaScript tax computations.
    echo "<input type='hidden' name='line[$lino][taxnumrates]' value='" . attr($taxnumrates) . "'>";
    echo "<input type='hidden' name='line[$lino][units]' value='" . attr($units) . "'>";
    // Indicator of whether and when this line item was previously billed:
    echo "<input type='hidden' name='line[$lino][billtime]' value='" . attr($billtime) . "'>";
    echo "</td>\n";
    echo "  <td class='text'>" . text($description) . "</td>";
    echo "  <td class='text' align='right'>" . text($units) . "</td>";

    if (empty($GLOBALS['gbl_checkout_charges'])) {
        // We show only total charges here.
        echo "  <td class='text' align='right'>";
        echo "<input type='hidden' name='line[$lino][price]' value='" . attr($price) . "'>";
        echo "<input type='text' name='line[$lino][charge]' value='" . attr($amount) . "' size='6'";
        echo " style='text-align:right;background-color:transparent' readonly />";
        echo "</td>\n";
    } else {
        // In this case show price and extended charge amount.
        echo "  <td class='text' align='right'>";
        echo "<input type='text' name='line[$lino][price]' value='" . attr($price) . "' size='6'";
        echo " style='text-align:right;background-color:transparent' readonly />";
        echo "</td>\n";
        echo "  <td class='text' align='right'>";
        echo "<input type='text' name='line[$lino][charge]' value='" . attr($amount) . "' size='6'";
        echo " style='text-align:right;background-color:transparent' readonly />";
        echo "</td>\n";
    }

    // Match up (and delete) entries in $aInvTaxes with the line.
    $lineid = $code_type == 'PROD' ? "P:$id" : "S:$id";
    $aTaxes = array();
    pull_tax($lineid, $aTaxes); // fills in $aTaxes

    if (!$TAXES_AFTER_ADJUSTMENT) {
        // A tax column for each tax. JavaScript will compute the amounts and
        // account for how the discount affects them.
        $i = 0;
        foreach ($taxes as $taxid => $dummy) {
            echo "  <td class='text' align='right'>";
            echo "<input type='text' name='line[$lino][tax][$i]' size='6'";
            // Set tax amounts for existing billed items. JS must not recompute those.
            echo " value='" . attr(formatMoneyNumber($aTaxes[$taxid])) . "'";
            echo " style='text-align:right;background-color:transparent' readonly />";
            echo "</td>\n";
            ++$i;
        }
    }

    // Optional Charge Category.
    if (!empty($GLOBALS['gbl_charge_categories'])) {
        echo "  <td class='text' align='right'>";
        echo generate_select_list(
            "line[$lino][chargecat]",
            'chargecats',
            $chargecat,
            '',
            ' ',
            '',
            '',
            '',
            $billtime ? array('disabled' => 'disabled') : null
        );
        echo "</td>\n";
    }

    if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
        echo "  <td class='text' align='right'>";
        echo generate_select_list(
            "line[$lino][memo]",
            'adjreason',
            $memo,
            '',
            ' ',
            '',
            'billingChanged()',
            '',
            $billtime ? array('disabled' => 'disabled') : null
        );
        echo "</td>\n";
        echo "  <td class='text' align='right' nowrap>";
        echo empty($GLOBALS['discount_by_money']) ? '' : text($GLOBALS['gbl_currency_symbol']);
        echo "<input type='text' name='line[$lino][adjust]' size='6'";
        echo " value='" . attr(formatMoneyNumber($adjust)) . "'";
        // Modifying discount requires the acct/disc permission.
        if ($billtime || $code_type == 'TAX' || $code_type == 'COPAY' || !AclMain::aclCheckCore('acct', 'disc')) {
            echo " style='text-align:right;background-color:transparent' readonly";
        } else {
            echo " style='text-align:right' maxlength='8' onkeyup='lineDiscountChanged($lino)'";
        }
        echo " /> ";
        echo empty($GLOBALS['discount_by_money']) ? '%' : '';
        echo "</td>\n";
    }

    if ($TAXES_AFTER_ADJUSTMENT) {
        // A tax column for each tax. JavaScript will compute the amounts and
        // account for how the discount affects them.
        $i = 0;
        foreach ($taxes as $taxid => $dummy) {
            echo "  <td class='text' align='right'>";
            echo "<input type='text' name='line[$lino][tax][$i]' size='6'";
            // Set tax amounts for existing billed items. JS must not recompute those.
            echo " value='" . attr(formatMoneyNumber($aTaxes[$taxid])) . "'";
            echo " style='text-align:right;background-color:transparent' readonly />";
            echo "</td>\n";
            ++$i;
        }
    }

    // Extended amount after adjustments and taxes.
    echo "  <td class='text' align='right'>";
    echo "<input type='text' name='line[$lino][amount]' value='" . attr($total) . "' size='6'";
    echo " style='text-align:right;background-color:transparent' readonly />";
    echo "</td>\n";

    echo " </tr>\n";
    ++$lino;
    $totalchg += $amount;
}

// Function to output a past payment/adjustment line to the form.
//
function write_old_payment_line($pay_type, $date, $method, $reference, $amount)
{
    global $lino, $taxes, $num_optional_columns;
    global $form_num_type_columns, $form_num_method_columns, $form_num_ref_columns, $form_num_amount_columns;
    // Write heading rows if that is not already done.
    write_form_headers();
    $amount = formatMoneyNumber($amount);
    echo " <tr>\n";
    echo "  <td class='text' colspan='$form_num_type_columns'>" . text($pay_type) . "</td>\n";
    echo "  <td class='text' colspan='$form_num_method_columns'>" . text($method) . "</td>\n";
    echo "  <td class='text' colspan='$form_num_ref_columns'>" . text($reference) . "</td>\n";
    echo "  <td class='text' align='right' colspan='$form_num_amount_columns'><input type='text' name='oldpay[$lino][amount]' " .
         "value='$amount' size='6' maxlength='8'";
    echo " style='text-align:right;background-color:transparent' readonly";
    echo "></td>\n";
    echo " </tr>\n";
    ++$lino;
}

// Mark the tax rates that are referenced in this invoice.
function markTaxes($taxrates)
{
    global $taxes;
    $arates = explode(':', $taxrates);
    if (empty($arates)) {
        return;
    }
    foreach ($arates as $value) {
        if (!empty($taxes[$value])) {
            $taxes[$value][2] = '1';
        }
    }
}

// Create the taxes array.  Key is tax id, value is
// (description, rate, indicator).  Indicator seems to be unused.
$taxes = array();
$pres = sqlStatement(
    "SELECT option_id, title, option_value " .
    "FROM list_options WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq, title, option_id"
);
while ($prow = sqlFetchArray($pres)) {
    $taxes[$prow['option_id']] = array($prow['title'], $prow['option_value'], 0);
}

// Array of HTML for the 4 or 5 cells of an input payment row.
// "%d" will be replaced by a payment line number on the client side.
//
$aCellHTML = array();
$aCellHTML[] = "<span id='paytitle_%d'>" . text(xl('New Payment')) . "</span>";
$aCellHTML[] = strtr(generate_select_list('payment[%d][method]', 'paymethod', '', '', ''), array("\n" => ""));
$aCellHTML[] = "<input type='text' name='payment[%d][refno]' size='10' />";
$aCellHTML[] = "<input type='text' name='payment[%d][amount]' size='6' style='text-align:right' onkeyup='setComputedValues()' />";

$alertmsg = ''; // anything here pops up in an alert box

// Make sure we have the encounter ID applicable to this request.
if (!empty($_POST['form_save'])) {
    $patient_id = (int) $_POST['form_pid'];
    $encounter_id = (int) $_POST['form_encounter'];
} else {
    foreach (array('regen', 'enc', 'void', 'voidall') as $key) {
        if (!empty($_GET[$key])) {
            $encounter_id = (int) $_GET[$key];
            break;
        }
    }
}

// Compute and validate the checksum.
$current_checksum = 0;
if ($patient_id && $encounter_id) {
    $current_checksum = invoiceChecksum($patient_id, $encounter_id);
    if (!empty($_REQUEST['form_checksum'])) {
        if ($_REQUEST['form_checksum'] != $current_checksum) {
            $alertmsg = xl('Someone else has just changed this visit. Please cancel this page and try again.');
        }
    }
}

// If the Save button was clicked...
//
if (!empty($_POST['form_save']) && !$alertmsg) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // On a save, do the following:
    // Flag this form's drug_sales and billing items as billed.
    // Post line-level adjustments, replacing any existing ones for the same charges.
    // Post any invoice-level adjustment.
    // Post payments and be careful to use a unique invoice number.
    // Call the generate-receipt function.
    // Exit.

    // A current invoice reference number may be present if there was a previous checkout.
    $tmprow = sqlQuery(
        "SELECT invoice_refno FROM form_encounter WHERE " .
        "pid = ? AND encounter = ?",
        array($patient_id, $encounter_id)
    );
    $current_irnumber = $tmprow['invoice_refno'];

    // Get the posting date from the form as yyyy-mm-dd.
    $postdate = substr($this_bill_date, 0, 10);
    if (preg_match("/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/", $_POST['form_date'], $matches)) {
        $postdate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }
    $dosdate = $postdate; // not sure if this is appropriate

    if (! $encounter_id) {
        die("Internal error: Encounter ID is missing!");
    }

    // Delete unbilled TAX rows from billing because they will be recalculated.
    // Do not delete already-billed taxes; we must not touch billed stuff.
    sqlStatement(
        "UPDATE billing SET activity = 0 WHERE " .
        "pid = ? AND encounter = ? AND " .
        "code_type = 'TAX' AND billed = 0 AND activity = 1",
        array($patient_id, $encounter_id)
    );

    $form_amount = $_POST['form_totalpay'];
    $lines = $_POST['line'];

    for ($lino = 0; !empty($lines[$lino]['code_type']); ++$lino) {
        $line = $lines[$lino];
        $code_type = $line['code_type'];
        $code      = $line['code'];
        $id        = $line['id'];
        $chargecat = $line['chargecat'] ?? '';
        $amount    = formatMoneyNumber(trim($line['amount']));
        $linetax   = 0;

        // Skip saving taxes and adjustments for billed items.
        if (!empty($line['billtime'])) {
            continue;
        }

        // Insert any taxes for this line.
        // There's a chance of input data and the $taxes array being out of sync if someone
        // updates the taxrate list during data entry... we oughta do something about that.
        if (is_array($line['tax'])) {
            // For tax rows the ndc_info field is used to identify the charge item that is taxed.
            // P indicates drug_sales.sale_id, S indicates billing.id.
            $ndc_info = $code_type == 'PROD' ? "P:$id" : "S:$id";
            $i = 0;
            foreach ($taxes as $taxid => $taxarr) {
                $taxamount = $line['tax'][$i++] + 0;
                if ($taxamount != 0) {
                    BillingUtilities::addBilling(
                        $encounter_id,
                        'TAX',
                        $taxid,
                        $taxarr[0],
                        $patient_id,
                        0,
                        0,
                        '',
                        '',
                        $taxamount,
                        $ndc_info,
                        '',
                        0
                    );
                    // billed=0 because we will set billed and bill_date for unbilled items below.
                    $linetax += $taxamount;
                }
            }
        }

        // If there is an adjustment for this line, insert it.
        if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
            $adjust = 0.00 + trim($line['adjust']);
            $memo = formDataCore($line['memo']);
            if ($adjust != 0 || $memo !== '') {
                // $memo = xl('Discount');
                if ($memo === '') {
                    $memo = formData('form_discount_type');
                }
                sqlBeginTrans();
                $sequence_no = sqlQuery(
                    "SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE " .
                    "pid = ? AND encounter = ?",
                    array($patient_id, $encounter_id)
                );
                $query = "INSERT INTO ar_activity ( " .
                    "pid, encounter, sequence_no, code_type, code, modifier, payer_type, " .
                    "post_user, post_time, post_date, session_id, memo, adj_amount " .
                    ") VALUES ( " .
                    "?, ?, ?, ?, ?, '', '0', ?, ?, ?, '0', ?, ? " .
                    ")";
                sqlStatement($query, array(
                    $patient_id,
                    $encounter_id,
                    $sequence_no['increment'],
                    $code_type,
                    $code,
                    $_SESSION['authUserID'],
                    $this_bill_date,
                    $postdate,
                    $memo,
                    $adjust
                ));
                sqlCommitTrans();
            }
        }

        if (!empty($GLOBALS['gbl_charge_categories'])) {
            // Update charge category for this line item.
            if ($code_type == 'PROD') {
                $query = "UPDATE drug_sales SET chargecat = ? WHERE sale_id = ?";
                sqlQuery($query, array($chargecat, $id));
            } else {
                $query = "UPDATE billing SET chargecat = ? WHERE id = ?";
                sqlQuery($query, array($chargecat, $id));
            }
        }
    }

    // Flag the encounter as billed.
    $query = "UPDATE billing SET billed = 1, bill_date = ? WHERE " .
        "pid = ? AND encounter = ? AND activity = 1 AND billed = 0";
    sqlQuery($query, array($this_bill_date, $patient_id, $encounter_id));
    $query = "update drug_sales SET billed = 1, bill_date = ? WHERE " .
        "pid = ? AND encounter = ? AND billed = 0";
    sqlQuery($query, array($this_bill_date, $patient_id, $encounter_id));

    // Post discount.
    if ($_POST['form_discount'] != 0) {
        if ($GLOBALS['discount_by_money']) {
            $amount  = formatMoneyNumber(trim($_POST['form_discount']));
        } else {
            $amount  = formatMoneyNumber(trim($_POST['form_discount']) * $form_amount / 100);
        }
        $memo = formData('form_discount_type');
        sqlBeginTrans();
        $sequence_no = sqlQuery(
            "SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE " .
            "pid = ? AND encounter = ?",
            array($patient_id, $encounter_id)
        );
        $query = "INSERT INTO ar_activity ( " .
            "pid, encounter, sequence_no, code, modifier, payer_type, post_user, post_time, " .
            "post_date, session_id, memo, adj_amount " .
            ") VALUES ( " .
            "?, ?, ?, '', '', '0', ?, ?, ?, '0', ?, ? " .
            ")";
        sqlStatement($query, array(
            $patient_id,
            $encounter_id,
            $sequence_no['increment'],
            $_SESSION['authUserID'],
            $this_bill_date,
            $postdate,
            $memo,
            $amount
        ));
        sqlCommitTrans();
    }

    // Post the payments.
    if (is_array($_POST['payment'])) {
        $lines = $_POST['payment'];
        for ($lino = 0; isset($lines[$lino]['amount']); ++$lino) {
            $line = $lines[$lino];
            $amount = formatMoneyNumber(trim($line['amount']));
            if ($amount != 0.00) {
                $method = $line['method'];
                $refno  = $line['refno'];
                if ($method !== '' && $refno !== '') {
                    $method .= " $refno";
                }
                $session_id = 0; // Is this OK?
                SLEOB::arPostPayment(
                    $patient_id,
                    $encounter_id,
                    $session_id,
                    $amount,
                    '',
                    0,
                    $method,
                    0,
                    $this_bill_date,
                    '',
                    $postdate
                );
            }
        }
    }

    // If applicable, set the invoice reference number.
    if (!$current_irnumber) {
        $invoice_refno = '';
        if (isset($_POST['form_irnumber'])) {
            $invoice_refno = formData('form_irnumber', 'P', true);
        } else {
            $invoice_refno = add_escape_custom(BillingUtilities::updateInvoiceRefNumber());
        }
        if ($invoice_refno) {
            sqlStatement(
                "UPDATE form_encounter SET invoice_refno = ? WHERE pid = ? AND encounter = ?",
                array($invoice_refno, $patient_id, $encounter_id)
            );
        }
    }

    // If appropriate, update the status of the related appointment to
    // "Checked out".
    updateAppointmentStatus($patient_id, $dosdate, '>');

    generate_receipt($patient_id, $encounter_id);
    exit();
}

// Void attributes.
$form_reason = empty($_GET['form_reason']) ? '' : $_GET['form_reason'];
$form_notes  = empty($_GET['form_notes' ]) ? '' : $_GET['form_notes'];

// If "regen" encounter ID was given, then we must generate a new receipt ID.
//
if (!$alertmsg && $patient_id && !empty($_GET['regen'])) {
    BillingUtilities::doVoid(
        $patient_id,
        $encounter_id,
        false,
        '',
        $form_reason,
        $form_notes
    );
    $current_checksum = invoiceChecksum($patient_id, $encounter_id);
    $_GET['enc'] = $encounter_id;
}

// If "enc" encounter ID was given, then we must generate a receipt and exit.
//
if ($patient_id && !empty($_GET['enc'])) {
    if (empty($_GET['pdf'])) {
        generate_receipt($patient_id, $_GET['enc']);
    } else {
        // PDF receipt is requested. In this case we are probably in a new window.
        require_once($GLOBALS['OE_SITE_DIR'] . "/" . $GLOBALS['gbl_custom_receipt']);
        // $checkout_id is an optional specified checkout timestamp.
        $billtime = $checkout_id;
        if (!$billtime) {
            // No timestamp specified so use the last one.
            $checkout_times = craGetTimestamps($patient_id, $_GET['enc']);
            $billtime = empty($checkout_times) ? '' : $checkout_times[count($checkout_times) - 1];
        }
        generateCheckoutReceipt($patient_id, $_GET['enc'], $billtime);
    }
    exit();
}

// If "void" encounter ID was given, then we must undo the last checkout.
// Or for "voidall" undo all checkouts for the encounter.
//
if (!$alertmsg && $patient_id && !empty($_GET['void'])) {
    BillingUtilities::doVoid($patient_id, $encounter_id, true, '', $form_reason, $form_notes);
    $current_checksum = invoiceChecksum($patient_id, $encounter_id);
} else if (!$alertmsg && $patient_id && !empty($_GET['voidall'])) {
    BillingUtilities::doVoid($patient_id, $encounter_id, true, 'all', $form_reason, $form_notes);
    $current_checksum = invoiceChecksum($patient_id, $encounter_id);
}

// Get the specified or first unbilled encounter ID for this patient.
//
if (!$encounter_id) {
    $query = "SELECT encounter FROM billing WHERE " .
        "pid = ? AND activity = 1 AND billed = 0 AND code_type != 'TAX' " .
        "ORDER BY encounter DESC LIMIT 1";
    $brow = sqlQuery($query, array($patient_id));
    $query = "SELECT encounter FROM drug_sales WHERE " .
        "pid = ? AND billed = 0 " .
        "ORDER BY encounter DESC LIMIT 1";
    $drow = sqlQuery($query, array($patient_id));
    if (!empty($brow['encounter'])) {
        if (!empty($drow['encounter'])) {
            $encounter_id = min(intval($brow['encounter']), intval($drow['encounter']));
        } else {
            $encounter_id = $brow['encounter'];
        }
    } else if (!empty($drow['encounter'])) {
        $encounter_id = $drow['encounter'];
    }
}

// If there are none, just redisplay the last receipt and exit.
//
if (!$encounter_id) {
    generate_receipt($patient_id);
    exit();
}

// Form requires billing permission.
if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Checkout")]);
    exit;
}

// We have $patient_id and $encounter_id. Generate checksum if not already done.
if (!$current_checksum) {
    $current_checksum = invoiceChecksum($patient_id, $encounter_id);
}

// Get the valid practitioners, including those not active.
$arr_users = array();
$ures = sqlStatement(
    "SELECT id, username FROM users WHERE " .
    "( authorized = 1 OR info LIKE '%provider%' ) AND username != ''"
);
while ($urow = sqlFetchArray($ures)) {
    $arr_users[$urow['id']] = '1';
}

// Now write a data entry form:
// List unbilled billing items (cpt, hcpcs, copays) for the patient.
// List unbilled product sales for the patient.
// Present an editable dollar amount for each line item, a total
// which is also the default value of the input payment amount,
// and OK and Cancel buttons.
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker']);?>

<title><?php echo xlt('Patient Checkout'); ?></title>

<style>
    @media (min-width: 992px){
        .modal-lg {
            width: 1000px !Important;
        }
    }
</style>

<script>
    var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

    // This clears tax amounts in preparation for recomputing taxes.
    // TBD: Probably don't need this at all.
    function clearTax(visible) {
        var f = document.forms[0];
        for (var i = 0; f['totaltax[' + i + ']']; ++i) {
            f['totaltax[' + i + ']'].value = '0.00';
        }
    }

    // This computes taxes and extended amount for the specified line, and returns
    // the extended amount.
    function calcTax(lino) {
        var f = document.forms[0];
        var pfx = 'line[' + lino + ']';
        var taxable = parseFloat(f[pfx + '[charge]'].value);
        var adjust = 0.00;
        if (f[pfx + '[adjust]']) {
            adjust = parseFloat(f[pfx + '[adjust]'].value);
        }
        var adjreason = '';
        if (f[pfx + '[memo]']) {
            adjreason = f[pfx + '[memo]'].value;
        }
        var extended = taxable - adjust;
        if (true
<?php
    // Generate JavaScript that checks if the chosen adjustment type is to be
    // applied before taxes are computed. option_value 1 indicates that the
    // "After Taxes" checkbox is checked for an adjustment type.
    $tmpres = sqlStatement(
        "SELECT option_id FROM list_options WHERE " .
        "list_id = 'adjreason' AND option_value = 1 AND activity = 1"
    );
    while ($tmprow = sqlFetchArray($tmpres)) {
        echo "            && adjreason != " . js_escape($tmprow['option_id']) . "\n";
    }
    ?>
        ) {
            taxable -= adjust;
        }
        var taxnumrates  = f[pfx + '[taxnumrates]'].value;
        var rates = taxnumrates.split(':');
        for (var i = 0; i < rates.length; ++i) {
            if (! f[pfx + '[tax][' + i + ']']) {
                break;
            }
            var tax = 0;
            if (f[pfx + '[billtime]'].value) {
                // Line item is billed, use the tax amounts that were previously set for it.
                tax = parseFloat(f[pfx + '[tax][' + i + ']'].value);
            }
            else {
                tax = taxable * parseFloat(rates[i]);
                tax = parseFloat(tax.toFixed(<?php echo $currdecimals ?>));
                if (isNaN(tax)) {
                    alert('Tax rate not numeric at line ' + lino);
                }
                f[pfx + '[tax][' + i + ']'].value = tax.toFixed(<?php echo $currdecimals ?>);
            }
            extended += tax;
            var totaltax = parseFloat(f['totaltax[' + i + ']'].value) + tax;
            f['totaltax[' + i + ']'].value = totaltax.toFixed(<?php echo $currdecimals ?>);
        }
        f[pfx + '[amount]'].value = extended.toFixed(<?php echo $currdecimals ?>);
        return extended;
    }

    // This mess recomputes total charges and optionally applies a discount.
    // As part of this, taxes and extended amount are recomputed for each line.
    function computeDiscountedTotals(discount, visible) {
        clearTax(visible);
        var f = document.forms[0];
        var total = 0.00;
        for (var lino = 0; f['line[' + lino + '][code_type]']; ++lino) {
            var code_type = f['line[' + lino + '][code_type]'].value;
            // price is price per unit when the form was originally generated.
            // By contrast, amount is the dynamically-generated discounted line total.
            var price = parseFloat(f['line[' + lino + '][price]'].value);
            if (isNaN(price)) {
                alert('Price not numeric at line ' + lino);
            }
            if (code_type == 'COPAY' || code_type == 'TAX') {
                // I think this case is obsolete now.
                total += parseFloat(price.toFixed(<?php echo $currdecimals ?>));
                continue;
            }
            // Compute and set taxes and extended amount for the given line.
            // This also returns the extended amount.
            total += calcTax(lino);
        }
        if (visible) {
            f.totalchg.value = total.toFixed(<?php echo $currdecimals ?>);
        }
        return total - discount;
    }

    // This computes and returns the total of payments.
    function computePaymentTotal() {
        var f = document.forms[0];
        var total = 0.00;
        for (var lino = 0; ('oldpay[' + lino + '][amount]') in f; ++lino) {
            var amount = parseFloat(f['oldpay[' + lino + '][amount]'].value);
            if (isNaN(amount)) {
                continue;
            }
            amount = parseFloat(amount.toFixed(<?php echo $currdecimals ?>));
            total += amount;
        }
        for (var lino = 0; ('payment[' + lino + '][amount]') in f; ++lino) {
            var amount = parseFloat(f['payment[' + lino + '][amount]'].value);
            if (isNaN(amount)) {
                amount = parseFloat(0);
            }
            amount = parseFloat(amount.toFixed(<?php echo $currdecimals ?>));
            total += amount;
            // Set payment row's description to Refund if the amount is negative.
            var title = amount < 0 ? <?php echo xlj('Refund'); ?> : <?php echo xlj('New payment'); ?>;
            var span = document.getElementById('paytitle_' + lino);
            span.innerHTML = title;
        }
        return total;
    }

    // Recompute default payment amount with any discount applied, but
    // not if there is more than one input payment line.
    // This is called when the discount amount is changed, and initially.
    // As a side effect the tax line items are recomputed and
    // setComputedValues() is called.
    function billingChanged() {
        var f = document.forms[0];
        var discount = parseFloat(f.form_discount.value);
        if (isNaN(discount)) {
            discount = 0;
        }
<?php if (!$GLOBALS['discount_by_money']) { ?>
        // This site discounts by percentage, so convert it to a money amount.
        if (discount > 100) {
            discount = 100;
        }
        if (discount < 0  ) {
            discount = 0;
        }
        discount = 0.01 * discount * computeDiscountedTotals(0, false);
<?php } ?>
        var total = computeDiscountedTotals(discount, true);
        // Get out if there is more than one input payment line.
        if (!('payment[1][amount]' in f)) {
            f['payment[0][amount]'].value = 0;
            total -= computePaymentTotal();
            f['payment[0][amount]'].value = total.toFixed(<?php echo $currdecimals ?>);
        }
        setComputedValues();
        return true;
    }

    // Function to return the adjustment type, if any, identified in a customer's Notes field.
    function adjTypeFromCustomer(customer) {
        var ret = '';
<?php
    $tmpres = sqlStatement(
        "SELECT option_id, notes FROM list_options WHERE " .
        "list_id = 'chargecats' AND activity = 1"
    );
    while ($tmprow = sqlFetchArray($tmpres)) {
        if (
            preg_match('/ADJ=(\w+)/', $tmprow['notes'], $matches) ||
            preg_match('/ADJ="(.*?)"/', $tmprow['notes'], $matches)
        ) {
            echo "  if (customer == " . js_escape($tmprow['option_id']) . ") ret = " . js_escape($matches[1]) . ";\n";
        }
    }
    ?>
        return ret;
    }

    // A line item adjustment was changed, so recompute stuff.
    function lineDiscountChanged(lino) {
        var f = document.forms[0];
        var discount = parseFloat(f['line[' + lino + '][adjust]'].value);
        if (isNaN(discount)) {
            discount = 0;
        }
        var charge = parseFloat(f['line[' + lino + '][charge]'].value);
        if (isNaN(charge)) {
            charge = 0;
        }
<?php if (!$GLOBALS['discount_by_money']) { ?>
        // This site discounts by percentage, so convert it to a money amount.
        if (discount > 100) {
            discount = 100;
        }
        if (discount < 0  ) {
            discount = 0;
        }
        discount = 0.01 * discount * charge;
<?php } ?>
        var amount = charge - discount;
        f['line[' + lino + '][amount]'].value = amount.toFixed(<?php echo $currdecimals ?>);
        // alert(f['line[' + lino + '][amount]'].value); // debugging
        if (discount) {
            // Apply default adjustment type if one is specified in the customer (charge category).
            var custElem = f['line[' + lino + '][chargecat]'];
            var adjtElem = f['line[' + lino + '][memo]'];
            if (custElem && custElem.value && adjtElem && !adjtElem.value) {
                var ccAdjType = adjTypeFromCustomer(custElem.value);
                if (ccAdjType) {
                    adjtElem.value = ccAdjType;
                }
            }
        }
        return billingChanged();
    }

    // Set Total Payments, Difference and Balance Due when any amount changes.
    function setComputedValues() {
        var f = document.forms[0];
        var payment = computePaymentTotal();
        var difference = computeDiscountedTotals(0, false) - payment;
        var discount = parseFloat(f.form_discount.value);
        if (isNaN(discount)) {
            discount = 0;
        }
<?php if (!$GLOBALS['discount_by_money']) { ?>
        // This site discounts by percentage, so convert it to a money amount.
        if (discount > 100) {
            discount = 100;
        }
        if (discount < 0  ) {
            discount = 0;
        }
        discount = 0.01 * discount * computeDiscountedTotals(0, false);
<?php } ?>
        var balance = difference - discount;
        f.form_totalpay.value = payment.toFixed(<?php echo $currdecimals ?>);
        f.form_difference.value = difference.toFixed(<?php echo $currdecimals ?>);
        f.form_balancedue.value = balance.toFixed(<?php echo $currdecimals ?>);
        return true;
    }

    // This is called when [Compute] is clicked by the user.
    // Computes and sets the discount value from total charges less payment.
    // This also calls setComputedValues() so the balance due will be correct.
    function computeDiscount() {
        var f = document.forms[0];
        var charges = computeDiscountedTotals(0, false);
        var payment = computePaymentTotal();
        var discount = charges - payment;
<?php if (!$GLOBALS['discount_by_money']) { ?>
        // This site discounts by percentage, so convert to that.
        discount = charges ? (100 * discount / charges) : 0;
        f.form_discount.value = discount.toFixed(4);
<?php } else { ?>
        f.form_discount.value = discount.toFixed(<?php echo $currdecimals ?>);
<?php } ?>
        setComputedValues();
        return false;
    }

    // When the main adjustment reason changes, duplicate it to all per-line reasons.
    function discountTypeChanged() {
        var f = document.forms[0];
        if (f.form_discount_type && f.form_discount_type.selectedIndex) {
            for (lino = 0; f['line[' + lino + '][memo]']; ++lino) {
                // But do not change adjustment reason for billed items.
                if (f['line[' + lino + '][billtime]'].value) {
                    continue;
                }
                f['line[' + lino + '][memo]'].selectedIndex = f.form_discount_type.selectedIndex;
            }
        }
    }

    // When the main charge category changes, duplicate it to all per-line categories.
    function chargeCategoryChanged() {
        var f = document.forms[0];
        if (f.form_charge_category && f.form_charge_category.selectedIndex) {
            for (lino = 0; f['line[' + lino + '][chargecat]']; ++lino) {
                // But do not change categories for billed items.
                if (f['line[' + lino + '][billtime]'].value) {
                    continue;
                }
                f['line[' + lino + '][chargecat]'].selectedIndex = f.form_charge_category.selectedIndex;
            }
        }
    }

    // This is specific to IPPF and Suriname.
    function check_referrals() {
<?php if (!empty($GLOBALS['gbl_menu_surinam_insurance'])) { ?>
        var msg = '';
        var f = document.forms[0];
        var services_needing_referral = '';
        for (var lino = 0; f['line[' + lino + '][chargecat]']; ++lino) {
            var pfx = 'line[' + lino + ']';
            var cust = f[pfx+'[chargecat]'].value;
            var price = parseFloat(f[pfx + '[price]'].value);
            if (isNaN(price)) {
                price = 0;
            }
            if ((cust == 'SZF' || cust == 'KL-0098') && f[pfx+'[code_type]'].value != 'PROD' && price != 0) {
                services_needing_referral += f[pfx+'[code_type]'].value + ':' + f[pfx+'[code]'].value + ';';
            }
        }
        if (services_needing_referral) {
            top.restoreSession();
            $.ajax({
                dataType: "json",
                async: false, // We cannot continue without an answer.
                url: "<?php echo $GLOBALS['webroot']; ?>/library/ajax/check_szf_referrals_ajax.php",
                data: {
                    "pid": <?php echo intval($patient_id); ?>,
                    "encounter": <?php echo intval($encounter_id); ?>,
                    "services": services_needing_referral
                },
                success: function (jsondata, textstatus) {
                    msg = jsondata['message'];
                }
            });
        }
        if (msg && !confirm(msg)) {
            return false;
        }
<?php } ?>
        return true;
    }

    // This is specific to IPPF and the NetSuite project.
    function check_giftcards() {
<?php if (empty($GLOBALS['gbl_menu_netsuite'])) { ?>
        return true;
<?php } else { ?>
        var f = document.forms[0];
        // If there is no gift card customer return true.
        var gc_customer_exists = false;
        for (lino = 0; f['line[' + lino + '][chargecat]']; ++lino) {
            var chargecat = f['line[' + lino + '][chargecat]'].value;
            if (
                1 == 2
    <?php
    $lres = sqlStatement(
        "SELECT option_id FROM list_options WHERE " .
        "list_id = 'chargecats' AND activity = 1 AND notes LIKE '%GIFTCARD=Y%' ORDER BY seq, title"
    );
    while ($lrow = sqlFetchArray($lres)) {
        echo "                || chargecat == " . js_escape($lrow['option_id']) . "\n";
    }
    ?>
            ) {
                gc_customer_exists = true;
            }
        }
        if (!gc_customer_exists) {
            return true;
        }
        // If there is a gift card payment method in the form return true.
        for (lino = 0; f['payment[' + lino + '][method]']; ++lino) {
            var method = f['payment[' + lino + '][method]'].value;
            if (
                1 == 2
    <?php
    $lres = sqlStatement(
        "SELECT option_id FROM list_options WHERE " .
        "list_id = 'paymethod' AND activity = 1 AND notes LIKE '%GIFTCARD=Y%' ORDER BY seq, title"
    );
    while ($lrow = sqlFetchArray($lres)) {
        echo "                || method == " . js_escape($lrow['option_id']) . "\n";
    }
    ?>
            ) {
                if (!f['payment[' + lino + '][refno]'].value) {
                  // There is a gift card payment method but no gift card ID is entered.
                  alert(<?php echo xlj("Enter Gift Card number in the Reference field"); ?>);
                  return false;
                }
                return true; // There is a gift card payment method or no such methods exist.
            }
        }
        // There is a gift card customer but no gift card payment method.
        alert(<?php echo xlj("Gift Card Customer requires at least one Gift Card Payment Method"); ?>);
        return false;
<?php } ?>
    }

    function validate() {
        var f = document.forms[0];
        var missingtypeamt = false;
        var missingtypeany = false;
        for (lino = 0; f['line[' + lino + '][memo]']; ++lino) {
            if (f['line[' + lino + '][memo]'].selectedIndex == 0 && f['line[' + lino + '][billtime]'].value == '') {
                missingtypeany = true;
                if (parseFloat(f['line[' + lino + '][adjust]'].value) != 0) {
                    missingtypeamt = true;
                }
            }
        }
<?php if (false /* adjustments_indicate_insurance */) { ?>
        if (missingtypeany) {
            alert(<?php echo xlj('Adjustment type is required for every line item.') ?>);
            return false;
        }
<?php } else { ?>
        if (missingtypeamt) {
            alert(<?php echo xlj('Adjustment type is required for each line with an adjustment.') ?>);
            return false;
        }
<?php } ?>
        if (!check_referrals()) {
            return false;
        }
        if (!check_giftcards()) {
            return false;
        }
        top.restoreSession();
        return true;
    }

</script>
<?php
    // TBD: Not sure this will be used here.
    $arrOeUiSettings = array(
        'heading_title' => xl('Patient Checkout'),
        'include_patient_name' => true,// use only in appropriate pages
        'expandable' => false,
        'expandable_files' => array(),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
        'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>

<body>

<?php
echo "<form method='post' action='pos_checkout.php?rde=" . attr_url($rapid_data_entry);
if ($encounter_id) {
    echo "&enid=" . attr_url($encounter_id);
}
if (!empty($_GET['framed'])) {
    echo '&framed=1';
}
echo "' onsubmit='return validate()'>\n";
echo "<input type='hidden' name='form_pid' value='" . attr($patient_id) . "' />\n";
?>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table cellspacing='5' id='paytable' width='85%'>
<?php
$inv_date      = '';
$inv_provider  = 0;
$inv_payer     = 0;
$gcac_related_visit = false;
$gcac_service_provided = false;

// This is set by write_form_headers() when the encounter is known.
$encounter_date = '';

// This to save copays from the billing table.
$aCopays = array();

$lino = 0;

$query = "SELECT id, date, code_type, code, modifier, code_text, " .
    "provider_id, payer_id, units, fee, encounter, billed, bill_date, chargecat " .
    "FROM billing WHERE pid = ? AND encounter = ? AND activity = 1 AND " .
    "code_type != 'TAX' ORDER BY id ASC";
$bres = sqlStatement($query, array($patient_id, $encounter_id));

$query = "SELECT s.sale_id, s.sale_date, s.prescription_id, s.fee, s.quantity, " .
    "s.encounter, s.drug_id, s.billed, s.bill_date, s.selector, s.chargecat, d.name, r.provider_id " .
    "FROM drug_sales AS s " .
    "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
    "LEFT OUTER JOIN prescriptions AS r ON r.id = s.prescription_id " .
    "WHERE s.pid = ? AND s.encounter = ? " .
    "ORDER BY s.sale_id ASC";
$dres = sqlStatement($query, array($patient_id, $encounter_id));

// Process billing table items.  Note this includes co-pays.
// Items that are not allowed to have a fee are skipped.
//
while ($brow = sqlFetchArray($bres)) {
    $thisdate = substr($brow['date'], 0, 10);
    $code_type = $brow['code_type'];
    $inv_payer = $brow['payer_id'];
    if (!$inv_date || $inv_date < $thisdate) {
        $inv_date = $thisdate;
    }
    // Co-pays are saved for later.
    if ($code_type == 'COPAY') {
        $aCopays[] = $brow;
        continue;
    }

    $billtime = $brow['billed'] ? $brow['bill_date'] : '';

    // Collect tax rates, related code and provider ID.
    $taxrates = '';
    $related_code = '';
    if (!empty($code_types[$code_type]['fee'])) {
        $query = "SELECT taxrates, related_code FROM codes WHERE code_type = ? AND " .
            "code = ? AND ";
        $binds = array($code_types[$code_type]['id'], $brow['code']);
        if ($brow['modifier']) {
            $query .= "modifier = ?";
            $binds[] = $brow['modifier'];
        } else {
            $query .= "(modifier IS NULL OR modifier = '')";
        }
        $query .= " LIMIT 1";
        $tmp = sqlQuery($query, $binds);
        $taxrates = $tmp['taxrates'];
        $related_code = $tmp['related_code'];
        markTaxes($taxrates);
    }

    // Write the line item if it allows fees or is not a diagnosis.
    if (!empty($code_types[$code_type]['fee']) || empty($code_types[$code_type]['diag'])) {
        write_form_line(
            $code_type,
            $brow['code'],
            $brow['id'],
            $thisdate,
            ucfirst(strtolower($brow['code_text'])),
            $brow['fee'],
            $brow['units'],
            $taxrates,
            $billtime,
            $brow['chargecat']
        );
    }

    // Custom logic for IPPF to determine if a GCAC issue applies.
    if ($GLOBALS['ippf_specific'] && $related_code) {
        $relcodes = explode(';', $related_code);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }
            list($codetype, $code) = explode(':', $codestring);
            if ($codetype !== 'IPPF2') {
                continue;
            }
            if (preg_match('/^211/', $code)) {
                $gcac_related_visit = true;
                if (
                    preg_match('/^211313030110/', $code) // Medical
                    || preg_match('/^211323030230/', $code) // Surgical
                    || preg_match('/^211403030110/', $code) // Incomplete Medical
                    || preg_match('/^211403030230/', $code) // Incomplete Surgical
                ) {
                    $gcac_service_provided = true;
                }
            }
        }
    }
}

// Process drug sales / products.
//
while ($drow = sqlFetchArray($dres)) {
    if ($encounter_id && $drow['encounter'] != $encounter_id) {
        continue;
    }
    $thisdate = $drow['sale_date'];
    if (!$encounter_id) {
        $encounter_id = $drow['encounter'];
    }
    if (!$inv_provider && !empty($arr_users[$drow['provider_id']])) {
        $inv_provider = $drow['provider_id'] + 0;
    }
    if (!$inv_date || $inv_date < $thisdate) {
        $inv_date = $thisdate;
    }
    $billtime = $drow['billed'] ? $drow['bill_date'] : '';

    // Accumulate taxes for this product.
    $tmp = sqlQuery(
        "SELECT taxrates FROM drug_templates WHERE drug_id = ? ORDER BY selector LIMIT 1",
        array($drow['drug_id'])
    );
    $taxrates = $tmp['taxrates'];
    markTaxes($taxrates);

    $tmpname = $drow['name'];
    if ($tmpname !== $drow['selector']) {
        $tmpname .= ' / ' . $drow['selector'];
    }
    $units = $drow['quantity'] / FeeSheet::getBasicUnits($drow['drug_id'], $drow['selector']);

    write_form_line(
        'PROD',
        $drow['drug_id'],
        $drow['sale_id'],
        $thisdate,
        $tmpname,
        $drow['fee'],
        $units,
        $taxrates,
        $billtime,
        $drow['chargecat']
    );
}

// Line for total charges.
$totalchg = formatMoneyNumber($totalchg);
echo " <tr>\n";
echo "  <td class='bold' colspan='" . (!empty($GLOBALS['gbl_checkout_charges']) ? 4 : 3) .
     "' align='right'>" . xlt('Total Charges This Visit') . "</td>\n";
echo "  <td class='text' align='right'><input type='text' name='totalcba' " .
     "value='" . attr($totalchg) . "' size='6' maxlength='8' " .
     "style='text-align:right;background-color:transparent' readonly";
echo "></td>\n";
if (!$TAXES_AFTER_ADJUSTMENT) {
    for ($i = 0; $i < count($taxes); ++$i) {
        echo "  <td class='text' align='right'><input type='text' name='totaltax[$i]' " .
             "value='0.00' size='6' maxlength='8' " .
             "style='text-align:right;background-color:transparent' readonly";
        echo "></td>\n";
    }
}
if (!empty($GLOBALS['gbl_charge_categories'])) {
    echo "  <td class='text' align='right'>&nbsp;</td>\n"; // Empty space in charge category column.
}
if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
    // Note $totalchg is the total of charges before adjustments, and the following
    // field will be recomputed at onload time and as adjustments are entered.
    echo "  <td class='text' align='right'>&nbsp;</td>\n"; // Empty space in adjustment type column.
    echo "  <td class='text' align='right'>&nbsp;</td>\n"; // TBD: Total adjustments can go here.
}
if ($TAXES_AFTER_ADJUSTMENT) {
    for ($i = 0; $i < count($taxes); ++$i) {
        echo "  <td class='text' align='right'><input type='text' name='totaltax[$i]' " .
             "value='0.00' size='6' maxlength='8' " .
             "style='text-align:right;background-color:transparent' readonly";
        echo "></td>\n";
    }
}
echo "  <td class='text' align='right'><input type='text' name='totalchg' " .
     "value='" . attr($totalchg) . "' size='6' maxlength='8' " .
     "style='text-align:right;background-color:transparent' readonly";
echo "></td>\n";
echo " </tr>\n";
?>

 <tr>
  <td class='title' colspan='<?php echo 5 + $num_optional_columns; ?>'
   style='border-top:1px solid black; padding-top:5pt;'>
   <b><?php echo xlt('Payments'); ?></b>
  </td>
 </tr>

<?php
// Start new section for payments.
echo "   <td class='bold' colspan='$form_num_type_columns'>" . xlt('Type') . "</td>\n";
echo "   <td class='bold' colspan='$form_num_method_columns'>" . xlt('Payment Method') . "</td>\n";
echo "   <td class='bold' colspan='$form_num_ref_columns'>" . xlt('Reference') . "</td>\n";
echo "   <td class='bold' colspan='$form_num_amount_columns' align='right' nowrap>" . xlt('Payment Amount') . "</td>\n";
echo "  </tr>\n";

$lino = 0;

// Write co-pays.
foreach ($aCopays as $brow) {
    $thisdate = substr($brow['date'], 0, 10);
    write_old_payment_line(
        xl('Prepayment'),
        $thisdate,
        $brow['code_text'],
        '',
        0 - $brow['fee']
    );
}

// Write any adjustments left in the aAdjusts array. This should only happen if
// there was an invoice-level discount in a prior checkout of this encounter.
foreach ($aAdjusts as $arow) {
    $memo = $arow['memotitle'];
    if ($arow['adj_amount'] == 0 && $memo === '') {
        continue;
    }
    $reference = $arow['reference'];
    write_old_payment_line(
        xl('Adjustment'),
        $thisdate,
        $memo,
        $reference,
        $arow['adj_amount']
    );
}

// Write ar_activity payments.
$ares = sqlStatement(
    "SELECT " .
    "a.payer_type, a.pay_amount, a.memo, s.session_id, s.reference, s.check_date " .
    "FROM ar_activity AS a " .
    "LEFT JOIN ar_session AS s ON s.session_id = a.session_id WHERE " .
    "a.pid = ? AND a.encounter = ? AND a.deleted IS NULL AND a.pay_amount != 0 " .
    "ORDER BY s.check_date, a.sequence_no",
    array($patient_id, $encounter_id)
);
while ($arow = sqlFetchArray($ares)) {
    $memo = $arow['memo'];
    $reference = $arow['reference'];
    if (empty($arow['session_id'])) {
        $atmp = explode(' ', $memo, 2);
        $memo = $atmp[0];
        $reference = $atmp[1];
    }
    $rowtype = $arow['payer_type'] ? xl('Insurance payment') : xl('Prepayment');
    write_old_payment_line(
        $rowtype,
        $thisdate,
        $memo,
        $reference,
        $arow['pay_amount']
    );
}

// Line for total payments.
echo " <tr id='totalpay'>\n";
echo "  <td class='bold' colspan='$form_num_type_columns'><a href='#' onclick='return addPayLine()'>[" . xlt('Add Row') . "]</a></td>\n";
echo "  <td class='bold' colspan='" . ($form_num_method_columns + $form_num_ref_columns) .
     "' align='right'>" . xlt('Total Payments This Visit') . "</td>\n";
echo "  <td class='text' align='right' colspan='$form_num_amount_columns'><input type='text' name='form_totalpay' " .
     "value='' size='6' maxlength='8' " .
     "style='text-align:right;background-color:transparent' readonly";
echo "></td>\n";
echo " </tr>\n";

// Line for Difference.
echo "  <tr>\n";
echo "   <td class='text' colspan='" . (5 + $num_optional_columns) .
     "' style='border-top:1px solid black; font-size:1pt; padding:0px;'>&nbsp;</td>\n";
echo "  </tr>\n";

echo " <tr";
// Hide this if only showing line item adjustments.
if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
    echo " style='display:none'";
}
echo ">\n";
echo "  <td class='title' colspan='" . ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) .
     "' align='right'><b>" . xlt('Difference') . "</b></td>\n";
echo "  <td class='text' align='right' colspan='$form_num_amount_columns'><input type='text' name='form_difference' " .
     "value='' size='6' maxlength='8' " .
     "style='text-align:right;background-color:transparent' readonly";
echo "></td>\n";
echo " </tr>\n";

if ($encounter_id) {
    $erow = sqlQuery(
        "SELECT provider_id FROM form_encounter WHERE pid = ? AND encounter = ? " .
        "ORDER BY id DESC LIMIT 1",
        array($patient_id, $encounter_id)
    );
    $inv_provider = $erow['provider_id'] + 0;
}

// Line for Discount.
echo " <tr";
// Hide this if only showing line item adjustments.
if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
    echo " style='display:none'";
}
echo ">\n";
echo "  <td class='bold' colspan='" . ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) .
     "' align='right'>";
if (AclMain::aclCheckCore('acct', 'disc') || AclMain::aclCheckCore('admin', 'super')) {
    echo "<a href='#' onclick='return computeDiscount()'>[" . xlt('Compute') . "]</a> <b>";
    echo xlt('Discount/Adjustment') . "</b></td>\n";
    echo "  <td class='text' align='right' colspan='$form_num_amount_columns'>" .
         "<input type='text' name='form_discount' " .
         "value='' size='6' maxlength='8' onkeyup='billingChanged()' " .
         "style='text-align:right' />";
} else {
    echo "" . xlt('Discount/Adjustment') . "</td>\n";
    echo "  <td class='text' align='right' colspan='$form_num_amount_columns'>" .
         "<input type='text' name='form_discount' value='' size='6' " .
         "style='text-align:right;background-color:transparent' readonly />";
}
echo "</td>\n";
echo " </tr>\n";

// Line for Balance Due
echo " <tr>\n";
echo "  <td class='title' colspan='" . ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) .
     "' align='right'>" . xlt('Balance Due') . "</td>\n";
echo "  <td class='text' align='right' colspan='$form_num_amount_columns'>" .
     "<input type='text' name='form_balancedue' " .
     "value='' size='6' maxlength='8' " .
     "style='text-align:right;background-color:transparent' readonly";
echo "></td>\n";
echo " </tr>\n";
?>

    <tr>
        <td class='bold' colspan='<?php echo ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) +
            (empty($GLOBALS['gbl_charge_categories']) ? 0 : 1); ?>' align='right'>
            <label class="control-label" for="form_date"><?php echo xlt('Posting Date'); ?>:</label>
        </td>
        <td class='text' colspan='<?php echo $form_num_amount_columns; ?>' align='right'>
            <input type='text' class='form-control datepicker' id='form_date' name='form_date'
              title='yyyy-mm-dd date of service'
              value='<?php echo attr($encounter_date) ?>' />
        </td>
    </tr>

<?php
// A current invoice reference number may be present if there was a previous checkout.
$tmprow = sqlQuery(
    "SELECT invoice_refno FROM form_encounter WHERE " .
    "pid = ? AND encounter = ?",
    array($patient_id, $encounter_id)
);
$current_irnumber = $tmprow['invoice_refno'];

if (!$current_irnumber) {
    // If this user has a non-empty irnpool assigned, show the pending
    // invoice reference number.
    $irnumber = BillingUtilities::getInvoiceRefNumber();
    if (!empty($irnumber)) {
        ?>
    <tr>
        <td class='bold' colspan='<?php echo ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) +
            (empty($GLOBALS['gbl_charge_categories']) ? 0 : 1); ?>' align='right'>
            <?php echo xlt('Tentative Invoice Ref No'); ?>
        </td>
        <td class='text' align='right' colspan='<?php echo $form_num_amount_columns; ?>'>
            <?php echo text($irnumber); ?>
        </td>
    </tr>
        <?php
    } else if (!empty($GLOBALS['gbl_mask_invoice_number'])) {
    // Otherwise if there is an invoice reference number mask, ask for the refno.
        ?>
    <tr>
        <td class='bold' colspan='<?php echo ($form_num_type_columns + $form_num_method_columns + $form_num_ref_columns) +
            (empty($GLOBALS['gbl_charge_categories']) ? 0 : 1); ?>' align='right'>
            <?php echo xlt('Invoice Reference Number'); ?>
        </td>
        <td class='text' align='right' colspan='<?php echo $form_num_amount_columns; ?>'>
            <input type='text' name='form_irnumber' size='10' value=''
                onkeyup='maskkeyup(this,"<?php echo addslashes($GLOBALS['gbl_mask_invoice_number']); ?>")'
                onblur='maskblur(this,"<?php echo addslashes($GLOBALS['gbl_mask_invoice_number']); ?>")'
        />
        </td>
    </tr>
        <?php
    }
}
?>

    <tr>
        <td class='text' colspan='<?php echo 5 + $num_optional_columns; ?>' align='center'>
            &nbsp;<br>
            <input type='submit' name='form_save' value='<?php echo xlt('Save'); ?>'
<?php if ($rapid_data_entry) { ?>
                style='background-color:#cc0000';color:#ffffff'
<?php } ?>
            /> &nbsp;
<?php if (empty($_GET['framed'])) { ?>
            <input type='button' value='Cancel' onclick='window.close()' />
<?php } ?>
            <input type='hidden' name='form_provider'  value='<?php echo attr($inv_provider); ?>' />
            <input type='hidden' name='form_payer'     value='<?php echo attr($inv_payer); ?>' />
            <input type='hidden' name='form_encounter' value='<?php echo attr($encounter_id); ?>' />
            <input type='hidden' name='form_checksum'  value='<?php echo attr($current_checksum); ?>' />
        </td>
    </tr>

</table>

</center>

</form>

<script>

    // Add a line for entering a payment.
    // Declared down here because $form_num_*_columns must be defined.
    var paylino = 0;
    function addPayLine() {
        var table = document.getElementById('paytable');
        for (var i = 0; i < table.rows.length; ++i) {
            if (table.rows[i].id == 'totalpay') {
                var row = table.insertRow(i);
                var cell;
<?php
foreach ($aCellHTML as $ix => $html) {
    echo "    var html = \"$html\";\n";
    echo "    cell = row.insertCell(row.cells.length);\n";
    if ($ix == 0) {
        echo "    cell.colSpan = $form_num_type_columns;\n";
    }
    if ($ix == 1) {
        echo "    cell.colSpan = $form_num_method_columns;\n";
    }
    if ($ix == 2) {
        echo "    cell.colSpan = $form_num_ref_columns;\n";
    }
    if ($ix == 3) {
        echo "    cell.colSpan = $form_num_amount_columns;\n";
    }
    echo "    cell.innerHTML = html.replace(/%d/, paylino);\n";
}
?>
                cell.align = 'right'; // last cell is right-aligned
                ++paylino;
                break;
            }
        }
        return false;
    }


// TBD: Clean up javascript indentation from here on. ////////////////////////////


 // Pop up the Payments window and close this one.
 function payprevious() {
  var width  = 750;
  var height = 550;
  var loc = '../patient_file/front_payment.php?omitenc=' + <?php echo js_url($encounter_id); ?>;
<?php if (empty($_GET['framed'])) { ?>
  opener.parent.left_nav.dlgopen(loc, '_blank', width, height);
  window.close();
<?php } else { ?>
  var tmp = parent.left_nav ? parent.left_nav : parent.parent.left_nav;
  tmp.dlgopen(loc, '_blank', width, height);
<?php } ?>
 }

 discountTypeChanged();
 addPayLine();
 billingChanged();

<?php
if ($alertmsg) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}

if ($gcac_related_visit && !$gcac_service_provided) {
    // Skip this warning if the GCAC visit form is not allowed.
    $grow = sqlQuery(
        "SELECT COUNT(*) AS count FROM layout_group_properties " .
        "WHERE grp_form_id = 'LBFgcac' AND grp_group_id = '' AND grp_activity = 1"
    );
    if (!empty($grow['count'])) { // if gcac is used
        // Skip this warning if referral or abortion in TS.
        $grow = sqlQuery(
            "SELECT COUNT(*) AS count FROM transactions " .
            "WHERE title = 'Referral' AND refer_date IS NOT NULL AND " .
            "refer_date = ? AND pid = ?",
            array($inv_date, $patient_id)
        );
        if (empty($grow['count'])) { // if there is no referral
            $grow = sqlQuery(
                "SELECT COUNT(*) AS count FROM forms " .
                "WHERE pid = ? AND encounter = ? AND " .
                "deleted = 0 AND formdir = 'LBFgcac'",
                array($patient_id, $encounter_id)
            );
            if (empty($grow['count'])) { // if there is no gcac form
                echo " alert(" . xlj('This visit will need a GCAC form, referral or procedure service.') . ");\n";
            }
        }
    }
} // end if ($gcac_related_visit)

if ($GLOBALS['ippf_specific']) {
    // More validation:
    // o If there is an initial contraceptive consult, make sure a LBFccicon form exists with that method on it.
    // o If a LBFccicon form exists with a new method on it, make sure the TS initial consult exists.

    require_once("$srcdir/contraception_billing_scan.inc.php");
    contraception_billing_scan($patient_id, $encounter_id);

    $csrow = sqlQuery(
        "SELECT field_value FROM shared_attributes WHERE pid = ? AND encounter = ? AND field_id = 'cgen_MethAdopt'",
        array($patient_id, $encounter_id)
    );
    $csmethod = empty($csrow['field_value']) ? '' : $csrow['field_value'];

    if (($csmethod || $contraception_billing_code) && $csmethod != "IPPFCM:$contraception_billing_code") {
        $warningMessage = xl('Warning') . ': ';
        if (!$csmethod) {
            $warningMessage .= xl('there is a contraception service but no contraception form new method');
        } else if (!$contraception_billing_code) {
            $warningMessage .= xl('there is a contraception form new method but no contraception service');
        } else {
            $warningMessage .= xl('new method in contraception form does not match the contraception service');
        }
        echo " alert(" . js_escape($warningMessage) . ");\n";
    }
}
?>
        </script>
    </body>
</html>
