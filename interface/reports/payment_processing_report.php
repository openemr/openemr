<?php

/**
 * Payment processing report.
 *  Supports void and credit with Sphere payment processing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\PaymentProcessing\PaymentProcessing;
use OpenEMR\PaymentProcessing\Sphere\SphereRevert;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Payment Processing")]);
    exit;
}

// If from date is empty, default to 1 week ago.
$from_date = (!empty($_POST['form_from_date'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_from_date']) : date('Y-m-d H:i:s', strtotime('-1 week'));
$to_date = (!empty($_POST['form_to_date'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_to_date']) : date('Y-m-d H:i:s');

$patient = $_POST['form_patient'] ?? null;
$service = $_POST['form_service'] ?? null;
$ticket = $_POST['form_ticket'] ?? null;
$transId = $_POST['form_trans_id'] ?? null;
$actionName = $_POST['form_action_name'] ?? null;
?>

<html>

<head>
    <title><?php echo xlt('Payment Processing'); ?></title>

    <?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

    <script>
        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        function refreshme() {
            document.forms[0].submit();
        }

        function setpatient(pid, lname, fname, dob) {
          document.forms[0].elements['form_patient'].value = pid;
        }

        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }

        <?php
        if ($GLOBALS['payment_gateway'] == 'Sphere') {
            echo SphereRevert::renderRevertSphereJs();
        }
        ?>
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

<!-- Required for the popup date selectors -->
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Payment Processing'); ?></span>

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='payment_processing_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
    <tr>
        <td width='650px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='col-form-label'><?php echo xlt('Service'); ?>:</td>
                <td>
                    <select name='form_service' id='form_service' class='form-control'>
                        <option value=''><?php echo xlt('All'); ?></option>
                        <option value='sphere' <?php echo ($service == 'sphere') ? 'selected' : '' ?>><?php echo xlt('Sphere'); ?></option>
                    </select>
                </td>
                <td class='col-form-label'><?php echo xlt('Patient'); ?>:</td>
                <td>
                    <input type='text' size='20' name='form_patient' class='form-control' style='cursor:pointer;' id='form_patient' value='<?php echo attr($patient); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
                </td>
            </tr>
            <tr>
                <td class='col-form-label'><?php echo xlt('From'); ?>:</td>
                <td><input type='text' name='form_from_date' id="form_from_date" class='datepicker form-control' size='10' value='<?php echo attr(oeFormatDateTime($from_date)); ?>' /></td>
                <td class='col-form-label'><?php echo xlt('To{{Range}}'); ?>:</td>
                <td><input type='text' name='form_to_date' id="form_to_date" class='datepicker form-control' size='10' value='<?php echo attr(oeFormatDateTime($to_date)); ?>'></td>
            </tr>

            <tr>
                <td class='col-form-label'><?php echo xlt('Ticket'); ?>:</td>
                <td><input type='text' name='form_ticket' id='form_ticket' class='form-control' value='<?php echo attr($ticket); ?>' /></td>
                <td class='col-form-label'><?php echo xlt('Transaction ID'); ?>:</td>
                <td><input type='text' name='form_trans_id' id='form_trans_id' class='form-control' value='<?php echo attr($transId); ?>' /></td>
            </tr>

            <tr>
                <td class='col-form-label'><?php echo xlt('Action'); ?>:</td>
                <td>
                    <select name='form_action_name' id='form_action_name' class='form-control'>
                        <option value=''><?php echo xlt('All'); ?></option>
                        <option value='Sale' <?php echo ($actionName == 'Sale') ? 'selected' : '' ?>><?php echo xlt('Sale'); ?></option>
                        <option value='credit' <?php echo ($actionName == 'credit') ? 'selected' : '' ?>><?php echo xlt('Credit'); ?></option>
                        <option value='void' <?php echo ($actionName == 'void') ? 'selected' : '' ?>><?php echo xlt('Void'); ?></option>
                    </select>
                </td>
                <td class='col-form-label'></td>
                <td></td>
            </tr>

        </table>

        </div>

        </td>
        <td class='h-100' align='left' valign='middle'>
        <table class='w-100 h-100' style='border-left: 1px solid;'>
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

</div>
<!-- end of search parameters --> <?php
if (!empty($_POST['form_refresh'])) {
    $showDate = ($from_date != $to_date) || (!$to_date);
    ?>
<div id="report_results">
<table class='table'>

    <thead class='thead-light'>
        <th><?php echo xlt('Date'); ?></th>
        <th><?php echo xlt('Service'); ?></th>
        <th><?php echo xlt('Front'); ?></th>
        <th><?php echo xlt('Ticket'); ?></th>
        <th><?php echo xlt('Transaction ID'); ?></th>
        <th><?php echo xlt('Patient'); ?></th>
        <th><?php echo xlt('Action'); ?></th>
        <th><?php echo xlt('Success'); ?></th>
        <th><?php echo xlt('Amount'); ?></th>
        <th><?php echo xlt('Error Message'); ?></th>
        <th><?php echo xlt('Void/Credit'); ?></th>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php
    $auditEntries = PaymentProcessing::fetchAudit($from_date, $to_date, $patient, $service, $ticket, $transId, $actionName);

    foreach ($auditEntries as $auditEntry) {
        ?>

        <tr valign='top' bgcolor='<?php echo attr($bgcolor ?? ''); ?>'>
            <td class="detail">&nbsp;<?php echo text(oeFormatDateTime($auditEntry['date'])); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['service']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['front_label']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['ticket']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['transaction_id']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['pid']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['action_name_label'] ?? ''); ?></td>
            <td class="detail">&nbsp;<?php echo (!empty($auditEntry['success'])) ? xlt("Yes") : xlt("No"); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['amount']); ?></td>
            <td class="detail">&nbsp;<?php echo text($auditEntry['error_message'] ?? ''); ?></td>
            <td class="detail">
                <?php
                if ($auditEntry['action_name'] == 'Sale') {
                    if (!empty($auditEntry['reverted'])) {
                        // Charge has already been reverted
                        if ($auditEntry['revert_action_name'] == 'void') {
                            echo xlt("This charge was reversed via void on following date") . ": " . text(oeFormatDateTime($auditEntry['revert_date'])) . "<br>" .
                                xlt("The transaction_id for the void was") . ": " . text($auditEntry['revert_transaction_id']);
                        } else { // $auditEntry['revert_action_name'] == 'credit'
                            echo xlt("This charge was reversed via credit on following date") . ": " . text(oeFormatDateTime($auditEntry['revert_date'])) . "<br>" .
                                xlt("The Transaction ID for the credit was") . ": " . text($auditEntry['revert_transaction_id']);
                        }
                    }
                    if (!empty($auditEntry['offer_void'])) {
                        if (($auditEntry['service'] == 'sphere') && ($GLOBALS['payment_gateway'] == 'Sphere')) {
                            echo SphereRevert::renderSphereVoidButton($auditEntry['front'], $auditEntry['transaction_id'], $auditEntry['uuid']);
                        }
                    }
                    if (!empty($auditEntry['offer_credit'])) {
                        if (($auditEntry['service'] == 'sphere') && ($GLOBALS['payment_gateway'] == 'Sphere')) {
                            echo SphereRevert::renderSphereCreditButton($auditEntry['front'], $auditEntry['transaction_id'], $auditEntry['uuid']);
                        }
                    }
                } elseif (($auditEntry['action_name'] == 'void') || ($auditEntry['action_name'] == 'credit')) {
                    if (!empty($auditEntry['success'])) {
                        if ($auditEntry['action_name'] == 'void') {
                            echo xlt("This transaction voided the following Transaction ID" . ": " . $auditEntry['map_transaction_id']);
                        } else { // $auditEntry['action_name'] == 'credit'
                            echo xlt("This transaction credited the following Transaction ID" . ": " . $auditEntry['map_transaction_id']);
                        }
                    }
                }
                ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<!-- end of search results -->
<?php } else { ?>
<div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>
<input type='hidden' name='form_refresh' id='form_refresh' value='' /></form>

</body>

</html>
