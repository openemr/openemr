<?php

/**
 * Claim Status Dashboard - tracks claim lifecycle with work queue and timeline.
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
use OpenEMR\Modules\ClaimRevConnector\ClaimTrackingService;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

$tab = "claim_status";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Claim Status",
        xl("ClaimRev Connect - Claim Status")
    );
}

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$portalUrl = $bootstrap->getGlobalConfig()->getPortalUrl();
$csrfToken = CsrfHelper::collectCsrfToken('claim_status');
$webRoot = OEGlobalsBag::getInstance()->getString('webroot');

$dateStart = ModuleInput::postString('dateStart');
$dateEnd = ModuleInput::postString('dateEnd');
$patientLastName = ModuleInput::postString('patientLastName');
$payerName = ModuleInput::postString('payerName');
$statusFilter = ModuleInput::postString('statusFilter', 'all');
$searchFilters = [
    'dateStart' => $dateStart,
    'dateEnd' => $dateEnd,
    'patientLastName' => $patientLastName,
    'payerName' => $payerName,
    'statusFilter' => $statusFilter,
    'pageIndex' => ModuleInput::postInt('pageIndex'),
];

$claims = [];
$totalRecords = 0;
$pageIndex = ModuleInput::postInt('pageIndex');
$pageSize = 50;
$searched = false;
$stats = ['total' => 0, 'needingAttention' => 0, 'rejected' => 0, 'denied' => 0, 'stale' => 0, 'paid' => 0, 'paidNotPosted' => 0];

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    $searched = true;
    $result = ClaimTrackingService::getWorkQueue($searchFilters);
    $claims = $result['claims'];
    $totalRecords = $result['totalRecords'];
    $stats = ClaimTrackingService::getDashboardStats($searchFilters);
}

$totalPages = ($totalRecords > 0) ? (int) ceil($totalRecords / $pageSize) : 0;
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Claim Status"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .status-row { cursor: pointer; }
            .status-row:hover { background-color: rgba(0,0,0,.05); }
            .status-row.row-rejected { background-color: #ffe5e5; }
            .status-row.row-rejected:hover { background-color: #ffd6d6; }
            .status-row.row-denied { background-color: #ffe5e5; }
            .status-row.row-denied:hover { background-color: #ffd6d6; }
            .status-row.row-stale { background-color: #fff9e5; }
            .status-row.row-stale:hover { background-color: #fff3cc; }
            .status-row.row-paid { background-color: #e8f5e9; }
            .status-row.row-paid:hover { background-color: #dcedc8; }
            .badge-status { font-size: 0.8em; padding: 3px 7px; }
            .timeline-row { display: none; }
            .timeline-row.show { display: table-row; }
            .timeline-event { border-left: 3px solid #dee2e6; padding: 8px 15px; margin-bottom: 8px; font-size: 0.85em; }
            .timeline-event.event-payment_posted { border-left-color: #28a745; }
            .timeline-event.event-denied, .timeline-event.event-rejected { border-left-color: #dc3545; }
            .timeline-event.event-status_check_276 { border-left-color: #007bff; }
            .timeline-event.event-requeued { border-left-color: #ffc107; }
            .timeline-event.event-claimrev_sync { border-left-color: #6c757d; }
            .timeline-event.event-manual_note { border-left-color: #17a2b8; }
            .timeline-event .event-date { color: #999; font-size: 0.85em; }
            .timeline-event .event-source { font-size: 0.8em; }
            .summary-cards .card { min-width: 110px; }
            .summary-cards .card-body { padding: 10px; text-align: center; }
            .summary-cards h5 { margin: 0; }
            .summary-cards small { color: #666; }
            .note-form { display: none; margin-top: 10px; }
            .note-form.show { display: block; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="claim_status.php" id="statusSearchForm">
                <input type="hidden" name="pageIndex" id="pageIndex" value="<?php echo attr((string) $pageIndex); ?>"/>
                <div class="card mt-3">
                    <div class="card-header">
                        <?php echo xlt("Claim Status Dashboard"); ?>
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
                                <label for="patientLastName"><?php echo xlt("Patient Last"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientLastName" name="patientLastName" value="<?php echo attr($patientLastName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="payerName"><?php echo xlt("Payer"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="statusFilter"><?php echo xlt("Filter"); ?></label>
                                <select class="form-control form-control-sm" id="statusFilter" name="statusFilter">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>><?php echo xlt("All Billed"); ?></option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>><?php echo xlt("Rejected"); ?></option>
                                    <option value="denied" <?php echo $statusFilter === 'denied' ? 'selected' : ''; ?>><?php echo xlt("Denied"); ?></option>
                                    <option value="stale" <?php echo $statusFilter === 'stale' ? 'selected' : ''; ?>><?php echo xlt("Stale"); ?></option>
                                    <option value="paid_not_posted" <?php echo $statusFilter === 'paid_not_posted' ? 'selected' : ''; ?>><?php echo xlt("Paid - Not Posted"); ?></option>
                                    <option value="unworked" <?php echo $statusFilter === 'unworked' ? 'selected' : ''; ?>><?php echo xlt("Unworked"); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Search"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($searched && $claims === []) { ?>
            <div class="mt-3"><?php echo xlt("No matching claims found."); ?></div>
        <?php } elseif ($claims !== []) { ?>

            <!-- Summary cards -->
            <div class="d-flex summary-cards mt-3 mb-2" style="gap: 10px;">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $stats['total']); ?></h5>
                        <small><?php echo xlt("Total"); ?></small>
                    </div>
                </div>
                <?php if ($stats['needingAttention'] > 0) { ?>
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="text-danger"><?php echo text((string) $stats['needingAttention']); ?></h5>
                        <small><?php echo xlt("Need Attention"); ?></small>
                    </div>
                </div>
                <?php } ?>
                <?php if ($stats['rejected'] > 0) { ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-danger"><?php echo text((string) $stats['rejected']); ?></h5>
                        <small><?php echo xlt("Rejected"); ?></small>
                    </div>
                </div>
                <?php } ?>
                <?php if ($stats['denied'] > 0) { ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-danger"><?php echo text((string) $stats['denied']); ?></h5>
                        <small><?php echo xlt("Denied"); ?></small>
                    </div>
                </div>
                <?php } ?>
                <?php if ($stats['stale'] > 0) { ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-warning"><?php echo text((string) $stats['stale']); ?></h5>
                        <small><?php echo xlt("Stale"); ?></small>
                    </div>
                </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-success"><?php echo text((string) $stats['paid']); ?></h5>
                        <small><?php echo xlt("Paid"); ?></small>
                    </div>
                </div>
                <?php if ($stats['paidNotPosted'] > 0) { ?>
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="text-warning"><?php echo text((string) $stats['paidNotPosted']); ?></h5>
                        <small><?php echo xlt("Paid Not Posted"); ?></small>
                    </div>
                </div>
                <?php } ?>
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
                        <th class="text-right"><?php echo xlt("Paid"); ?></th>
                        <th><?php echo xlt("Last Check"); ?></th>
                        <th><?php echo xlt("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $idx => $claim) {
                        $rowClass = 'status-row';
                        $crStatusId = $claim['crStatusId'];
                        $payerAccId = $claim['payerAcceptanceStatusId'];
                        $eraCls = $claim['eraClassification'];
                        $isRejected = in_array($crStatusId, [10, 16, 17], true) || $payerAccId === 3;
                        $isDenied = stripos($eraCls, 'denied') !== false || $claim['oeStatus'] === 7;
                        $isPaid = stripos($eraCls, 'paid') !== false;
                        $billTime = $claim['billTime'];
                        $isStale = $claim['oeStatus'] === 2 && $eraCls === '' && $billTime !== '' && strtotime($billTime) < strtotime('-45 days');

                        if ($isRejected) {
                            $rowClass .= ' row-rejected';
                        } elseif ($isDenied) {
                            $rowClass .= ' row-denied';
                        } elseif ($isStale) {
                            $rowClass .= ' row-stale';
                        } elseif ($isPaid) {
                            $rowClass .= ' row-paid';
                        }

                        // OE status badge
                        $oeBadgeClass = match ($claim['oeStatus']) {
                            0 => 'badge-light text-dark',
                            1 => 'badge-warning',
                            2 => 'badge-primary',
                            3 => 'badge-success',
                            6 => 'badge-info',
                            7 => 'badge-danger',
                            default => 'badge-secondary',
                        };

                        // CR status badge
                        $crBadgeClass = 'badge-secondary';
    if ($isRejected) {
        $crBadgeClass = 'badge-danger';
    } elseif ($payerAccId === 4) {
        $crBadgeClass = 'badge-success';
    } elseif (in_array($crStatusId, [7, 8, 9, 18], true)) {
        $crBadgeClass = 'badge-primary';
    }

                        // ERA badge
                        $eraBadgeClass = match (true) {
                            stripos($eraCls, 'denied') !== false => 'badge-danger',
                            stripos($eraCls, 'partial') !== false => 'badge-info',
                            stripos($eraCls, 'paid') !== false => 'badge-success',
                            stripos($eraCls, 'pending') !== false => 'badge-warning',
                            default => 'badge-secondary',
                        };
    ?>
                    <tr class="<?php echo attr($rowClass); ?>" onclick="toggleTimeline(<?php echo attr((string) $idx); ?>)" id="row-<?php echo attr((string) $idx); ?>">
                        <td>
                            <?php echo text($claim['patientName']); ?>
                            <br/><small class="text-muted"><?php echo text($claim['patientDob']); ?></small>
                        </td>
                        <td><small><?php echo text($claim['pcn']); ?></small></td>
                        <td><?php echo text($claim['encounterDate']); ?></td>
                        <td>
                            <?php echo text($claim['payerName']); ?>
                            <?php if ($claim['payerNumber'] !== '') { ?>
                                <br/><small class="text-muted"><?php echo text($claim['payerNumber']); ?></small>
                            <?php } ?>
                        </td>
                        <td class="text-right"><?php echo text(number_format($claim['totalCharges'], 2)); ?></td>
                        <td>
                            <span class="badge <?php echo attr($oeBadgeClass); ?> badge-status"><?php echo text($claim['oeStatusLabel']); ?></span>
                        </td>
                        <td>
                            <?php if ($claim['crStatusName'] !== '') { ?>
                                <span class="badge <?php echo attr($crBadgeClass); ?> badge-status"><?php echo text($claim['crStatusName']); ?></span>
                                <?php if ($claim['payerAcceptanceName'] !== '') { ?>
                                    <br/><small class="text-muted"><?php echo text($claim['payerAcceptanceName']); ?></small>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="text-muted small"><?php echo $claim['trackingId'] !== null ? xlt("Synced") : xlt("Not tracked"); ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($eraCls !== '') { ?>
                                <span class="badge <?php echo attr($eraBadgeClass); ?> badge-status"><?php echo text($eraCls); ?></span>
                            <?php } else { ?>
                                <span class="text-muted">&mdash;</span>
                            <?php } ?>
                        </td>
                        <td class="text-right">
                            <?php if ($claim['payerPaidAmount'] > 0) { ?>
                                <?php echo text(number_format($claim['payerPaidAmount'], 2)); ?>
                            <?php } else { ?>
                                <span class="text-muted">&mdash;</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($claim['lastStatusCheck'] !== '') { ?>
                                <small><?php echo text(substr($claim['lastStatusCheck'], 0, 10)); ?></small>
                            <?php } else { ?>
                                <small class="text-muted"><?php echo xlt("Never"); ?></small>
                            <?php } ?>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info btn-sm" title="<?php echo xla("Check Status"); ?>"
                                    onclick="checkStatus(<?php echo attr((string) $claim['pid']); ?>, <?php echo attr((string) $claim['encounter']); ?>, <?php echo attr((string) $claim['payerType']); ?>, this)">
                                    <i class="fa fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" title="<?php echo xla("Open Encounter"); ?>"
                                    onclick="openEncounterTab(<?php echo attr((string) $claim['pid']); ?>, <?php echo attr((string) $claim['encounter']); ?>)">
                                    <i class="fa fa-folder-open"></i>
                                </button>
                                <?php if ($portalUrl !== '' && $claim['crObjectId'] !== '') { ?>
                                    <a href="<?php echo attr($portalUrl); ?>/claimeditor/professionaleditor/<?php echo attr($claim['crObjectId']); ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="<?php echo xla("View in Portal"); ?>">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="timeline-row" id="timeline-<?php echo attr((string) $idx); ?>">
                        <td colspan="11" style="background-color: rgba(0,0,0,.02); padding: 15px 25px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6><?php echo xlt("Claim Summary"); ?></h6>
                                    <div style="font-size: 0.85em;">
                                        <strong><?php echo xlt("Patient"); ?>:</strong> <?php echo text($claim['patientName']); ?><br/>
                                        <strong><?php echo xlt("Encounter"); ?>:</strong> <?php echo text($claim['pcn']); ?><br/>
                                        <strong><?php echo xlt("Payer"); ?>:</strong> <?php echo text($claim['payerName']); ?><br/>
                                        <strong><?php echo xlt("Charges"); ?>:</strong> $<?php echo text(number_format($claim['totalCharges'], 2)); ?><br/>
                                        <strong><?php echo xlt("OE Status"); ?>:</strong> <?php echo text($claim['oeStatusLabel']); ?><br/>
                                        <?php if ($claim['crStatusName'] !== '') { ?>
                                            <strong><?php echo xlt("CR Status"); ?>:</strong> <?php echo text($claim['crStatusName']); ?><br/>
                                        <?php } ?>
                                        <?php if ($claim['lastSynced'] !== '') { ?>
                                            <strong><?php echo xlt("Last Synced"); ?>:</strong> <?php echo text($claim['lastSynced']); ?><br/>
                                        <?php } ?>
                                        <?php if ($claim['arSessionId'] !== null) { ?>
                                            <strong><?php echo xlt("Payment Session"); ?>:</strong> <?php echo text((string) $claim['arSessionId']); ?><br/>
                                        <?php } ?>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleNoteForm(<?php echo attr((string) $idx); ?>)">
                                            <i class="fa fa-sticky-note"></i> <?php echo xlt("Add Note"); ?>
                                        </button>
                                    </div>
                                    <div class="note-form" id="noteForm-<?php echo attr((string) $idx); ?>">
                                        <textarea class="form-control form-control-sm mt-1" id="noteText-<?php echo attr((string) $idx); ?>" rows="2" placeholder="<?php echo xla("Enter note..."); ?>"></textarea>
                                        <button type="button" class="btn btn-sm btn-primary mt-1" onclick="addNote(<?php echo attr((string) $claim['pid']); ?>, <?php echo attr((string) $claim['encounter']); ?>, <?php echo attr((string) $claim['payerType']); ?>, <?php echo attr((string) $idx); ?>)">
                                            <?php echo xlt("Save Note"); ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6><?php echo xlt("Timeline"); ?></h6>
                                    <div id="timelineContent-<?php echo attr((string) $idx); ?>">
                                        <div class="text-muted"><i class="fa fa-spinner fa-spin"></i> <?php echo xlt("Loading..."); ?></div>
                                    </div>
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
            var claimData = <?php echo json_encode($claims); ?>;

            function openEncounterTab(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr($webRoot); ?>/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(pid) + '&set_encounterid=' + encodeURIComponent(encounter);
            }

            function toggleTimeline(idx) {
                var row = document.getElementById('timeline-' + idx);
                if (row) {
                    var wasHidden = !row.classList.contains('show');
                    row.classList.toggle('show');
                    if (wasHidden) {
                        loadTimeline(idx);
                    }
                }
            }

            function loadTimeline(idx) {
                var container = document.getElementById('timelineContent-' + idx);
                if (!container || container.dataset.loaded === 'true') return;

                var claim = claimData[idx];
                if (!claim) return;

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('action', 'get_timeline');
                formData.append('pid', claim.pid);
                formData.append('encounter', claim.encounter);
                formData.append('payer_type', claim.payerType);

                fetch('claim_status_api.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        container.dataset.loaded = 'true';
                        container.innerHTML = renderTimeline(result.timeline || []);
                    })
                    .catch(function(err) {
                        container.innerHTML = '<div class="text-danger"><?php echo xla("Failed to load timeline"); ?>: ' + escapeHtml(err.message) + '</div>';
                    });
            }

            function renderTimeline(events) {
                if (events.length === 0) {
                    return '<div class="text-muted" style="font-size:0.85em;"><?php echo xla("No events recorded yet. Events will appear as claims are synced, checked, and posted."); ?></div>';
                }

                var html = '';
                var eventIcons = {
                    'submitted': 'fa-paper-plane text-primary',
                    'rejected': 'fa-times-circle text-danger',
                    'accepted': 'fa-check-circle text-success',
                    'denied': 'fa-ban text-danger',
                    'status_check_276': 'fa-sync-alt text-info',
                    'era_received': 'fa-file-invoice text-primary',
                    'payment_posted': 'fa-dollar-sign text-success',
                    'requeued': 'fa-redo text-warning',
                    'corrected': 'fa-edit text-info',
                    'manual_note': 'fa-sticky-note text-info',
                    'claimrev_sync': 'fa-cloud-download-alt text-muted'
                };

                var sourceBadges = {
                    'claimrev': 'badge-primary',
                    'payer_277': 'badge-info',
                    'user': 'badge-warning',
                    'system': 'badge-secondary',
                    'era': 'badge-success'
                };

                events.forEach(function(evt) {
                    var iconClass = eventIcons[evt.event_type] || 'fa-circle text-muted';
                    var sourceBadge = sourceBadges[evt.source] || 'badge-secondary';
                    var eventLabel = evt.event_type.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

                    html += '<div class="timeline-event event-' + escapeHtml(evt.event_type) + '">';
                    html += '<div class="d-flex justify-content-between">';
                    html += '<div><i class="fa ' + iconClass + '"></i> <strong>' + escapeHtml(eventLabel) + '</strong>';
                    html += ' <span class="badge ' + sourceBadge + ' event-source">' + escapeHtml(evt.source) + '</span>';
                    html += '</div>';
                    html += '<span class="event-date">' + escapeHtml(evt.created_date) + ' by ' + escapeHtml(evt.created_by) + '</span>';
                    html += '</div>';

                    if (evt.status_description) {
                        html += '<div>' + escapeHtml(evt.status_description) + '</div>';
                    }
                    if (evt.detail_text) {
                        html += '<div class="text-muted">' + escapeHtml(evt.detail_text) + '</div>';
                    }
                    if (evt.amount && parseFloat(evt.amount) !== 0) {
                        html += '<div><strong>$' + parseFloat(evt.amount).toFixed(2) + '</strong></div>';
                    }

                    html += '</div>';
                });

                return html;
            }

            function checkStatus(pid, encounter, payerType, btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('action', 'check_status');
                formData.append('pid', pid);
                formData.append('encounter', encounter);
                formData.append('payer_type', payerType);

                fetch('claim_status_api.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-sync-alt"></i>';

                        if (result.success) {
                            alert(result.message);
                            // Reload timeline if open
                            var idx = findClaimIndex(pid, encounter);
                            if (idx !== -1) {
                                var container = document.getElementById('timelineContent-' + idx);
                                if (container) {
                                    container.dataset.loaded = 'false';
                                    var row = document.getElementById('timeline-' + idx);
                                    if (row && row.classList.contains('show')) {
                                        loadTimeline(idx);
                                    }
                                }
                            }
                        } else {
                            alert(result.message || '<?php echo xla("Status check failed"); ?>');
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-sync-alt"></i>';
                        alert('<?php echo xla("Error"); ?>: ' + err.message);
                    });
            }

            function toggleNoteForm(idx) {
                var form = document.getElementById('noteForm-' + idx);
                if (form) {
                    form.classList.toggle('show');
                }
            }

            function addNote(pid, encounter, payerType, idx) {
                var noteText = document.getElementById('noteText-' + idx).value.trim();
                if (noteText === '') return;

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('action', 'add_note');
                formData.append('pid', pid);
                formData.append('encounter', encounter);
                formData.append('payer_type', payerType);
                formData.append('note_text', noteText);

                fetch('claim_status_api.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.success) {
                            document.getElementById('noteText-' + idx).value = '';
                            document.getElementById('noteForm-' + idx).classList.remove('show');
                            // Reload timeline
                            var container = document.getElementById('timelineContent-' + idx);
                            if (container) {
                                container.dataset.loaded = 'false';
                                loadTimeline(idx);
                            }
                        } else {
                            alert(result.error || '<?php echo xla("Failed to save note"); ?>');
                        }
                    })
                    .catch(function(err) {
                        alert('<?php echo xla("Error"); ?>: ' + err.message);
                    });
            }

            function findClaimIndex(pid, encounter) {
                for (var i = 0; i < claimData.length; i++) {
                    if (claimData[i].pid === pid && claimData[i].encounter === encounter) {
                        return i;
                    }
                }
                return -1;
            }

            function goToPage(page) {
                document.getElementById('pageIndex').value = page;
                document.getElementById('statusSearchForm').querySelector('[name="SubmitButton"]').click();
            }

            function escapeHtml(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }
        </script>
    </body>
</html>
