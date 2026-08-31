<?php

/**
 * Prepayment balance report.
 *
 * Lists open pre-payment sessions (ar_session.adjustment_code = 'pre_payment',
 * closed = 0) whose received amount has not been fully applied to charges.
 *
 * The balance arithmetic lives in {@see PrepaymentBalanceService}; this file is
 * the view.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../globals.php");

use OpenEMR\Billing\PrepaymentBalance;
use OpenEMR\Billing\PrepaymentBalanceService;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\DateFormatterUtils;

$request = CurrentRequest::get();
$session = SessionWrapperFactory::getInstance()->getActiveSession();

if ($request->isMethod('POST')) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/rep_a: Prepayment Balances",
        xl("Prepayment Balances")
    );
}

// Optional filters. An empty check-date bound means no bound in that direction,
// so the default view is every open prepayment regardless of age, which is the
// point of the report. Dates filter on ar_session.check_date.
$formFromDate = DateFormatterUtils::DateToYYYYMMDD($request->request->getString('form_from_date'));
$formToDate = DateFormatterUtils::DateToYYYYMMDD($request->request->getString('form_to_date'));
$formFromDate = is_string($formFromDate) ? $formFromDate : '';
$formToDate = is_string($formToDate) ? $formToDate : '';
$formPatient = trim($request->request->getString('form_patient'));
$formPatientId = $formPatient === '' ? null : (int) $formPatient;
$formParkedOnly = $request->request->getBoolean('form_parked_only');
$isRefresh = $request->request->getBoolean('form_refresh');

// oeFormatShortDate() returns its argument unchanged for inputs shorter than a
// full date, so its return type is mixed. Narrow once here rather than at each
// of the five display sites.
$shortDate = static function (?string $date): string {
    if ($date === null || $date === '') {
        return '';
    }
    $formatted = DateFormatterUtils::oeFormatShortDate($date);

    return is_string($formatted) ? $formatted : '';
};
?>

<html>

<head>
    <title><?php echo xlt('Prepayment Balances'); ?></title>

    <?php Header::setupHeader(["datetime-picker", "report-helper"]); ?>

    <script>
        // Re-run the report when the edit_payment dialog closes so that fully
        // applied sessions drop off the list immediately.
        function refreshReport() {
            $("#form_refresh").attr("value", "true");
            $("#theform").submit();
        }

        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            // Open sessions in a dialog rather than navigating away from the
            // report, matching the pattern in billing/search_payments.php.
            $(document).on('click', '.medium_modal', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 'modal-full', 800, '', '', {
                    buttons: [
                        {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                    ],
                    sizeHeight: '',
                    onClosed: 'refreshReport',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });

        function setpatient(pid, lname, fname, dob) {
            document.forms[0].elements['form_patient'].value = pid;
            document.forms[0].elements['form_patient_name'].value = lname + ', ' + fname;
        }

        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }

        function clear_patient() {
            document.forms[0].elements['form_patient'].value = '';
            document.forms[0].elements['form_patient_name'].value = '';
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

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Prepayment Balances'); ?></span>

<div id="report_parameters_daterange"><?php echo text($shortDate($formFromDate)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text($shortDate($formToDate)); ?>
</div>

<form method='post' name='theform' id='theform' action='prepayment_balance_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />

<div id="report_parameters">

<table>
    <tr>
        <td>
        <div class='float-left'>

        <table class='text'>
            <tr>
                <td class='col-form-label'><?php echo xlt('Check Date From'); ?>:</td>
                <td><input type='text' name='form_from_date' id='form_from_date' class='datepicker form-control' size='10' value='<?php echo attr($shortDate($formFromDate)); ?>' /></td>
                <td class='col-form-label'><?php echo xlt('To{{Range}}'); ?>:</td>
                <td><input type='text' name='form_to_date' id='form_to_date' class='datepicker form-control' size='10' value='<?php echo attr($shortDate($formToDate)); ?>' /></td>
            </tr>
            <tr>
                <td class='col-form-label'><?php echo xlt('Patient'); ?>:</td>
                <td>
                    <input type='text' size='20' name='form_patient_name' id='form_patient_name' class='form-control' style='cursor: pointer;' value='<?php echo attr($formPatient); ?>' onclick='sel_patient()' readonly title='<?php echo xla('Click to select patient'); ?>' />
                    <input type='hidden' name='form_patient' id='form_patient' value='<?php echo attr($formPatient); ?>' />
                    <a href='#' onclick='clear_patient(); return false;'><?php echo xlt('Clear'); ?></a>
                </td>
                <td class='col-form-label'><?php echo xlt('Parked in Global only'); ?>:</td>
                <td>
                    <input type='checkbox' name='form_parked_only' id='form_parked_only' value='1'<?php echo $formParkedOnly ? ' checked' : ''; ?> />
                </td>
            </tr>
        </table>

        </div>

        </td>
        <td class='h-100'>
        <table class='w-100 h-100' style='border-left: 1px solid;'>
            <tr>
                <td>
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('Submit'); ?>
                            </a>
                            <?php if ($isRefresh) { ?>
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
<!-- end of search parameters -->
<?php
if ($isRefresh) {
    $service = new PrepaymentBalanceService();
    $balances = $service->getOpenBalances($formFromDate, $formToDate, $formPatientId, $formParkedOnly);
    $totals = PrepaymentBalance::totals($balances);
    ?>
<div id="report_results">
<table class='table'>

    <thead class='thead-light'>
        <tr>
            <th><?php echo xlt('Patient'); ?></th>
            <th><?php echo xlt('PID'); ?></th>
            <th><?php echo xlt('Session'); ?></th>
            <th><?php echo xlt('Reference'); ?></th>
            <th><?php echo xlt('Check Date'); ?></th>
            <th class="text-right"><?php echo xlt('Days Open'); ?></th>
            <th class="text-right"><?php echo xlt('Received'); ?></th>
            <th class="text-right"><?php echo xlt('Applied'); ?></th>
            <th class="text-right"><?php echo xlt('In Global'); ?></th>
            <th class="text-right"><?php echo xlt('Unapplied'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($balances as $balance) { ?>
        <tr>
            <td class="detail">&nbsp;<?php echo text($balance->patientName); ?></td>
            <td class="detail">&nbsp;<?php echo text((string) $balance->patientId); ?></td>
            <td class="detail">&nbsp;
                <a class="medium_modal" href='../billing/edit_payment.php?payment_id=<?php echo attr_url((string) $balance->sessionId); ?>'>
                    <?php echo text((string) $balance->sessionId); ?>
                </a>
            </td>
            <td class="detail">&nbsp;<?php echo text($balance->reference); ?></td>
            <td class="detail">&nbsp;<?php echo $balance->checkDate === null ? '&nbsp;' : text($shortDate($balance->checkDate)); ?></td>
            <td class="detail text-right"><?php echo text((string) $balance->daysOpen); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($balance->received)); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($balance->applied)); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($balance->inGlobal)); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($balance->unapplied)); ?></td>
        </tr>
    <?php } ?>
    </tbody>
    <tfoot>
        <tr class='font-weight-bold'>
            <td class="detail" colspan="6">&nbsp;<?php echo xlt('Totals'); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($totals['received'])); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($totals['applied'])); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($totals['inGlobal'])); ?></td>
            <td class="detail text-right"><?php echo text(oeFormatMoney($totals['unapplied'])); ?></td>
        </tr>
    </tfoot>
</table>
</div>
<!-- end of search results -->
<?php } else { ?>
<div class='text'><?php echo xlt('Click Submit to list all open prepayments with an unapplied balance. Date and patient filters are optional.'); ?>
</div>
<?php } ?>
<input type='hidden' name='form_refresh' id='form_refresh' value='' /></form>

</body>

</html>
