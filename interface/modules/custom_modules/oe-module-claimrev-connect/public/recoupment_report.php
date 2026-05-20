<?php

/**
 * Recoupment Report — shows claims where payments were reversed/recouped.
 *
 * Typically from Medicare reprocessing. Shows original payment, recoupment,
 * reprocessed payment, and net impact per encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\RecoupmentReportService;

$tab = "recoupment_report";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Recoupment Report",
        xl("ClaimRev Connect - Recoupment Report")
    );
}

$dateStart = ModuleInput::postString('dateStart');
$dateEnd = ModuleInput::postString('dateEnd');
$payerName = ModuleInput::postString('payerName');
$patientName = ModuleInput::postString('patientName');
if ($dateStart === '') {
    $dateStart = date('Y-m-d', strtotime('-6 months'));
}
if ($dateEnd === '') {
    $dateEnd = date('Y-m-d');
}
$filters = [
    'dateStart' => $dateStart,
    'dateEnd' => $dateEnd,
    'payerName' => $payerName,
    'patientName' => $patientName,
];

// CSV export
if (ModuleInput::postExists('export_csv') && CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'recoupment')) {
    $data = RecoupmentReportService::getRecoupmentReport($filters);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="recoupment_report_' . date('Y-m-d') . '.csv"');
    file_put_contents('php://output', RecoupmentReportService::toCsv($data['recoupments']));
    exit;
}

$csrfToken = CsrfHelper::collectCsrfToken('recoupment');
$data = null;
$searched = false;

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    $searched = true;
    $data = RecoupmentReportService::getRecoupmentReport($filters);
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Recoupment Report"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .recoup-row { cursor: pointer; }
            .recoup-row:hover { background-color: rgba(0,0,0,.05); }
            .recoup-row.row-pending { background-color: #fff9e5; }
            .recoup-detail-row { display: none; }
            .recoup-detail-row.show { display: table-row; }
            .detail-label { font-weight: bold; color: #666; font-size: 0.85em; }
            .detail-value { font-size: 0.85em; }
            .summary-cards .card { min-width: 120px; }
            .summary-cards .card-body { padding: 10px; text-align: center; }
            .summary-cards h5 { margin: 0; }
            .summary-cards small { color: #666; }
            .pmt-table { font-size: 0.8em; }
            .pmt-table td, .pmt-table th { padding: 2px 6px; }
            .negative { color: #dc3545; }
            .positive { color: #28a745; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="recoupment_report.php" id="recoupForm">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrfToken); ?>"/>
                <div class="card mt-3">
                    <div class="card-header">
                        <?php echo xlt("Recoupment Report"); ?>
                        <small class="text-muted ml-2"><?php echo xlt("Payment reversals, Medicare reprocessing, and take-backs"); ?></small>
                    </div>
                    <div class="card-body pb-0">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="dateStart"><?php echo xlt("From"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateStart" name="dateStart" value="<?php echo attr($dateStart); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="dateEnd"><?php echo xlt("To"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateEnd" name="dateEnd" value="<?php echo attr($dateEnd); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="payerName"><?php echo xlt("Payer"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patientName"><?php echo xlt("Patient"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientName" name="patientName" value="<?php echo attr($patientName); ?>"/>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Run Report"); ?></button>
                            </div>
                            <?php if ($data !== null) { ?>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="export_csv" value="1" class="btn btn-outline-secondary btn-sm btn-block"><i class="fa fa-download"></i> <?php echo xlt("Export CSV"); ?></button>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($searched && ($data === null || $data['recoupments'] === [])) { ?>
            <div class="mt-3"><?php echo xlt("No recoupments found in this date range."); ?></div>
        <?php } elseif ($data !== null) { ?>
            <?php $summary = $data['summary']; ?>

            <!-- Summary cards -->
            <div class="d-flex summary-cards mt-3 mb-2" style="gap: 10px;">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $summary['count']); ?></h5>
                        <small><?php echo xlt("Recoupments"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="negative">$<?php echo text(number_format(abs($summary['totalRecouped']), 2)); ?></h5>
                        <small><?php echo xlt("Total Recouped"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="positive">$<?php echo text(number_format($summary['totalReprocessed'], 2)); ?></h5>
                        <small><?php echo xlt("Reprocessed"); ?></small>
                    </div>
                </div>
                <div class="card border-<?php echo $summary['netImpact'] < 0 ? 'danger' : 'success'; ?>">
                    <div class="card-body">
                        <h5 class="<?php echo $summary['netImpact'] < 0 ? 'negative' : 'positive'; ?>">
                            $<?php echo text(number_format(abs($summary['netImpact']), 2)); ?>
                            <?php echo $summary['netImpact'] < 0 ? xlt("loss") : xlt("gain"); ?>
                        </h5>
                        <small><?php echo xlt("Net Impact"); ?></small>
                    </div>
                </div>
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="text-warning"><?php echo text((string) $summary['pendingReprocess']); ?></h5>
                        <small><?php echo xlt("Pending Reprocess"); ?></small>
                    </div>
                </div>
            </div>

            <!-- Results table -->
            <table class="table table-sm table-bordered mt-1">
                <thead class="thead-light">
                    <tr>
                        <th><?php echo xlt("Patient"); ?></th>
                        <th><?php echo xlt("Encounter"); ?></th>
                        <th><?php echo xlt("Service Date"); ?></th>
                        <th><?php echo xlt("Payer"); ?></th>
                        <th><?php echo xlt("Code"); ?></th>
                        <th class="text-right"><?php echo xlt("Original Paid"); ?></th>
                        <th class="text-right"><?php echo xlt("Recouped"); ?></th>
                        <th class="text-right"><?php echo xlt("Reprocessed"); ?></th>
                        <th class="text-right"><?php echo xlt("Net Impact"); ?></th>
                        <th class="text-right"><?php echo xlt("Balance"); ?></th>
                        <th><?php echo xlt("Status"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['recoupments'] as $idx => $r) {
                        $rowClass = 'recoup-row' . (!$r['hasReprocessed'] ? ' row-pending' : '');
                        ?>
                    <tr class="<?php echo attr($rowClass); ?>" onclick="toggleDetail(<?php echo attr((string) $idx); ?>)">
                        <td>
                            <?php echo text($r['patientName']); ?>
                            <br/><small class="text-muted"><?php echo text($r['patientDob']); ?></small>
                        </td>
                        <td><?php echo text((string) $r['encounter']); ?></td>
                        <td><?php echo text($r['encounterDate']); ?></td>
                        <td><?php echo text($r['payerName']); ?></td>
                        <td><?php echo text($r['code']); ?></td>
                        <td class="text-right">$<?php echo text(number_format($r['originalTotal'], 2)); ?></td>
                        <td class="text-right negative">$<?php echo text(number_format(abs($r['recoupAmount']), 2)); ?></td>
                        <td class="text-right">
                            <?php if ($r['hasReprocessed']) { ?>
                                <span class="positive">$<?php echo text(number_format($r['reprocessedTotal'], 2)); ?></span>
                            <?php } else { ?>
                                <span class="text-muted">—</span>
                            <?php } ?>
                        </td>
                        <td class="text-right <?php echo $r['netImpact'] < 0 ? 'negative' : 'positive'; ?> font-weight-bold">
                            <?php echo $r['netImpact'] < 0 ? '-' : '+'; ?>$<?php echo text(number_format(abs($r['netImpact']), 2)); ?>
                        </td>
                        <td class="text-right"><?php echo text(number_format($r['currentBalance'], 2)); ?></td>
                        <td>
                            <?php if ($r['hasReprocessed']) { ?>
                                <span class="badge badge-success"><?php echo xlt("Reprocessed"); ?></span>
                            <?php } else { ?>
                                <span class="badge badge-warning"><?php echo xlt("Pending"); ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="recoup-detail-row" id="detail-<?php echo attr((string) $idx); ?>">
                        <td colspan="11" style="background-color: rgba(0,0,0,.02); padding: 15px 25px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="detail-label"><?php echo xlt("Recoupment Details"); ?></div>
                                    <div class="detail-value">
                                        <?php echo xlt("Date"); ?>: <strong><?php echo text($r['recoupDate']); ?></strong>
                                        <br/><?php echo xlt("Amount"); ?>: <span class="negative">-$<?php echo text(number_format(abs($r['recoupAmount']), 2)); ?></span>
                                        <?php if ($r['recoupReference'] !== '') { ?>
                                            <br/><?php echo xlt("Reference"); ?>: <?php echo text($r['recoupReference']); ?>
                                        <?php } ?>
                                        <?php if ($r['recoupCheckDate'] !== '') { ?>
                                            <br/><?php echo xlt("Check Date"); ?>: <?php echo text($r['recoupCheckDate']); ?>
                                        <?php } ?>
                                        <?php if ($r['recoupMemo'] !== '') { ?>
                                            <br/><?php echo xlt("Memo"); ?>: <?php echo text($r['recoupMemo']); ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-label"><?php echo xlt("Original Payments"); ?></div>
                                    <?php if ($r['originalPayments'] !== []) { ?>
                                        <table class="table table-sm table-borderless pmt-table mb-0">
                                            <tr><th><?php echo xlt("Date"); ?></th><th><?php echo xlt("Amount"); ?></th><th><?php echo xlt("Ref"); ?></th></tr>
                                            <?php foreach ($r['originalPayments'] as $pmt) { ?>
                                            <tr>
                                                <td><?php echo text($pmt['date']); ?></td>
                                                <td class="positive">$<?php echo text(number_format($pmt['amount'], 2)); ?></td>
                                                <td><?php echo text($pmt['reference']); ?></td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    <?php } else { ?>
                                        <div class="detail-value text-muted"><?php echo xlt("No prior payments found"); ?></div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-label"><?php echo xlt("Reprocessed Payments"); ?></div>
                                    <?php if ($r['reprocessedPayments'] !== []) { ?>
                                        <table class="table table-sm table-borderless pmt-table mb-0">
                                            <tr><th><?php echo xlt("Date"); ?></th><th><?php echo xlt("Amount"); ?></th><th><?php echo xlt("Ref"); ?></th></tr>
                                            <?php foreach ($r['reprocessedPayments'] as $pmt) { ?>
                                            <tr>
                                                <td><?php echo text($pmt['date']); ?></td>
                                                <td class="positive">$<?php echo text(number_format($pmt['amount'], 2)); ?></td>
                                                <td><?php echo text($pmt['reference']); ?></td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    <?php } else { ?>
                                        <div class="detail-value text-warning">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <?php echo xlt("No reprocessed payment found yet. Medicare may still be processing, or the new ERA has not been posted."); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        </div>

        <script>
            function toggleDetail(idx) {
                var row = document.getElementById('detail-' + idx);
                if (row) {
                    row.classList.toggle('show');
                }
            }
        </script>
    </body>
</html>
