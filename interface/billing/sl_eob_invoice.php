<?php

/**
 * This provides for manual posting of EOBs.  It is invoked from
 * sl_eob_search.php.  For automated (X12 835) remittance posting
 * see sl_eob_process.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/forms.inc");
require_once("../../custom/code_types.inc.php");
require_once "$srcdir/user.inc";
require_once("$srcdir/payment.inc.php");

use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

$debug = 0; // set to 1 for debugging mode
$save_stay = (!empty($_REQUEST['form_save']) && ($_REQUEST['form_save'] == '1')) ? true : false;
$from_posting = (0 + ($_REQUEST['isPosting'] ?? null)) ? 1 : 0;
$g_posting_adj_disable = $GLOBALS['posting_adj_disable'] ? 'checked' : '';
if ($from_posting) {
    $posting_adj_disable = prevSetting('sl_eob_search.', 'posting_adj_disable', 'posting_adj_disable', $g_posting_adj_disable);
} else {
    $posting_adj_disable = $g_posting_adj_disable;
}

// If we permit deletion of transactions.  Might change this later.
$ALLOW_DELETE = true;

$info_msg = "";

// Format money for display.
//
function bucks($amount)
{
    if ($amount) {
        return sprintf("%.2f", $amount);
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'opener', 'no_dialog']); ?>
    <title><?php echo xlt('EOB Posting - Invoice') ?></title>
    <script>

        const adjDisable = <?php echo js_escape($posting_adj_disable); ?>;
        // An insurance radio button is selected.
        function setins(istr) {
            return true;
        }

        function goEncounterSummary(e, pid) {
            if(pid) {
                if(typeof opener.toEncSummary  === 'function') {
                    opener.toEncSummary(e, pid);
                }
            }
            doClose();
        }

        function doClose() {
            window.close();
        }

        // Compute an adjustment that writes off the balance:
        function writeoff(code) {
            const f = document.forms[0];
            const belement = f['form_line[' + code + '][bal]'];
            const pelement = f['form_line[' + code + '][pay]'];
            const aelement = f['form_line[' + code + '][adj]'];
            const relement = f['form_line[' + code + '][reason]'];
            const tmp = belement.value - pelement.value;
            aelement.value = Number(tmp).toFixed(2);
            if (aelement.value && !relement.value) {
                relement.selectedIndex = 1;
            }
            return false;
        }

        // Onsubmit handler.  A good excuse to write some JavaScript.
        function validate(f) {
            let delcount = 0;
            let allempty = true;

            for (let i = 0; i < f.elements.length; ++i) {
                let ename = f.elements[i].name;
                // Count deletes.
                if (ename.substring(0, 9) == 'form_del[') {
                    if (f.elements[i].checked) {
                        ++delcount;
                    }
                    continue;
                }
                let pfxlen = ename.indexOf('[pay]');
                if (pfxlen < 0) {
                    continue
                };
                let pfx = ename.substring(0, pfxlen);
                let code = pfx.substring(pfx.indexOf('[') + 1, pfxlen - 1);
                let cPay = parseFloat(f[pfx + '[pay]'].value).toFixed(2);
                let cAdjust = parseFloat(f[pfx + '[adj]'].value).toFixed(2);

                if ((cPay !== 0) || cAdjust !== 0) {
                    allempty = false;
                }
                if(adjDisable) {
                    if ((cAdjust == 0 && ins_done.value == 'changed')) {
                        allempty = false;
                    }
                }
                if ((cPay !== 0) && isNaN(parseFloat(f[pfx + '[pay]'].value))) {
                    let message = <?php echo xlj('Payment value for code') ?> + " " + code + " " + <?php echo xlj('is not a number') ?>;
                    (async (message, time) => {
                        await asyncAlertMsg(message, time, 'danger', 'lg');
                    })(message, 3000)
                    .then(res => { });
                    return false;
                }
                if ((cAdjust !== 0) && isNaN(parseFloat(f[pfx + '[adj]'].value))) {
                    let message = <?php echo xlj('Adjustment value for code') ?> + " " + code  + " " + <?php echo xlj('is not a number') ?>;
                    (async (message, time) => {
                        await asyncAlertMsg(message, time, 'danger', 'lg');
                    })(message, 3000)
                    .then(res => { });
                    return false;
                }
                if ((cAdjust !== 0) && !f[pfx + '[reason]'].value && !adjDisable) {
                    let message = <?php echo xlj('Please select an adjustment reason for code') ?> + " " + code;
                    (async (message, time) => {
                        await asyncAlertMsg(message, time, 'danger', 'lg');
                    })(message, 3000)
                    .then(res => { });
                    return false;
                }
            // TBD: validate the date format
            }
            // Check if save is clicked with nothing to post.
            if (allempty && delcount === 0) {
                let message = <?php echo xlj('Nothing to Post! Please review entries or use Cancel to exit transaction') ?>;
                (async (message, time) => {
                    await asyncAlertMsg(message, time, 'danger', 'lg');
                })(message, 3000)
                .then(res => { });
                return false;
            }
            // Demand confirmation if deleting anything.
            if (delcount > 0) {
                if (!confirm(<?php echo xlj('Really delete'); ?> + ' ' + delcount +
                    ' ' + <?php echo xlj('transactions'); ?> + '?' +
                    ' ' + <?php echo xlj('This action will be logged'); ?> + '!')
                ) return false;
            }
            return true;
        }

        // Get current date
        function getFormattedToday() {
            let today = new Date();
            let dd = today.getDate();
            let mm = today.getMonth() + 1; //January is 0!
            let yyyy = today.getFullYear();
            if (dd < 10) {
                dd = '0' + dd;
            }
            if (mm < 10) {
                mm = '0' + mm;
            }
            return (yyyy + '-' + mm + '-' + dd);
        }

        // Update Payment Fields
        function updateFields(payField, adjField, balField, coPayField, isFirstProcCode) {
            let payAmount = 0.0;
            let adjAmount = 0.0;
            let balAmount = 0.0;
            let coPayAmount = 0.0;

            // coPayFiled will be null if there is no co-pay entry in the fee sheet
            if (coPayField) {
                coPayAmount = coPayField.value;
            }

            // if balance field is 0.00, its value comes back as null, so check for nul-ness first
            if (balField) {
                balAmount = (balField.value) ? balField.value : 0;
            }

            if (payField) {
                payAmount = (payField.value) ? payField.value : 0;
            }

            // alert('balance = >' + balAmount +'<  payAmount = ' + payAmount + '  copay = ' + coPayAmount + '  isFirstProcCode = ' + isFirstProcCode);

            // subtract the co-pay only from the first procedure code
            if (isFirstProcCode == 1) {
                balAmount = parseFloat(balAmount) + parseFloat(coPayAmount);
            }

            if (adjDisable) {
                return;
            }

            adjAmount = balAmount - payAmount;
            // Assign rounded adjustment value back to TextField
            adjField.value = adjAmount = Math.round(adjAmount * 100) / 100;
        }

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        $("#ins_done").on("change", function() {
            $("#ins_done").val('changed');
        });

    </script>
    <style>
        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left !Important;
            }
        }

        .table {
            margin: auto;
            width: 99%;
        }

        .table > tbody > tr > td {
            border-top: none;
        }

        .last_detail {
            border-bottom: 1px var(--black) solid;
            margin-top: 2px;
        }

        @media (min-width: 992px) {
            .modal-lg {
                width: 1000px !Important;
            }
        }
    </style>
</head>
<body>
<?php
$trans_id = (int) $_GET['id'];
if (!$trans_id) {
    die(xlt("You cannot access this page directly."));
}

// A/R case, $trans_id matches form_encounter.id.
$ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname FROM form_encounter AS e, patient_data AS p WHERE e.id = ? AND p.pid = e.pid", array($trans_id));
if (empty($ferow)) {
    die("There is no encounter with form_encounter.id = '" . text($trans_id) . "'.");
}
$patient_id = (int) $ferow['pid'];
$encounter_id = (int) $ferow['encounter'];
$svcdate = substr($ferow['date'], 0, 10);
$form_payer_id = (!empty($_POST['form_payer_id'])) ? (0 + $_POST['form_payer_id']) : 0;
$form_reference = $_POST['form_reference'] ?? null;
$form_check_date   = fixDate(($_POST['form_check_date'] ?? ''), date('Y-m-d'));
$form_deposit_date = fixDate(($_POST['form_deposit_date'] ?? ''), $form_check_date);
$form_pay_total = (!empty($_POST['form_pay_total'])) ? (0 + $_POST['form_pay_total']) : 0;

$payer_type = 0;
if (preg_match('/^Ins(\d)/i', ($_POST['form_insurance'] ?? ''), $matches)) {
    $payer_type = $matches[1];
}

if (!empty($_POST['form_save']) || !empty($_POST['form_cancel']) || !empty($_POST['isLastClosed']) || !empty($_POST['billing_note'])) {
    if (!empty($_POST['form_save'])) {
        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        if ($debug) {
            echo "<p><b>" . xlt("This module is in test mode. The database will not be changed.") . "</b><p>\n";
        }

        $session_id = SLEOB::arGetSession($form_payer_id, $form_reference, $form_check_date, $form_deposit_date, $form_pay_total);
// The sl_eob_search page needs its invoice links modified to invoke
// javascript to load form parms for all the above and submit.
// At the same time that page would be modified to work off the
// openemr database exclusively.
// And back to the sl_eob_invoice page, I think we may want to move
// the source input fields from row level to header level.

// Handle deletes. row_delete() is borrowed from deleter.php.
        if ($ALLOW_DELETE && !$debug) {
            if (!empty($_POST['form_del']) && is_array($_POST['form_del'])) {
                foreach ($_POST['form_del'] as $arseq => $dummy) {
                    row_modify(
                        "ar_activity",
                        "deleted = NOW()",
                        "pid = '" . add_escape_custom($patient_id) .
                        "' AND encounter = '" . add_escape_custom($encounter_id) .
                        "' AND sequence_no = '" . add_escape_custom($arseq) .
                        "' AND deleted IS NULL"
                    );
                }
            }
        }

        $paytotal = 0;
        foreach ($_POST['form_line'] as $code => $cdata) {
            $thispay = trim($cdata['pay']);
            $thisadj = trim($cdata['adj']);
            $thisins = trim($cdata['ins']);
            $thiscodetype = trim($cdata['code_type']);
            $reason = $cdata['reason'];

// Get the adjustment reason type.  Possible values are:
// 1 = Charge adjustment
// 2 = Coinsurance
// 3 = Deductible
// 4 = Other pt resp
// 5 = Comment
            $reason_type = '1';
            if ($reason) {
                $tmp = sqlQuery("SELECT option_value FROM list_options WHERE list_id = 'adjreason' AND activity = 1 AND option_id = ?", array($reason));
                if (empty($tmp['option_value'])) {
// This should not happen but if it does, apply old logic.
                    if (preg_match("/To copay/", $reason)) {
                        $reason_type = 2;
                    } elseif (preg_match("/To ded'ble/", $reason)) {
                        $reason_type = 3;
                    }
                    $info_msg .= xl("No adjustment reason type found for") . " \"$reason\". ";
                } else {
                    $reason_type = $tmp['option_value'];
                }
            }

            if (!$thisins) {
                $thisins = 0;
            }

            if (0.0 + $thispay) {
                SLEOB::arPostPayment($patient_id, $encounter_id, $session_id, $thispay, $code, $payer_type, '', $debug, '', $thiscodetype);
                $paytotal += $thispay;
            }

// Be sure to record adjustment reasons, even for zero adjustments if
// they happen to be comments.
            if (
                (0.0 + $thisadj) ||
                ($reason && $reason_type == 5) ||
                ($reason && ($reason_type > 1 && $reason_type < 6))
            ) {
// "To copay" and "To ded'ble" need to become a comment in a zero
// adjustment, formatted just like sl_eob_process.php.
                if ($reason_type == '2') {
                    $reason = $_POST['form_insurance'] . " coins: $thisadj";
                    $thisadj = 0;
                } elseif ($reason_type == '3') {
                    $reason = $_POST['form_insurance'] . " dedbl: $thisadj";
                    $thisadj = 0;
                } elseif ($reason_type == '4') {
                    $reason = $_POST['form_insurance'] . " ptresp: $thisadj $reason";
                    $thisadj = 0;
                } elseif ($reason_type == '5') {
                    $reason = $_POST['form_insurance'] . " note: $thisadj $reason";
                    $thisadj = 0;
                } else {
// An adjustment reason including "Ins" is assumed to be assigned by
// insurance, and in that case we identify which one by appending
// Ins1, Ins2 or Ins3.
                    if (strpos(strtolower($reason), 'ins') != false) {
                        $reason .= ' ' . $_POST['form_insurance'];
                    }
                }
                SLEOB::arPostAdjustment($patient_id, $encounter_id, $session_id, $thisadj, $code, $payer_type, $reason, $debug, '', $thiscodetype);
            }
        }

// Maintain which insurances are marked as finished.

        $form_done = 0 + $_POST['form_done'];
        $form_stmt_count = 0 + $_POST['form_stmt_count'];
        sqlStatement("UPDATE form_encounter SET last_level_closed = ?, stmt_count = ? WHERE pid = ? AND encounter = ?", array($form_done, $form_stmt_count, $patient_id, $encounter_id));

        if (!empty($_POST['form_secondary'])) {
            SLEOB::arSetupSecondary($patient_id, $encounter_id, $debug);
        }
        echo "<script>\n";
        echo " if (opener.document.forms[0] != undefined) {\n";
        echo "   if (opener.document.forms[0].form_amount) {\n";
        echo "     var tmp = opener.document.forms[0].form_amount.value - " . attr($paytotal) . ";\n";
        echo "     opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
        echo "   }\n";
        echo " }\n";
    } else {
        echo "<script>\n";
    }
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }
    if (!$debug && !$save_stay && !$_POST['isLastClosed']) {
        echo "doClose();\n";
    }
    if (!$debug && ($save_stay || $_POST['isLastClosed'] || $_POST['billing_note'])) {
        if ($_POST['isLastClosed']) {
            // save last closed level
            $form_done = 0 + $_POST['form_done'];
            $form_stmt_count = 0 + $_POST['form_stmt_count'];
            sqlStatement("UPDATE form_encounter SET last_level_closed = ?, stmt_count = ? WHERE pid = ? AND encounter = ?", array($form_done, $form_stmt_count, $patient_id, $encounter_id));
            // also update billing for aging
            sqlStatement("UPDATE billing SET bill_date = ? WHERE pid = ? AND encounter = ?", array($form_deposit_date, $patient_id, $encounter_id));
            if (!empty($_POST['form_secondary'])) {
                SLEOB::arSetupSecondary($patient_id, $encounter_id, $debug);
            }
        }

        if ($_POST['billing_note']) {
            // save last closed level
            sqlStatement("UPDATE form_encounter SET billing_note = ? WHERE pid = ? AND encounter = ?", array($_POST['billing_note'], $patient_id, $encounter_id));
        }

        // will reload page w/o reposting
        echo "location.replace(location)\n";
    }
    echo "</script>\n";
    if (!$save_stay && !$_POST['isLastClosed']) {
        exit();
    }
}

// Get invoice charge details.
$codes = InvoiceSummary::arGetInvoiceSummary($patient_id, $encounter_id, true);
$pdrow = sqlQuery("select billing_note from patient_data where pid = ? limit 1", array($patient_id));
$bnrow = sqlQuery("select billing_note from form_encounter where pid = ? AND encounter = ? limit 1", array($patient_id, $encounter_id));
?>

<div class="container-fluid">
    <div class="row">
        <h2><?php echo xlt('EOB Invoice'); ?></h2>
    </div>
    <div class="container-fluid">
        <form class="form" action='sl_eob_invoice.php?id=<?php echo attr_url($trans_id); ?>' method='post' onsubmit='return validate(this)'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
            <input type="hidden" name="isPosting" value="<?php echo attr($from_posting); ?>"/>
            <input type="hidden" name="isLastClosed" value="" />
            <fieldset>
                <legend><?php echo xlt('Invoice Actions'); ?></legend>
                <div class="form-row">
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_name"><?php echo xlt('Patient'); ?>:</label>
                        <input type="text" class="form-control" id='form_name'
                               name='form_name'
                               value="<?php echo attr($ferow['fname']) . ' ' . attr($ferow['mname']) . ' ' . attr($ferow['lname']); ?>"
                               disabled />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_provider"><?php echo xlt('Provider'); ?>:</label>
                        <?php
                        $tmp = sqlQuery("SELECT fname, mname, lname " .
                            "FROM users WHERE id = ?", array($ferow['provider_id']));
                        $provider = text($tmp['fname']) . ' ' . text($tmp['mname']) . ' ' . text($tmp['lname']);
                        $tmp = sqlQuery("SELECT bill_date FROM billing WHERE " .
                            "pid = ? AND encounter = ? AND " .
                            "activity = 1 ORDER BY fee DESC, id ASC LIMIT 1", array($patient_id, $encounter_id));
                        $billdate = substr(($tmp['bill_date'] ?? '' . "Not Billed"), 0, 10);
                        ?>
                        <input type="text" class="form-control" id='form_provider'
                               name='form_provider' value="<?php echo attr($provider); ?>" disabled />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_invoice"><?php echo xlt('Invoice'); ?>:</label>
                        <input type="text" class="form-control" id='form_provider'
                               name='form_provider' value='<?php echo attr($patient_id) . "." . attr($encounter_id); ?>'
                               disabled />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="svc_date"><?php echo xlt('Svc Date'); ?>:</label>
                        <input type="text" class="form-control" id='svc_date' name='form_provider'
                               value='<?php echo attr($svcdate); ?>' disabled />
                    </div>
                    <div class="card bg-light col-lg-4">
                        <div class="card-title mx-auto"><?php echo xlt('Insurance'); ?></div>
                        <?php
                        for ($i = 1; $i <= 3; ++$i) {
                            $payerid = SLEOB::arGetPayerID($patient_id, $svcdate, $i);
                            if ($payerid) {
                                $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = ?", array($payerid));
                                echo "$i: " . $tmp['name'] . "<br />";
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="billing_note"><?php echo xlt('Billing Note'); ?>:</label>
                        <textarea name="billing_note" id="billing_note" class="form-control" cols="5" rows="2"><?php echo text(($pdrow['billing_note'] ?? '')) . "\n" . text(($bnrow['billing_note'] ?? '')); ?></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_stmt_count"><?php echo xlt('Statements Sent'); ?>:</label>
                        <input type='text' name='form_stmt_count' id='form_stmt_count' class="form-control" value='<?php echo attr((0 + $ferow['stmt_count'])); ?>' />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_last_bill"><?php echo xlt('Last Bill Date'); ?>:</label>
                        <input type='text' name="form_last_bill" id='form_last_bill' class="form-control"
                               value ='<?php echo attr($billdate); ?>' disabled />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_reference"><?php echo xlt('Check/EOB No.'); ?>:</label>
                        <input type='text' name='form_reference' id='form_reference' class="form-control" value='' />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_check_date"><?php echo xlt('Check/EOB Date'); ?>:</label>
                        <input type='text' name='form_check_date' id='form_check_date' class='form-control datepicker' value='' />
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="form_deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
                        <input type='text' name='form_deposit_date' id='form_deposit_date' class='form-control datepicker' value='' />
                        <input type='hidden' name='form_payer_id' value='' />
                        <input type='hidden' name='form_orig_reference' value='' />
                        <input type='hidden' name='form_orig_check_date' value='' />
                        <input type='hidden' name='form_orig_deposit_date' value='' />
                        <input type='hidden' name='form_pay_total' value='' />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-lg">
                        <label class="col-form-label" for="type_code"><?php echo xlt('Now posting for'); ?>:</label>
                        <div class="pl-3">
                            <?php
                                $last_level_closed = 0 + $ferow['last_level_closed'];
                            ?>
                            <label class="radio-inline">
                                <input <?php echo $last_level_closed === 0 ? attr('checked') : ''; ?> name='form_insurance' onclick='setins("Ins1")' type='radio'
                                    value='Ins1' /><?php echo xlt('Ins1') ?>
                            </label>
                            <label class="radio-inline">
                                <input <?php echo $last_level_closed === 1 ? attr('checked') : ''; ?> name='form_insurance' onclick='setins("Ins2")' type='radio'
                                    value='Ins2' /><?php echo xlt('Ins2') ?>
                            </label>
                            <label class="radio-inline">
                                <input <?php echo $last_level_closed === 2 ? attr('checked') : ''; ?> name='form_insurance' onclick='setins("Ins3")' type='radio'
                                    value='Ins3' /><?php echo xlt('Ins3') ?>
                            </label>
                            <label class="radio-inline">
                                <input <?php echo $last_level_closed === 3 ? attr('checked') : ''; ?> name='form_insurance' onclick='setins("Pt")' type='radio'
                                    value='Pt' /><?php echo xlt('Patient') ?>
                            </label>
                            <?php
                            // TBD: I think the following is unused and can be removed.
                            ?>
                            <input name='form_eobs' type='hidden' value='<?php echo attr($arrow['shipvia'] ?? '') ?>'/>
                        </div>
                    </div>
                    <div class="form-group col-lg" id='ins_done'>
                        <label class="col-form-label" for=""><?php echo xlt('Done with'); ?>:</label>
                        <a class="btn btn-save bg-light text-primary"
                            onclick="document.forms[0].isLastClosed.value='3'; document.forms[0].submit()"><?php echo xlt("Save Level"); ?>
                        </a>
                        <div class="pl-3">
                            <?php
                            // Write a checkbox for each insurance.  It is to be checked when
                            // we no longer expect any payments from that company for the claim.
                            $last_level_closed = 0 + $ferow['last_level_closed'];
                            foreach (array(0 => 'None', 1 => 'Ins1', 2 => 'Ins2', 3 => 'Ins3') as $key => $value) {
                                if ($key && !SLEOB::arGetPayerID($patient_id, $svcdate, $key)) {
                                    continue;
                                }
                                $checked = ($last_level_closed == $key) ? " checked" : "";
                                echo "<label class='radio-inline'>";
                                echo "<input type='radio' name='form_done' value='" . attr($key) . "'$checked />" . text($value);
                                echo "</label>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group col-lg">
                        <label class="col-form-label" for=""><?php echo xlt('Secondary billing'); ?>:</label>
                        <div class="pl-3">
                            <label class="checkbox-inline">
                                <input name="form_secondary" type="checkbox" value="1" /><?php echo xlt('Needs secondary billing') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Invoice Details'); ?></legend>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th><?php echo xlt('Code') ?></th>
                                <th class="text-left"><?php echo xlt('Charge') ?></th>
                                <th class="text-left"><?php echo xlt('Balance') ?>&nbsp;</th>
                                <th><?php echo xlt('By/Source') ?></th>
                                <th><?php echo xlt('Date') ?></th>
                                <th><?php echo xlt('Pay') ?></th>
                                <th><?php echo xlt('Adjust') ?></th>
                                <th>&nbsp;</th>
                                <th><?php echo xlt('Reason') ?></th>
                                <?php
                                if ($ALLOW_DELETE) { ?>
                                    <th><?php echo xlt('Del') ?></th>
                                    <?php
                                } ?>
                            </tr>
                        </thead>
                        <?php
                        $firstProcCodeIndex = -1;
                        $encount = 0;
                        foreach ($codes as $code => $cdata) {
                            ++$encount;
                            $dispcode = $code;

                            // remember the index of the first entry whose code is not "CO-PAY", i.e. it's a legitimate proc code
                            if ($firstProcCodeIndex == -1 && strcmp($code, "CO-PAY") != 0) {
                                $firstProcCodeIndex = $encount;
                            }

                            // this sorts the details more or less chronologically:
                            ksort($cdata['dtl']);
                            foreach ($cdata['dtl'] as $dkey => $ddata) {
                                $ddate = substr($dkey, 0, 10);
                                if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
                                    $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                                }
                                $tmpchg = "";
                                $tmpadj = "";
                                if (!empty($ddata['chg']) && ($ddata['chg'] != 0)) {
                                    if (isset($ddata['rsn'])) {
                                        $tmpadj = 0 - $ddata['chg'];
                                    } else {
                                        $tmpchg = $ddata['chg'];
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="detail" style="background:<?php echo $dispcode ? 'lightyellow' : ''; ?>"><?php echo text($dispcode); $dispcode = "" ?></td>
                                    <td class="detail"><?php echo text(bucks($tmpchg)); ?></td>
                                    <td class="detail">&nbsp;</td>
                                    <td class="detail">
                                        <?php
                                        if (isset($ddata['plv'])) {
                                            if (!$ddata['plv']) {
                                                echo 'Pt/';
                                            } else {
                                                echo 'Ins' . text($ddata['plv']) . '/';
                                            }
                                        }
                                        echo text($ddata['src'] ?? '');
                                        ?>
                                    </td>
                                    <td class="detail"><?php echo text($ddate); ?></td>
                                    <td class="detail"><?php echo text(bucks($ddata['pmt'] ?? '')); ?></td>
                                    <td class="detail"><?php echo text(bucks($tmpadj)); ?></td>
                                    <td class="detail">&nbsp;</td>
                                    <td class="detail"><?php echo text($ddata['rsn'] ?? ''); ?></td>
                                    <?php
                                    if ($ALLOW_DELETE) { ?>
                                        <td class="detail">
                                            <?php
                                            if (!empty($ddata['arseq'])) { ?>
                                                <input name="form_del[<?php echo attr($ddata['arseq']); ?>]"
                                                       type="checkbox" />
                                                <?php
                                            } else {
                                                ?> &nbsp;
                                                <?php
                                            } ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } // end of prior detail line ?>
                            <tr>
                                <td class="last_detail"><?php echo text($dispcode);
                                    $dispcode = "" ?>
                                </td>
                                <td class="last_detail">&nbsp;</td>
                                <td class="last_detail">
                                    <input name="form_line[<?php echo attr($code); ?>][bal]" type="hidden"
                                           value="<?php echo attr(bucks($cdata['bal'])); ?>" />
                                    <input name="form_line[<?php echo attr($code); ?>][ins]" type="hidden"
                                           value="<?php echo attr($cdata['ins'] ?? ''); ?>" />
                                    <input name="form_line[<?php echo attr($code); ?>][code_type]" type="hidden"
                                           value="<?php echo attr($cdata['code_type'] ?? ''); ?>" /> <?php echo text(sprintf("%.2f", $cdata['bal'])); ?>
                                    &nbsp;
                                </td>
                                <td class="last_detail"></td>
                                <td class="last_detail"></td>
                                <td class="last_detail">
                                    <input name="form_line[<?php echo attr($code); ?>][pay]"
                                           onkeyup="updateFields(document.forms[0]['form_line[<?php echo attr($code); ?>][pay]'], document.forms[0]['form_line[<?php echo attr($code); ?>][adj]'], document.forms[0]['form_line[<?php echo attr($code); ?>][bal]'], document.forms[0]['form_line[CO-PAY][bal]'], <?php echo ($firstProcCodeIndex == $encount) ? 1 : 0 ?>)"
                                           onfocus="this.select()" autofocus size="10" type="text" class="form-control"
                                           value="0.00" />
                                </td>
                                <td class="last_detail">
                                    <input name="form_line[<?php echo attr($code); ?>][adj]" size="10" type="text"
                                           class="form-control"
                                           value='<?php echo attr((!empty($totalAdjAmount)) ? $totalAdjAmount : '0.00'); ?>'
                                           onclick="this.select()" />
                                </td>
                                <td class="last_detail text-center">
                                    <a href="#" class="text-decoration-none" onclick="return writeoff(<?php echo attr_js($code); ?>)">WO</a>
                                </td>
                                <td class="last_detail">
                                    <select class="form-control" name="form_line[<?php echo attr($code); ?>][reason]">
                                        <?php
                                        // Adjustment reasons are now taken from the list_options table.
                                        echo "    <option value=''></option>\n";
                                        $ores = sqlStatement("SELECT option_id, title, is_default FROM list_options " .
                                            "WHERE list_id = 'adjreason' AND activity = 1 ORDER BY seq, title");
                                        while ($orow = sqlFetchArray($ores)) {
                                            echo "    <option value='" . attr($orow['option_id']) . "'";
                                            if ($orow['is_default']) {
                                                echo " selected";
                                            }
                                            echo ">" . text($orow['title']) . "</option>\n";
                                        }
                                        ?>
                                    </select>
                                    <?php
                                    // TBD: Maybe a comment field would be good here, for appending
                                    // to the reason.
                                    ?>
                                </td>
                                <?php if ($ALLOW_DELETE) { ?>
                                    <td class="last_detail">&nbsp;</td>
                                <?php } ?>
                            </tr>
                        <?php } // end of code ?>
                    </table>
                </div>
            </fieldset>
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
            <div class="form-group col-lg clearfix">
                <div class="col-sm-12 text-left position-override" id="search-btn">
                    <div class="btn-group" role="group">
                        <!-- @todo leave as I may still use sjp 08/2020 -->
                        <!--<button type='submit' class="btn btn-primary btn-save" name='form_save' id="btn-save-stay"
                            onclick="this.value='1';"><?php /*echo xlt("Save Current"); */?></button>-->
                        <button type='submit' class="btn btn-primary btn-save" name='form_save' id="btn-save"
                            onclick="this.value='2';"><?php echo xlt("Save"); ?></button>
                        <button type='button' class="btn btn-secondary btn-cancel" name='form_cancel'
                            id="btn-cancel" onclick='doClose()'><?php echo xlt("Close"); ?></button>
                    </div>
                    <?php if ($from_posting) { ?>
                        <button type='button' class="btn btn-secondary btn-view float-right" name='form_goto' id="btn-goto"
                            onclick="goEncounterSummary(event, <?php echo attr_js($patient_id) ?>)"><?php echo xlt("Past Encounters"); ?></button>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
</div><!--End of container div-->
<?php if ($from_posting) { ?>
<script>
    var f1 = opener.document.forms[0];
    var f2 = document.forms[0];
    if (f1.form_source) {
        <?php
        // These support creation and lookup of ar_session table entries:
        echo "  f2.form_reference.value         = f1.form_source.value;\n";
        echo "  f2.form_check_date.value        = f1.form_paydate.value;\n";
        echo "  //f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
        echo "  if (f1.form_deposit_date.value != '')\n";
        echo "     f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
        echo "  else\n";
        echo "     f2.form_deposit_date.value      = getFormattedToday();\n";
        echo "  f2.form_payer_id.value          = f1.form_payer_id.value;\n";
        echo "  f2.form_pay_total.value         = f1.form_amount.value;\n";
        echo "  f2.form_orig_reference.value    = f1.form_source.value;\n";
        echo "  f2.form_orig_check_date.value   = f1.form_paydate.value;\n";
        echo "  f2.form_orig_deposit_date.value = f1.form_deposit_date.value;\n";

        // While I'm thinking about it, some notes about eob sessions.
        // If they do not have all of the session key fields in the search
        // page, then show a warning at the top of the invoice page.
        // Also when they go to save the invoice page and a session key
        // field has changed, alert them to that and allow a cancel.

        // Another point... when posting EOBs, the incoming payer ID might
        // not match the payer ID for the patient's insurance.  This is
        // because the same payer might be entered more than once into the
        // insurance_companies table.  I don't think it matters much.
        ?>
    }
    setins("Ins1");
</script>
<?php } ?>
</body>
</html>
