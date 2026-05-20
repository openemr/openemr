<?php

/**
 * AR Aging Report — 30/60/90/120 day aging buckets by payer.
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
use OpenEMR\Modules\ClaimRevConnector\AgingReportService;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

$tab = "aging_report";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Aging Report",
        xl("ClaimRev Connect - Aging Report")
    );
}

$payerName = ModuleInput::postString('payerName');
$patientName = ModuleInput::postString('patientName');
$minAmount = ModuleInput::postString('minAmount');
$filters = [
    'payerName' => $payerName,
    'patientName' => $patientName,
    'minAmount' => $minAmount,
];

// Handle CSV export
if (ModuleInput::postExists('export_csv') && CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'aging')) {
    $report = AgingReportService::getAgingReport($filters);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="aging_report_' . date('Y-m-d') . '.csv"');
    file_put_contents('php://output', AgingReportService::toCsv($report['encounters']));
    exit;
}

$csrfToken = CsrfHelper::collectCsrfToken('aging');
$report = null;
$searched = false;

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    $searched = true;
    $report = AgingReportService::getAgingReport($filters);
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Aging Report"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .aging-table th, .aging-table td { font-size: 0.85em; }
            .aging-table .payer-row { cursor: pointer; }
            .aging-table .payer-row:hover { background-color: rgba(0,0,0,.05); }
            .bucket-header { font-size: 0.75em; }
            .totals-row { font-weight: bold; background-color: #f8f9fa; }
            .summary-cards .card { min-width: 100px; }
            .summary-cards .card-body { padding: 10px; text-align: center; }
            .summary-cards h5 { margin: 0; }
            .summary-cards small { color: #666; }
            .pct-bar { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; min-width: 60px; }
            .pct-bar-fill { height: 100%; border-radius: 4px; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="aging_report.php" id="agingForm">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrfToken); ?>"/>
                <div class="card mt-3">
                    <div class="card-header">
                        <?php echo xlt("AR Aging Report"); ?>
                    </div>
                    <div class="card-body pb-0">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="payerName"><?php echo xlt("Payer"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="patientName"><?php echo xlt("Patient Name"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientName" name="patientName" value="<?php echo attr($patientName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="minAmount"><?php echo xlt("Min Balance"); ?></label>
                                <input type="number" step="0.01" class="form-control form-control-sm" id="minAmount" name="minAmount" value="<?php echo attr($minAmount); ?>" placeholder="0.01"/>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Run Report"); ?></button>
                            </div>
                            <?php if ($report !== null) { ?>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="export_csv" value="1" class="btn btn-outline-secondary btn-sm btn-block"><i class="fa fa-download"></i> <?php echo xlt("Export CSV"); ?></button>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($searched && $report === null) { ?>
            <div class="mt-3"><?php echo xlt("No data found."); ?></div>
        <?php } elseif ($report !== null) { ?>
            <?php $totals = $report['totals']; $payers = $report['payers']; ?>

            <!-- Summary cards -->
            <div class="d-flex summary-cards mt-3 mb-2" style="gap: 10px;">
                <div class="card">
                    <div class="card-body">
                        <h5>$<?php echo text(number_format($totals['total'], 0)); ?></h5>
                        <small><?php echo xlt("Total AR"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-success">$<?php echo text(number_format($totals['current'], 0)); ?></h5>
                        <small><?php echo xlt("0-30"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5>$<?php echo text(number_format($totals['days30'], 0)); ?></h5>
                        <small><?php echo xlt("31-60"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-warning">$<?php echo text(number_format($totals['days60'], 0)); ?></h5>
                        <small><?php echo xlt("61-90"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-danger">$<?php echo text(number_format($totals['days90'], 0)); ?></h5>
                        <small><?php echo xlt("91-120"); ?></small>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="text-danger">$<?php echo text(number_format($totals['days120'] + $totals['days120plus'], 0)); ?></h5>
                        <small><?php echo xlt("120+"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) count($payers)); ?></h5>
                        <small><?php echo xlt("Payers"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) count($report['encounters'])); ?></h5>
                        <small><?php echo xlt("Encounters"); ?></small>
                    </div>
                </div>
            </div>

            <!-- Payer aging table -->
            <table class="table table-sm table-bordered aging-table mt-1">
                <thead class="thead-light">
                    <tr>
                        <th><?php echo xlt("Payer"); ?></th>
                        <th class="text-right bucket-header"><?php echo xlt("0-30"); ?></th>
                        <th class="text-right bucket-header"><?php echo xlt("31-60"); ?></th>
                        <th class="text-right bucket-header"><?php echo xlt("61-90"); ?></th>
                        <th class="text-right bucket-header"><?php echo xlt("91-120"); ?></th>
                        <th class="text-right bucket-header"><?php echo xlt("120+"); ?></th>
                        <th class="text-right"><?php echo xlt("Total"); ?></th>
                        <th class="text-center"><?php echo xlt("Enc"); ?></th>
                        <th style="width:100px;"><?php echo xlt("Distribution"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payers as $p) {
                        $pctOver90 = $p['total'] > 0 ? (($p['days90'] + $p['days120'] + $p['days120plus']) / $p['total']) * 100 : 0.0;
                        $barColor = $pctOver90 > 50 ? '#dc3545' : ($pctOver90 > 25 ? '#ffc107' : '#28a745');
                        ?>
                    <tr class="payer-row">
                        <td><?php echo text($p['payerName']); ?></td>
                        <td class="text-right"><?php echo $p['current'] > 0 ? text(number_format($p['current'], 2)) : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-right"><?php echo $p['days30'] > 0 ? text(number_format($p['days30'], 2)) : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-right"><?php echo $p['days60'] > 0 ? text(number_format($p['days60'], 2)) : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-right"><?php echo $p['days90'] > 0 ? '<span class="text-danger">' . text(number_format($p['days90'], 2)) . '</span>' : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-right"><?php echo ($p['days120'] + $p['days120plus']) > 0 ? '<span class="text-danger font-weight-bold">' . text(number_format($p['days120'] + $p['days120plus'], 2)) . '</span>' : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-right font-weight-bold">$<?php echo text(number_format($p['total'], 2)); ?></td>
                        <td class="text-center"><?php echo text((string) $p['encounterCount']); ?></td>
                        <td>
                            <div class="pct-bar">
                                <div class="pct-bar-fill" style="width:<?php echo attr((string) min(100.0, $pctOver90)); ?>%; background-color:<?php echo attr($barColor); ?>;"></div>
                            </div>
                            <small class="text-muted"><?php echo text((string) round($pctOver90)); ?>% &gt;90d</small>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr class="totals-row">
                        <td><?php echo xlt("TOTAL"); ?></td>
                        <td class="text-right"><?php echo text(number_format($totals['current'], 2)); ?></td>
                        <td class="text-right"><?php echo text(number_format($totals['days30'], 2)); ?></td>
                        <td class="text-right"><?php echo text(number_format($totals['days60'], 2)); ?></td>
                        <td class="text-right text-danger"><?php echo text(number_format($totals['days90'], 2)); ?></td>
                        <td class="text-right text-danger font-weight-bold"><?php echo text(number_format($totals['days120'] + $totals['days120plus'], 2)); ?></td>
                        <td class="text-right font-weight-bold">$<?php echo text(number_format($totals['total'], 2)); ?></td>
                        <td class="text-center"><?php echo text((string) count($report['encounters'])); ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        <?php } ?>
        </div>
    </body>
</html>
