<?php

/**
 * Reconciliation page - compares OpenEMR encounters with ClaimRev claim statuses.
 *
 * Shows billed encounters from OpenEMR alongside their ClaimRev status,
 * flags discrepancies, and provides actions to sync or requeue.
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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\ReconciliationService;

$tab = "reconciliation";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Reconciliation",
        xl("ClaimRev Connect - Reconciliation")
    );
}

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$portalUrl = $bootstrap->getGlobalConfig()->getPortalUrl();
$csrfToken = CsrfHelper::collectCsrfToken('claims');
$webRoot = OEGlobalsBag::getInstance()->getString('webroot');

$dateStart = ModuleInput::postString('dateStart');
$dateEnd = ModuleInput::postString('dateEnd');
$statusFilter = ModuleInput::postString('statusFilter', 'billed');
$patientLastName = ModuleInput::postString('patientLastName');
$payerName = ModuleInput::postString('payerName');
$discrepancyOnly = ModuleInput::postExists('discrepancyOnly');
$searchFilters = [
    'dateStart' => $dateStart,
    'dateEnd' => $dateEnd,
    'statusFilter' => $statusFilter,
    'patientLastName' => $patientLastName,
    'payerName' => $payerName,
    'discrepancyOnly' => $discrepancyOnly ? '1' : '',
    'pageIndex' => ModuleInput::postInt('pageIndex'),
];

$encounters = [];
$totalRecords = 0;
$pageIndex = ModuleInput::postInt('pageIndex');
$pageSize = 50;
$claimRevLookupFailed = false;
$searched = false;

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    $searched = true;
    $result = ReconciliationService::reconcile($searchFilters);
    $encounters = $result['encounters'];
    $totalRecords = $result['totalRecords'];
    $claimRevLookupFailed = $result['claimRevLookupFailed'];
}

$totalPages = ($totalRecords > 0) ? (int) ceil($totalRecords / $pageSize) : 0;

// Counts for summary
$discrepancyCount = 0;
$notInCrCount = 0;
$matchedCount = 0;
foreach ($encounters as $enc) {
    if ($enc['discrepancy'] !== '') {
        $discrepancyCount++;
    }
    if (!$enc['crFound']) {
        $notInCrCount++;
    } else {
        $matchedCount++;
    }
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Reconciliation"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .recon-row { cursor: pointer; }
            .recon-row:hover { background-color: rgba(0,0,0,.05); }
            .recon-row.row-discrepancy-danger { background-color: #ffe5e5; }
            .recon-row.row-discrepancy-danger:hover { background-color: #ffd6d6; }
            .recon-row.row-discrepancy-warning { background-color: #fff9e5; }
            .recon-row.row-discrepancy-warning:hover { background-color: #fff3cc; }
            .recon-row.row-not-found { background-color: #f5f5f5; }
            .recon-row.row-matched { }
            .badge-oe { font-size: 0.8em; padding: 3px 7px; }
            .badge-cr { font-size: 0.8em; padding: 3px 7px; }
            .recon-detail-row { display: none; }
            .recon-detail-row.show { display: table-row; }
            .detail-label { font-weight: bold; color: #666; font-size: 0.85em; }
            .detail-value { font-size: 0.85em; }
            .summary-cards .card { min-width: 120px; }
            .summary-cards .card-body { padding: 10px; text-align: center; }
            .summary-cards h5 { margin: 0; }
            .summary-cards small { color: #666; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="reconciliation.php" id="reconSearchForm">
                <input type="hidden" name="pageIndex" id="pageIndex" value="<?php echo attr((string) $pageIndex); ?>"/>
                <div class="card mt-3">
                    <div class="card-header">
                        <?php echo xlt("Reconcile OpenEMR Encounters with ClaimRev"); ?>
                    </div>
                    <div class="card-body pb-0">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="dateStart"><?php echo xlt("Service Date Start"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateStart" name="dateStart" value="<?php echo attr($dateStart); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="dateEnd"><?php echo xlt("Service Date End"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateEnd" name="dateEnd" value="<?php echo attr($dateEnd); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="statusFilter"><?php echo xlt("OE Claim Status"); ?></label>
                                <select class="form-control form-control-sm" id="statusFilter" name="statusFilter">
                                    <option value="billed" <?php echo $statusFilter === 'billed' ? 'selected' : ''; ?>><?php echo xlt("Billed / Crossover"); ?></option>
                                    <option value="denied" <?php echo $statusFilter === 'denied' ? 'selected' : ''; ?>><?php echo xlt("Denied"); ?></option>
                                    <option value="all_billed" <?php echo $statusFilter === 'all_billed' ? 'selected' : ''; ?>><?php echo xlt("All (Billed+Denied)"); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patientLastName"><?php echo xlt("Patient Last"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientLastName" name="patientLastName" value="<?php echo attr($patientLastName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="payerName"><?php echo xlt("Payer"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <div class="w-100">
                                    <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Reconcile"); ?></button>
                                    <div class="custom-control custom-checkbox mt-1 text-center">
                                        <input type="checkbox" class="custom-control-input" id="discrepancyOnly" name="discrepancyOnly" value="1" <?php echo $discrepancyOnly ? 'checked' : ''; ?>/>
                                        <label class="custom-control-label small text-muted" for="discrepancyOnly"><?php echo xlt("Discrepancies Only"); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($claimRevLookupFailed) { ?>
            <div class="alert alert-warning mt-3">
                <i class="fa fa-exclamation-triangle"></i>
                <?php echo xlt("Could not connect to ClaimRev. Showing OpenEMR data only — ClaimRev status columns are empty."); ?>
            </div>
        <?php } ?>

        <?php if ($searched && $encounters === []) { ?>
            <div class="mt-3"><?php echo xlt("No matching encounters found."); ?></div>
        <?php } elseif ($encounters !== []) { ?>

            <!-- Summary cards -->
            <div class="d-flex summary-cards mt-3 mb-2" style="gap: 10px;">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $totalRecords); ?></h5>
                        <small><?php echo xlt("Total"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-success"><?php echo text((string) $matchedCount); ?></h5>
                        <small><?php echo xlt("In ClaimRev"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-muted"><?php echo text((string) $notInCrCount); ?></h5>
                        <small><?php echo xlt("Not in CR"); ?></small>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="text-danger"><?php echo text((string) $discrepancyCount); ?></h5>
                        <small><?php echo xlt("Discrepancies"); ?></small>
                    </div>
                </div>
                <?php if ($totalPages > 1) { ?>
                <div class="ml-auto d-flex align-items-center">
                    <small class="text-muted mr-2"><?php echo xlt("Page"); ?> <?php echo text((string) ($pageIndex + 1)); ?>/<?php echo text((string) $totalPages); ?></small>
                    <?php if ($pageIndex > 0) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1" onclick="goToPage(<?php echo attr((string) ($pageIndex - 1)); ?>)">&laquo;</button>
                    <?php } ?>
                    <?php if ($pageIndex < $totalPages - 1) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex + 1)); ?>)">&raquo;</button>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <table class="table table-sm table-bordered mt-1">
                <thead class="thead-light">
                    <tr>
                        <th><?php echo xlt("Patient"); ?></th>
                        <th><?php echo xlt("Encounter"); ?></th>
                        <th><?php echo xlt("Service Date"); ?></th>
                        <th><?php echo xlt("Payer"); ?></th>
                        <th class="text-right"><?php echo xlt("Charges"); ?></th>
                        <th><?php echo xlt("OE Status"); ?></th>
                        <th><?php echo xlt("ClaimRev Status"); ?></th>
                        <th><?php echo xlt("ERA"); ?></th>
                        <th class="text-right"><?php echo xlt("CR Paid"); ?></th>
                        <th><?php echo xlt("Issue"); ?></th>
                        <th><?php echo xlt("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($encounters as $idx => $enc) {
                        $rowClass = 'recon-row';
                        if ($enc['discrepancy'] !== '') {
                            $level = $enc['discrepancyLevel'] ?: 'warning';
                            $rowClass .= ' row-discrepancy-' . $level;
                        } elseif (!$enc['crFound']) {
                            $rowClass .= ' row-not-found';
                        }

                        $oeBadgeClass = match ($enc['oeStatus']) {
                            0 => 'badge-light text-dark',
                            1 => 'badge-warning',
                            2 => 'badge-primary',
                            6 => 'badge-info',
                            7 => 'badge-danger',
                            default => 'badge-secondary',
                        };

                        // ClaimRev status badge
                        $crBadgeClass = 'badge-secondary';
                        $crStatusId = $enc['crStatusId'];
                        $crPayerAcc = $enc['crPayerAcceptanceStatusId'];
    if (in_array($crStatusId, [10, 16, 17], true) || $crPayerAcc === 3) {
        $crBadgeClass = 'badge-danger';
    } elseif ($crPayerAcc === 4) {
        $crBadgeClass = 'badge-success';
    } elseif (in_array($crStatusId, [7, 8, 9, 18], true)) {
        $crBadgeClass = 'badge-primary';
    }

                        // ERA badge
                        $eraBadge = '';
                        $eraClass = $enc['crEraClassification'];
    if ($eraClass !== '') {
        $eraBadgeClass = match (true) {
            stripos($eraClass, 'denied') !== false => 'badge-danger',
            stripos($eraClass, 'partial') !== false => 'badge-info',
            stripos($eraClass, 'paid') !== false => 'badge-success',
            stripos($eraClass, 'pending') !== false => 'badge-warning',
            default => 'badge-secondary',
        };
    }

                        $isRejectedInCr = in_array($crStatusId, [10, 16, 17], true) || $crPayerAcc === 3;
    ?>
                    <tr class="<?php echo attr($rowClass); ?>" onclick="toggleDetail(<?php echo attr((string) $idx); ?>)">
                        <td>
                            <?php echo text($enc['patientName']); ?>
                            <br/><small class="text-muted"><?php echo text($enc['patientDob']); ?></small>
                        </td>
                        <td><small><?php echo text($enc['pcn']); ?></small></td>
                        <td><?php echo text($enc['encounterDate']); ?></td>
                        <td>
                            <?php echo text($enc['payerName']); ?>
                            <?php if ($enc['payerNumber'] !== '') { ?>
                                <br/><small class="text-muted"><?php echo text($enc['payerNumber']); ?></small>
                            <?php } ?>
                        </td>
                        <td class="text-right"><?php echo text(number_format($enc['totalCharges'], 2)); ?></td>
                        <td>
                            <span class="badge <?php echo attr($oeBadgeClass); ?> badge-oe"><?php echo text($enc['oeStatusLabel']); ?></span>
                        </td>
                        <td>
                            <?php if ($enc['crFound']) { ?>
                                <span class="badge <?php echo attr($crBadgeClass); ?> badge-cr"><?php echo text($enc['crStatusName']); ?></span>
                                <?php if ($enc['crPayerAcceptance'] !== '') { ?>
                                    <br/><small class="text-muted"><?php echo text($enc['crPayerAcceptance']); ?></small>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="text-muted small"><?php echo xlt("Not found"); ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($eraClass !== '') { ?>
                                <span class="badge <?php echo attr($eraBadgeClass); ?> badge-cr"><?php echo text($eraClass); ?></span>
                            <?php } else { ?>
                                <span class="text-muted">—</span>
                            <?php } ?>
                        </td>
                        <td class="text-right">
                            <?php if ($enc['crFound'] && $enc['crPayerPaidAmount'] > 0) { ?>
                                <?php echo text(number_format($enc['crPayerPaidAmount'], 2)); ?>
                            <?php } else { ?>
                                <span class="text-muted">—</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($enc['discrepancy'] !== '') { ?>
                                <small class="text-<?php echo attr($enc['discrepancyLevel'] ?: 'warning'); ?>">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <?php echo text($enc['discrepancy']); ?>
                                </small>
                            <?php } ?>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info btn-sm" title="<?php echo xla("Open Encounter"); ?>"
                                    onclick="openEncounterTab(<?php echo attr((string) $enc['pid']); ?>, <?php echo attr((string) $enc['encounter']); ?>)">
                                    <i class="fa fa-folder-open"></i>
                                </button>
                                <?php if ($enc['crFound'] && $isRejectedInCr && $enc['oeStatus'] !== 7) { ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm sync-status-btn"
                                        data-idx="<?php echo attr((string) $idx); ?>"
                                        data-pcn="<?php echo attr($enc['pcn']); ?>"
                                        data-statusid="<?php echo attr((string) $crStatusId); ?>"
                                        data-statusname="<?php echo attr($enc['crStatusName']); ?>"
                                        data-payeracceptance="<?php echo attr((string) $crPayerAcc); ?>"
                                        title="<?php echo xla("Sync rejected status to OpenEMR"); ?>">
                                        <i class="fa fa-sync-alt"></i>
                                    </button>
                                <?php } ?>
                                <?php if (in_array($enc['oeStatus'], [2, 7], true)) { ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm requeue-btn"
                                        data-idx="<?php echo attr((string) $idx); ?>"
                                        data-pcn="<?php echo attr($enc['pcn']); ?>"
                                        title="<?php echo xla("Requeue for billing"); ?>">
                                        <i class="fa fa-redo"></i>
                                    </button>
                                <?php } ?>
                                <?php if ($enc['crFound'] && $portalUrl !== '' && $enc['crObjectId'] !== '') { ?>
                                    <a href="<?php echo attr($portalUrl); ?>/claimeditor/professionaleditor/<?php echo attr($enc['crObjectId']); ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="<?php echo xla("View in Portal"); ?>">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="recon-detail-row" id="detail-<?php echo attr((string) $idx); ?>">
                        <td colspan="11" style="background-color: rgba(0,0,0,.02); padding: 15px 25px;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="detail-label"><?php echo xlt("OpenEMR"); ?></div>
                                    <div class="detail-value">
                                        <?php echo xlt("Status"); ?>: <strong><?php echo text($enc['oeStatusLabel']); ?></strong>
                                        <br/><?php echo xlt("Bill Time"); ?>: <?php echo text($enc['billTime']); ?>
                                        <?php if ($enc['oeProcessFile'] !== '') { ?>
                                            <br/><?php echo xlt("Process File"); ?>: <?php echo text($enc['oeProcessFile']); ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="detail-label"><?php echo xlt("ClaimRev"); ?></div>
                                    <?php if ($enc['crFound']) { ?>
                                        <div class="detail-value">
                                            <?php echo xlt("Status"); ?>: <strong><?php echo text($enc['crStatusName']); ?></strong>
                                            <br/><?php echo xlt("Payer"); ?>: <?php echo text($enc['crPayerAcceptance']); ?>
                                            <br/><?php echo xlt("Worked"); ?>: <?php echo $enc['crIsWorked'] ? xlt("Yes") : xlt("No"); ?>
                                        </div>
                                    <?php } else { ?>
                                        <div class="detail-value text-muted"><?php echo xlt("Not found in ClaimRev"); ?></div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-3">
                                    <div class="detail-label"><?php echo xlt("ERA / Payment"); ?></div>
                                    <div class="detail-value">
                                        <?php if ($eraClass !== '') { ?>
                                            <?php echo xlt("Classification"); ?>: <strong><?php echo text($eraClass); ?></strong>
                                            <br/><?php echo xlt("Paid"); ?>: <?php echo text(number_format($enc['crPayerPaidAmount'], 2)); ?>
                                        <?php } else { ?>
                                            <span class="text-muted"><?php echo xlt("No ERA data"); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <?php if ($enc['discrepancy'] !== '') { ?>
                                        <div class="detail-label text-<?php echo attr($enc['discrepancyLevel'] ?: 'warning'); ?>"><?php echo xlt("Discrepancy"); ?></div>
                                        <div class="detail-value">
                                            <i class="fa fa-exclamation-triangle text-<?php echo attr($enc['discrepancyLevel'] ?: 'warning'); ?>"></i>
                                            <?php echo text($enc['discrepancy']); ?>
                                        </div>
                                    <?php } else { ?>
                                        <div class="detail-label text-success"><?php echo xlt("Status"); ?></div>
                                        <div class="detail-value text-success"><i class="fa fa-check-circle"></i> <?php echo xlt("No issues detected"); ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1) { ?>
                <div class="d-flex justify-content-center mb-3">
                    <?php if ($pageIndex > 0) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="goToPage(<?php echo attr((string) ($pageIndex - 1)); ?>)">&laquo; <?php echo xlt("Prev"); ?></button>
                    <?php } ?>
                    <?php if ($pageIndex < $totalPages - 1) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex + 1)); ?>)"><?php echo xlt("Next"); ?> &raquo;</button>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
        </div>

        <script>
            var csrfToken = <?php echo json_encode($csrfToken); ?>;

            function openEncounterTab(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr($webRoot); ?>/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(pid) + '&set_encounterid=' + encodeURIComponent(encounter);
            }

            function toggleDetail(idx) {
                var row = document.getElementById('detail-' + idx);
                if (row) {
                    row.classList.toggle('show');
                }
            }

            function goToPage(page) {
                document.getElementById('pageIndex').value = page;
                document.getElementById('reconSearchForm').querySelector('[name="SubmitButton"]').click();
            }

            $(document).ready(function() {
                // Sync Status
                $('.sync-status-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    if (!confirm(<?php echo xlj("Sync this rejected status to OpenEMR? The claim will be marked as denied."); ?>)) return;

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    $.post('claim_sync_status.php', {
                        csrf_token: csrfToken,
                        claimData: JSON.stringify({
                            patientControlNumber: $btn.data('pcn'),
                            statusId: $btn.data('statusid'),
                            statusName: $btn.data('statusname'),
                            payerAcceptanceStatusId: $btn.data('payeracceptance'),
                            payerAcceptanceStatusName: '',
                            errorMessage: ''
                        })
                    }, function(response) {
                        if (response.success && response.action === 'denied') {
                            $btn.replaceWith('<span class="text-success"><i class="fa fa-check"></i></span>');
                            alert(<?php echo xlj("Status synced"); ?>);
                        } else {
                            alert(response.message || <?php echo xlj("No action needed"); ?>);
                            $btn.prop('disabled', false).html('<i class="fa fa-sync-alt"></i>');
                        }
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed to sync"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-sync-alt"></i>');
                    });
                });

                // Requeue
                $('.requeue-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    if (!confirm(<?php echo xlj("Requeue this claim for billing? It will appear in the next billing batch."); ?>)) return;

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    $.post('claim_requeue.php', {
                        csrf_token: csrfToken,
                        patientControlNumber: $btn.data('pcn')
                    }, function(response) {
                        if (response.success) {
                            $btn.replaceWith('<span class="text-success"><i class="fa fa-check"></i> ' + <?php echo xlj("Requeued"); ?> + '</span>');
                        } else {
                            alert(response.message || <?php echo xlj("Requeue failed"); ?>);
                            $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
                        }
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
                    });
                });
            });
        </script>
    </body>
</html>
