<?php

/**
 * ClaimRev Connect - RCM Dashboard.
 *
 * Displays key performance indicators for the revenue cycle:
 * claim pipeline, AR metrics, denial rates, collections, and patient balance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Modules\ClaimRevConnector\DashboardService;
use OpenEMR\Modules\ClaimRevConnector\ModuleVersionCheckService;

$tab = "home";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - Home", xl("ClaimRev Connect - Home"));
}

// Read the cached version-check result for the dashboard banner. This
// never makes a network call; the actual check fires from claim send /
// eligibility paths on the 24h throttle.
$versionCheck = ModuleVersionCheckService::getLastResult();
$installedVersion = Bootstrap::MODULE_VERSION;

$kpis = DashboardService::getKpis();
$claims = $kpis['claims'];
$ar = $kpis['ar'];
$denials = $kpis['denials'];
$collections = $kpis['collections'];
$patientAr = $kpis['patientAr'];

$contactInfo = ClaimRevApi::getSupportInfo();
$contactArr = is_array($contactInfo) ? $contactInfo : [];
$phone = is_string($contactArr['phone'] ?? null) ? $contactArr['phone'] : '918-842-9564';
$supportEmail = is_string($contactArr['supportEmail'] ?? null) ? $contactArr['supportEmail'] : 'support@claimrev.com';
?>
<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Dashboard"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .kpi-card { min-height: 110px; }
            .kpi-card .card-body { padding: 12px 16px; }
            .kpi-value { font-size: 1.8em; font-weight: 700; line-height: 1.1; }
            .kpi-label { font-size: 0.8em; color: #666; margin-top: 4px; }
            .kpi-sub { font-size: 0.75em; color: #999; }
            .section-title { font-size: 0.9em; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; margin-top: 16px; }
            .top-reasons td, .top-reasons th { font-size: 0.85em; padding: 4px 8px; }
            .trend-bar { height: 6px; border-radius: 3px; display: inline-block; vertical-align: middle; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>

            <?php if ($versionCheck === null) { ?>
                <div class="alert alert-light border mt-3 py-2 small">
                    <i class="fa fa-info-circle text-muted"></i>
                    <?php echo xlt("Version"); ?>: <?php echo text($installedVersion); ?>.
                    <?php echo xlt("Update status not yet checked — runs automatically with the next claim send or eligibility check."); ?>
                </div>
            <?php } elseif ($versionCheck->disabled) { ?>
                <div class="alert alert-danger mt-3">
                    <strong><i class="fa fa-ban"></i> <?php echo xlt("Module disabled by ClaimRev"); ?></strong>
                    <div class="small mt-1">
                        <?php echo xlt("Installed version"); ?>: <?php echo text($installedVersion); ?> &middot;
                        <?php echo xlt("Latest version"); ?>: <?php echo text($versionCheck->currentVersion); ?>
                    </div>
                    <?php if ($versionCheck->disableReason !== '') { ?>
                        <div class="mt-1"><?php echo text($versionCheck->disableReason); ?></div>
                    <?php } ?>
                    <?php if ($versionCheck->downloadUrl !== '') { ?>
                        <div class="mt-1"><a href="<?php echo attr($versionCheck->downloadUrl); ?>" target="_blank"><?php echo xlt("Download the latest version"); ?></a></div>
                    <?php } ?>
                </div>
            <?php } elseif (!$versionCheck->isCurrent) { ?>
                <?php
                $alertClass = match ($versionCheck->severity) {
                    'critical' => 'alert-danger',
                    'warning' => 'alert-warning',
                    default => 'alert-info',
                };
                ?>
                <div class="alert <?php echo attr($alertClass); ?> mt-3 py-2">
                    <i class="fa fa-info-circle"></i>
                    <?php echo xlt("A newer version of ClaimRev Connect is available"); ?>:
                    <strong><?php echo text($installedVersion); ?> &rarr; <?php echo text($versionCheck->currentVersion); ?></strong>
                    <?php if ($versionCheck->message !== '') { ?>
                        &mdash; <?php echo text($versionCheck->message); ?>
                    <?php } ?>
                    <?php if ($versionCheck->downloadUrl !== '') { ?>
                        &middot; <a href="<?php echo attr($versionCheck->downloadUrl); ?>" target="_blank"><?php echo xlt("Download"); ?></a>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-success mt-3 py-2 small">
                    <i class="fa fa-check-circle"></i>
                    <?php echo xlt("Version"); ?>: <?php echo text($installedVersion); ?>
                    &mdash; <?php echo xlt("up to date"); ?>
                </div>
            <?php } ?>

            <?php if ($versionCheck !== null && $versionCheck->disabled) { ?>
                <!-- Disabled — KPIs hidden until module is re-enabled. -->
            <?php } else { ?>
            <!-- Claim Pipeline -->
            <div class="section-title mt-3"><?php echo xlt("Claim Pipeline"); ?></div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value text-primary"><?php echo text((string) $claims['inFlight']); ?></div>
                            <div class="kpi-label"><?php echo xlt("Claims In Flight"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("Billed, awaiting response"); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value text-warning"><?php echo text((string) $claims['pendingEras']); ?></div>
                            <div class="kpi-label"><?php echo xlt("Pending ERAs"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("ERA received, not posted"); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value text-danger"><?php echo text((string) $claims['rejected']); ?></div>
                            <div class="kpi-label"><?php echo xlt("Rejected / Denied"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("Last 90 days"); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value <?php echo $claims['cleanClaimRate'] >= 95 ? 'text-success' : ($claims['cleanClaimRate'] >= 90 ? 'text-warning' : 'text-danger'); ?>">
                                <?php echo text((string) $claims['cleanClaimRate']); ?>%
                            </div>
                            <div class="kpi-label"><?php echo xlt("Clean Claim Rate"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("90-day first-pass acceptance"); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AR & Collections -->
            <div class="section-title"><?php echo xlt("Accounts Receivable"); ?></div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value">$<?php echo text(number_format($ar['totalAr'], 0)); ?></div>
                            <div class="kpi-label"><?php echo xlt("Total AR"); ?></div>
                            <div class="kpi-sub">$<?php echo text(number_format($ar['over90'], 0)); ?> <?php echo xlt("over 90 days"); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value <?php echo $ar['avgDaysInAr'] <= 35 ? 'text-success' : ($ar['avgDaysInAr'] <= 50 ? 'text-warning' : 'text-danger'); ?>">
                                <?php echo text((string) $ar['avgDaysInAr']); ?>
                            </div>
                            <div class="kpi-label"><?php echo xlt("Avg Days in AR"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("Target: under 35"); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value text-success">$<?php echo text(number_format($collections['thisMonth'], 0)); ?></div>
                            <div class="kpi-label"><?php echo xlt("Collections This Month"); ?></div>
                            <div class="kpi-sub"><?php echo xlt("Last month"); ?>: $<?php echo text(number_format($collections['lastMonth'], 0)); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi-card">
                        <div class="card-body">
                            <div class="kpi-value">$<?php echo text(number_format($collections['thisQuarter'], 0)); ?></div>
                            <div class="kpi-label"><?php echo xlt("Collections This Quarter"); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Denials & Patient AR -->
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="section-title"><?php echo xlt("Denials (90 Day)"); ?></div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <div class="kpi-value <?php echo $denials['denialRate'] <= 5 ? 'text-success' : ($denials['denialRate'] <= 10 ? 'text-warning' : 'text-danger'); ?>">
                                        <?php echo text((string) $denials['denialRate']); ?>%
                                    </div>
                                    <div class="kpi-label"><?php echo xlt("Denial Rate"); ?></div>
                                    <div class="kpi-sub"><?php echo text((string) $denials['totalDenied']); ?> / <?php echo text((string) $denials['totalProcessed']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php if ($denials['topReasons'] !== []) { ?>
                            <div class="card" style="min-height: 110px;">
                                <div class="card-body p-2">
                                    <table class="table table-sm table-borderless top-reasons mb-0">
                                        <tr><th><?php echo xlt("Top Adjustment Reasons"); ?></th><th class="text-right">#</th></tr>
                                        <?php foreach ($denials['topReasons'] as $r) { ?>
                                        <tr>
                                            <td class="text-truncate" style="max-width:250px;" title="<?php echo attr($r['reason']); ?>"><?php echo text($r['reason']); ?></td>
                                            <td class="text-right"><?php echo text((string) $r['count']); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="card" style="min-height: 110px;">
                                <div class="card-body d-flex align-items-center justify-content-center text-muted">
                                    <?php echo xlt("No adjustment data yet"); ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="section-title"><?php echo xlt("Patient Responsibility"); ?></div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <div class="kpi-value text-danger">$<?php echo text(number_format($patientAr['totalPatientAr'], 0)); ?></div>
                                    <div class="kpi-label"><?php echo xlt("Patient AR"); ?></div>
                                    <div class="kpi-sub"><?php echo text((string) $patientAr['encountersWithBalance']); ?> <?php echo xlt("encounters"); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <div class="kpi-value text-warning"><?php echo text((string) $patientAr['neverSentStatements']); ?></div>
                                    <div class="kpi-label"><?php echo xlt("Need Statements"); ?></div>
                                    <div class="kpi-sub"><?php echo xlt("Never sent"); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card">
                                <div class="card-body text-center pt-4">
                                    <a href="patient_balance.php" class="btn btn-outline-primary btn-sm"><?php echo xlt("Patient Balance Queue"); ?> &rarr;</a>
                                    <br/>
                                    <a href="aging_report.php" class="btn btn-outline-secondary btn-sm mt-2"><?php echo xlt("Aging Report"); ?> &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links & Support -->
            <div class="row mt-3 mb-3">
                <div class="col-md-8">
                    <div class="section-title"><?php echo xlt("Quick Actions"); ?></div>
                    <div class="d-flex" style="gap: 8px; flex-wrap: wrap;">
                        <a href="claims.php" class="btn btn-outline-primary btn-sm"><i class="fa fa-search"></i> <?php echo xlt("Search Claims"); ?></a>
                        <a href="payment_advice.php" class="btn btn-outline-success btn-sm"><i class="fa fa-dollar-sign"></i> <?php echo xlt("Payment Advice"); ?></a>
                        <a href="reconciliation.php" class="btn btn-outline-info btn-sm"><i class="fa fa-balance-scale"></i> <?php echo xlt("Reconciliation"); ?></a>
                        <a href="claim_status.php" class="btn btn-outline-warning btn-sm"><i class="fa fa-tasks"></i> <?php echo xlt("Claim Status"); ?></a>
                        <a href="denial_analytics.php" class="btn btn-outline-danger btn-sm"><i class="fa fa-chart-bar"></i> <?php echo xlt("Denial Analytics"); ?></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="section-title"><?php echo xlt("Support"); ?></div>
                    <small>
                        <i class="fa fa-phone"></i> <a href="tel:<?php echo attr(preg_replace('/[^0-9]/', '', $phone) ?? ''); ?>"><?php echo text($phone); ?></a>
                        &nbsp;|&nbsp;
                        <i class="fa fa-envelope"></i> <a href="mailto:<?php echo attr($supportEmail); ?>"><?php echo text($supportEmail); ?></a>
                    </small>
                </div>
            </div>
            <?php } // end else block — module not disabled, show KPIs ?>
        </div>
    </body>
</html>
