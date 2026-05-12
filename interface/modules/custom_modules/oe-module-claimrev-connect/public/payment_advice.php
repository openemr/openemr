<?php

/**
 * Payment Advice search page - displays ERA payment data from ClaimRev
 * with OpenEMR claim status, portal links, preview, and posting.
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
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdviceMockService;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePage;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePostingService;

$tab = "payments";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Payment Advice",
        xl("ClaimRev Connect - Payment Advice")
    );
}

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$globalConfig = $bootstrap->getGlobalConfig();
$portalUrl = $globalConfig->getPortalUrl();
$testModeAllowed = $globalConfig->isTestModeEnabled();
$csrfToken = CsrfHelper::collectCsrfToken('payment_advice');

$sortFieldRaw = ModuleInput::postString('sortField');
$sortDirectionRaw = ModuleInput::postString('sortDirection');
$receivedDateStart = ModuleInput::postString('receivedDateStart');
$receivedDateEnd = ModuleInput::postString('receivedDateEnd');
$patientFirstName = ModuleInput::postString('patientFirstName');
$patientLastName = ModuleInput::postString('patientLastName');
$checkNumber = ModuleInput::postString('checkNumber');
$isWorkedFilter = ModuleInput::postString('isWorked');
$serviceDateStart = ModuleInput::postString('serviceDateStart');
$serviceDateEnd = ModuleInput::postString('serviceDateEnd');
$payerNumber = ModuleInput::postString('payerNumber');
$patientControlNumber = ModuleInput::postString('patientControlNumber');
$paymentSearchFilters = [
    'sortField' => $sortFieldRaw,
    'sortDirection' => $sortDirectionRaw,
    'receivedDateStart' => $receivedDateStart,
    'receivedDateEnd' => $receivedDateEnd,
    'patientFirstName' => $patientFirstName,
    'patientLastName' => $patientLastName,
    'checkNumber' => $checkNumber,
    'isWorked' => $isWorkedFilter,
    'serviceDateStart' => $serviceDateStart,
    'serviceDateEnd' => $serviceDateEnd,
    'payerNumber' => $payerNumber,
    'patientControlNumber' => $patientControlNumber,
    'pageIndex' => ModuleInput::postInt('pageIndex'),
];

$datas = [];
$totalRecords = 0;
$pageIndex = ModuleInput::postInt('pageIndex');
$pageSize = 50;
$errorMessage = null;
$testMode = $testModeAllowed && ModuleInput::postExists('testMode');

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    try {
        if ($testMode) {
            $result = PaymentAdviceMockService::generateMockResults($paymentSearchFilters);
        } else {
            $result = PaymentAdvicePage::searchPaymentInfo($paymentSearchFilters);
        }
        $datas = $result['results'];
        $totalRecords = $result['totalRecords'];
    } catch (ClaimRevApiException) {
        $errorMessage = xlt('Failed to search payment advice. Please check your ClaimRev connection settings.');
        $datas = [];
    }
}

// Check which results are already posted
$postedMap = [];
foreach ($datas as $data) {
    $id = $data['paymentAdviceId'];
    $pcn = $data['paymentInfo']['patientControlNumber'];
    $parsed = PaymentAdvicePostingService::parsePatientControlNumber($pcn);
    $pid = $parsed['pid'] ?? 0;
    $enc = $parsed['encounter'] ?? 0;
    if ($id !== '') {
        $check = PaymentAdvicePostingService::isAlreadyPosted($id, $pid, $enc);
        $postedMap[$id] = $check['posted'];
    }
}

$totalPages = ($totalRecords > 0) ? (int) ceil($totalRecords / $pageSize) : 0;
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Payment Advice"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .sortable-header { cursor: pointer; user-select: none; white-space: nowrap; }
            .sortable-header:hover { background-color: rgba(0,0,0,.075); }
            .badge-claim-status { font-size: 0.8em; padding: 3px 7px; }
            .payment-row { cursor: pointer; }
            .payment-row:hover { background-color: rgba(0,0,0,.05); }
            .payment-row.row-posted { background-color: #e8f5e9; }
            .payment-row.row-posted:hover { background-color: #dcedc8; }
            .payment-row.row-denied { background-color: #ffe5e5; }
            .payment-row.row-denied:hover { background-color: #ffd6d6; }
            .payment-detail-row { display: none; }
            .payment-detail-row.show { display: table-row; }
            .detail-label { font-weight: bold; color: #666; font-size: 0.85em; }
            .detail-value { font-size: 0.85em; }
            .preview-panel { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin-top: 10px; }
            .svc-matched { color: #28a745; }
            .svc-unmatched { color: #dc3545; }
            .batch-progress { display: none; }
            .batch-progress.active { display: block; }
            #batchResultsModal .modal-body { max-height: 400px; overflow-y: auto; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="payment_advice.php" id="paymentSearchForm">
                <input type="hidden" name="sortField" id="sortField" value="<?php echo attr($sortFieldRaw); ?>"/>
                <input type="hidden" name="sortDirection" id="sortDirection" value="<?php echo attr($sortDirectionRaw); ?>"/>
                <input type="hidden" name="pageIndex" id="pageIndex" value="<?php echo attr((string) $pageIndex); ?>"/>
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <?php echo xlt("Search Payment Advice"); ?>
                        <button class="btn btn-sm btn-link" type="button" data-toggle="collapse" data-target="#moreFilters" aria-expanded="false">
                            <?php echo xlt("More Filters"); ?>
                        </button>
                    </div>
                    <div class="card-body pb-0">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="receivedDateStart"><?php echo xlt("Received Date Start"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="receivedDateStart" name="receivedDateStart" value="<?php echo attr($receivedDateStart); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="receivedDateEnd"><?php echo xlt("Received Date End"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="receivedDateEnd" name="receivedDateEnd" value="<?php echo attr($receivedDateEnd); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patientFirstName"><?php echo xlt("Patient First"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientFirstName" name="patientFirstName" value="<?php echo attr($patientFirstName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patientLastName"><?php echo xlt("Patient Last"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientLastName" name="patientLastName" value="<?php echo attr($patientLastName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="checkNumber"><?php echo xlt("Check Number"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="checkNumber" name="checkNumber" value="<?php echo attr($checkNumber); ?>"/>
                            </div>
                            <div class="form-group col-md-1">
                                <label for="isWorked"><?php echo xlt("Worked"); ?></label>
                                <select class="form-control form-control-sm" id="isWorked" name="isWorked">
                                    <option value=""><?php echo xlt("All"); ?></option>
                                    <option value="0" <?php echo $isWorkedFilter === '0' ? 'selected' : ''; ?>><?php echo xlt("No"); ?></option>
                                    <option value="1" <?php echo $isWorkedFilter === '1' ? 'selected' : ''; ?>><?php echo xlt("Yes"); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-1 d-flex align-items-end">
                                <div class="w-100">
                                    <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Search"); ?></button>
                                    <?php if ($testModeAllowed) { ?>
                                    <div class="custom-control custom-checkbox mt-1 text-center">
                                        <input type="checkbox" class="custom-control-input" id="testMode" name="testMode" value="1" <?php echo $testMode ? 'checked' : ''; ?>/>
                                        <label class="custom-control-label small text-muted" for="testMode"><?php echo xlt("Test Mode"); ?></label>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="collapse" id="moreFilters">
                            <hr class="mt-0"/>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="serviceDateStart"><?php echo xlt("Service Date Start"); ?></label>
                                    <input type="date" class="form-control form-control-sm" id="serviceDateStart" name="serviceDateStart" value="<?php echo attr($serviceDateStart); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="serviceDateEnd"><?php echo xlt("Service Date End"); ?></label>
                                    <input type="date" class="form-control form-control-sm" id="serviceDateEnd" name="serviceDateEnd" value="<?php echo attr($serviceDateEnd); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="payerNumber"><?php echo xlt("Payer Number"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="payerNumber" name="payerNumber" value="<?php echo attr($payerNumber); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="patientControlNumber"><?php echo xlt("Patient Control #"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="patientControlNumber" name="patientControlNumber" value="<?php echo attr($patientControlNumber); ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($testMode && $datas !== []) { ?>
            <div class="alert alert-warning mt-3 py-2">
                <i class="fa fa-flask"></i> <strong><?php echo xlt("Test Mode"); ?></strong> &mdash;
                <?php echo xlt("This is simulated data for demonstration purposes only. The payment information shown below is not real. Do not post test mode results in a production environment."); ?>
            </div>
        <?php } ?>

        <?php if ($errorMessage !== null) { ?>
            <div class="alert alert-danger mt-3"><?php echo text($errorMessage); ?></div>
        <?php } elseif (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton') && $datas === []) { ?>
            <div class="mt-3"><?php echo xlt("No results found"); ?></div>
        <?php } elseif ($datas !== []) { ?>
            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                <small class="text-muted">
                    <?php echo text((string) $totalRecords); ?> <?php echo xlt("results"); ?>
                    <?php if ($totalPages > 1) { ?>
                        &mdash; <?php echo xlt("Page"); ?> <?php echo text((string) ($pageIndex + 1)); ?> <?php echo xlt("of"); ?> <?php echo text((string) $totalPages); ?>
                    <?php } ?>
                </small>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-success" id="batchPostSelectedBtn" onclick="batchPostSelected()" style="display:none;">
                        <i class="fa fa-check-square"></i> <?php echo xlt("Post Selected"); ?> (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="batchPostBtn" onclick="batchPostAll()">
                        <i class="fa fa-upload"></i> <?php echo xlt("Batch Post All Paid"); ?>
                    </button>
                    <?php if ($totalPages > 1) { ?>
                        <?php if ($pageIndex > 0) { ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex - 1)); ?>)">&laquo; <?php echo xlt("Prev"); ?></button>
                        <?php } ?>
                        <?php if ($pageIndex < $totalPages - 1) { ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex + 1)); ?>)"><?php echo xlt("Next"); ?> &raquo;</button>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <!-- Batch progress bar -->
            <div class="batch-progress" id="batchProgress">
                <div class="alert alert-info">
                    <i class="fa fa-spinner fa-spin"></i>
                    <span id="batchProgressText"><?php echo xlt("Processing..."); ?></span>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="batchProgressBar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <table class="table table-sm table-striped mt-1">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"/></th>
                        <th class="sortable-header" onclick="sortBy('receivedDate')"><?php echo xlt("Received"); ?></th>
                        <th><?php echo xlt("Payer"); ?></th>
                        <th><?php echo xlt("Patient"); ?></th>
                        <th><?php echo xlt("Control #"); ?></th>
                        <th><?php echo xlt("Check #"); ?></th>
                        <th class="text-right"><?php echo xlt("Billed"); ?></th>
                        <th class="text-right"><?php echo xlt("Paid"); ?></th>
                        <th class="text-right"><?php echo xlt("Pt Resp"); ?></th>
                        <th><?php echo xlt("ERA Status"); ?></th>
                        <th><?php echo xlt("OE Status"); ?></th>
                        <th><?php echo xlt("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datas as $idx => $data) {
                        $paymentAdviceId = $data['paymentAdviceId'];
                        $receivedDate = substr($data['receivedDate'], 0, 10);
                        $payerName = $data['payerName'];
                        $payerNumber = $data['payerNumber'];

                        $paymentInfo = $data['paymentInfo'];
                        $patientFirst = $paymentInfo['patientFirstName'];
                        $patientLast = $paymentInfo['patientLastName'];
                        $pcn = $paymentInfo['patientControlNumber'];
                        $claimStatusCode = $paymentInfo['claimStatusCode'];
                        $totalClaimAmount = $paymentInfo['totalClaimAmount'];
                        $claimPaymentAmount = $paymentInfo['claimPaymentAmount'];
                        $patientResponsibility = $paymentInfo['patientResponsibility'];
                        $isWorked = $paymentInfo['isWorked'];

                        $checkInfo = $data['checkInformation'];
                        $checkNumber = $checkInfo['checkNumber'];
                        $checkDate = $checkInfo['checkDate'] !== '' ? substr($checkInfo['checkDate'], 0, 10) : '';

                        // ERA classification from ClaimRev (Paid, Denied, PartiallyPaid, etc.)
                        $eraClassification = $data['eraClassification'];

                        $claimStatusLabels = [
                            '1' => 'Primary',
                            '2' => 'Secondary',
                            '3' => 'Tertiary',
                            '4' => 'Denied',
                            '5' => 'Pended',
                            '22' => 'Reversal',
                        ];
                        $claimStatusLabel = $claimStatusLabels[$claimStatusCode] ?? $claimStatusCode;

                        // Map eraClassification to badge styles
                        $eraClassBadge = match ($eraClassification) {
                            'Paid' => 'badge-success',
                            'Denied' => 'badge-danger',
                            'PartiallyDenied' => 'badge-warning',
                            'PartiallyPaid' => 'badge-info',
                            'PatientResponsibility' => 'badge-secondary',
                            'Pending' => 'badge-warning',
                            default => 'badge-light text-dark',
                        };
                        // User-friendly labels
                        $eraClassLabel = match ($eraClassification) {
                            'Paid' => 'Paid',
                            'Denied' => 'Denied',
                            'PartiallyDenied' => 'Partially Denied',
                            'PartiallyPaid' => 'Partially Paid',
                            'PatientResponsibility' => 'Patient Resp',
                            'Pending' => 'Pending',
                            'Unknown' => 'Unknown',
                            default => $eraClassification,
                        };

                        $oeStatus = PaymentAdvicePage::getOpenEmrClaimStatus($pcn);
                        $isPosted = (bool) ($postedMap[$paymentAdviceId] ?? false);
                        $isPostable = in_array($claimStatusCode, ['1', '2', '3', '4', '5', '22'], true) && !$isPosted;
                        $needsApproval = in_array($claimStatusCode, ['5', '22'], true);
                        $isDenied = ($claimStatusCode === '4' || $eraClassification === 'Denied');

                        $rowClass = 'payment-row';
    if ($isPosted) {
        $rowClass .= ' row-posted';
    } elseif ($isDenied) {
        $rowClass .= ' row-denied';
    }
    ?>
                    <tr class="<?php echo attr($rowClass); ?>" onclick="toggleDetail('<?php echo attr($paymentAdviceId); ?>')" id="row-<?php echo attr($paymentAdviceId); ?>">
                        <td onclick="event.stopPropagation();">
                            <?php if ($isPostable) { ?>
                                <input type="checkbox" class="post-checkbox" data-index="<?php echo attr((string) $idx); ?>" data-id="<?php echo attr($paymentAdviceId); ?>" onchange="updateSelectedCount()"/>
                            <?php } elseif ($isPosted) { ?>
                                <i class="fa fa-check text-success" title="<?php echo xla('Already posted'); ?>"></i>
                            <?php } ?>
                        </td>
                        <td><?php echo text($receivedDate); ?></td>
                        <td><?php echo text($payerName); ?><br/><small class="text-muted"><?php echo text($payerNumber); ?></small></td>
                        <td><?php echo text($patientLast); ?>, <?php echo text($patientFirst); ?></td>
                        <td><?php echo text($pcn); ?></td>
                        <td><?php echo text($checkNumber); ?></td>
                        <td class="text-right"><?php echo text(number_format($totalClaimAmount, 2)); ?></td>
                        <td class="text-right"><?php echo text(number_format($claimPaymentAmount, 2)); ?></td>
                        <td class="text-right"><?php echo text(number_format($patientResponsibility, 2)); ?></td>
                        <td>
                            <?php if ($eraClassification !== '') { ?>
                                <span class="badge <?php echo attr($eraClassBadge); ?> badge-claim-status"><?php echo text($eraClassLabel); ?></span>
                                <br/><small class="text-muted"><?php echo text($claimStatusLabel); ?></small>
                            <?php } else { ?>
                                <span class="badge badge-secondary badge-claim-status"><?php echo text($claimStatusLabel); ?></span>
                            <?php } ?>
                            <?php if ($isPosted) { ?>
                                <br/><span class="badge badge-success badge-claim-status mt-1"><i class="fa fa-check"></i> <?php echo xlt("Posted"); ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($oeStatus !== null) {
                                $oeClass = 'badge-secondary';
                                if ($oeStatus['status'] === 2) {
                                    $oeClass = 'badge-primary';
                                } elseif ($oeStatus['status'] === 3) {
                                    $oeClass = 'badge-success';
                                } elseif ($oeStatus['status'] === 7) {
                                    $oeClass = 'badge-danger';
                                } elseif ($oeStatus['status'] === -1) {
                                    $oeClass = 'badge-light text-muted';
                                }
                                ?>
                                <span class="badge <?php echo attr($oeClass); ?> badge-claim-status"><?php echo text($oeStatus['status_label']); ?></span>
                            <?php } ?>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <?php if ($isPostable && $needsApproval) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="postSingle(<?php echo attr((string) $idx); ?>)" title="<?php echo xla('Requires approval — click to review and post'); ?>">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </button>
                                <?php } elseif ($isPostable) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="postSingle(<?php echo attr((string) $idx); ?>)" title="<?php echo xla('Post to OpenEMR'); ?>">
                                        <i class="fa fa-upload"></i>
                                    </button>
                                <?php } ?>
                                <?php if ($portalUrl !== '' && $paymentAdviceId !== '') { ?>
                                    <a href="<?php echo attr($portalUrl); ?>/reports/scpaymentadvice/<?php echo attr($paymentAdviceId); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?php echo xla('View in ClaimRev Portal'); ?>">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="payment-detail-row" id="detail-<?php echo attr($paymentAdviceId); ?>">
                        <td colspan="12" style="background-color: rgba(0,0,0,.02); padding: 15px 25px;">
                            <div id="preview-<?php echo attr($paymentAdviceId); ?>">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="detail-label"><?php echo xlt("Check Date"); ?></span>
                                        <span class="detail-value"><?php echo text($checkDate); ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="detail-label"><?php echo xlt("ERA Classification"); ?></span>
                                        <span class="detail-value"><?php echo text($eraClassification); ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="detail-label"><?php echo xlt("Worked"); ?></span>
                                        <span class="detail-value"><?php echo $isWorked ? xlt("Yes") : xlt("No"); ?></span>
                                    </div>
                                    <?php if ($oeStatus !== null && $oeStatus['status'] !== -1) { ?>
                                    <div class="col-md-3">
                                        <span class="detail-label"><?php echo xlt("OpenEMR Encounter"); ?></span>
                                        <span class="detail-value"><?php echo text($oeStatus['pid'] . '-' . $oeStatus['encounter']); ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <!-- Preview loads here via AJAX when row is expanded -->
                                <div id="previewContent-<?php echo attr($paymentAdviceId); ?>" class="mt-2"></div>
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

        <!-- Batch Results Modal -->
        <div class="modal fade" id="batchResultsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo xlt("Batch Post Results"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" id="batchResultsBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt("Close"); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Store all payment data for JS access
            var paymentResults = <?php echo json_encode($datas); ?>;
            var csrfToken = <?php echo json_encode($csrfToken); ?>;
            var testMode = <?php echo json_encode($testMode); ?>;

            function toggleDetail(id) {
                var row = document.getElementById('detail-' + id);
                if (row) {
                    var wasHidden = !row.classList.contains('show');
                    row.classList.toggle('show');
                    // Load preview on first expand
                    if (wasHidden) {
                        loadPreview(id);
                    }
                }
            }

            function loadPreview(paymentAdviceId) {
                var container = document.getElementById('previewContent-' + paymentAdviceId);
                if (!container || container.dataset.loaded === 'true') return;

                var data = paymentResults.find(function(d) { return d.paymentAdviceId === paymentAdviceId; });
                if (!data) return;

                container.innerHTML = '<div class="text-muted"><i class="fa fa-spinner fa-spin"></i> <?php echo xla("Loading preview..."); ?></div>';

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('paymentData', JSON.stringify(data));

                fetch('payment_advice_preview.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(preview) {
                        container.dataset.loaded = 'true';
                        container.innerHTML = renderPreview(preview, paymentAdviceId);
                    })
                    .catch(function(err) {
                        container.innerHTML = '<div class="text-danger"><?php echo xla("Failed to load preview"); ?>: ' + err.message + '</div>';
                    });
            }

            function renderPreview(preview, paymentAdviceId) {
                var html = '<div class="preview-panel">';
                html += '<h6><?php echo xla("Posting Preview"); ?></h6>';

                // Errors
                if (preview.errors && preview.errors.length > 0) {
                    html += '<div class="alert alert-danger py-1 px-2 mb-2" style="font-size:0.85em;">';
                    preview.errors.forEach(function(e) { html += '<div>' + escapeHtml(e) + '</div>'; });
                    html += '</div>';
                }

                // Warnings
                if (preview.warnings && preview.warnings.length > 0) {
                    html += '<div class="alert alert-warning py-1 px-2 mb-2" style="font-size:0.85em;">';
                    preview.warnings.forEach(function(w) { html += '<div>' + escapeHtml(w) + '</div>'; });
                    html += '</div>';
                }

                if (preview.alreadyPosted && preview.postingDetails && preview.postingDetails.found) {
                    // Show full posting details for already-posted items
                    var pd = preview.postingDetails;
                    html += '<div class="alert alert-success py-2 px-3 mb-3">';
                    html += '<i class="fa fa-check-circle"></i> <strong><?php echo xla("Posted to OpenEMR"); ?></strong>';
                    html += '</div>';

                    html += '<div class="row mb-2" style="font-size:0.85em;">';
                    html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Session ID"); ?></span><br/>' + escapeHtml(String(pd.session_id)) + '</div>';
                    html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Check Date"); ?></span><br/>' + escapeHtml(pd.check_date) + '</div>';
                    html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Total Paid"); ?></span><br/>$' + formatMoney(pd.pay_total) + '</div>';
                    html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Posted By"); ?></span><br/>' + escapeHtml(pd.post_user || 'N/A') + '</div>';
                    html += '<div class="col-md-3"><span class="detail-label"><?php echo xla("Posted On"); ?></span><br/>' + escapeHtml(pd.created_time) + '</div>';
                    html += '</div>';

                    if (pd.lines && pd.lines.length > 0) {
                        html += '<table class="table table-sm table-bordered mb-2" style="font-size:0.85em;">';
                        html += '<thead><tr>';
                        html += '<th><?php echo xla("Code"); ?></th>';
                        html += '<th><?php echo xla("Modifier"); ?></th>';
                        html += '<th class="text-right"><?php echo xla("Payment"); ?></th>';
                        html += '<th class="text-right"><?php echo xla("Adjustment"); ?></th>';
                        html += '<th><?php echo xla("Memo / Reason"); ?></th>';
                        html += '<th><?php echo xla("Date"); ?></th>';
                        html += '</tr></thead><tbody>';

                        var totalPayments = 0, totalAdj = 0;
                        pd.lines.forEach(function(line) {
                            totalPayments += line.pay_amount;
                            totalAdj += line.adj_amount;
                            var memo = line.reason_code || line.account_code || '';
                            if (line.memo) {
                                memo = memo ? memo + ' — ' + line.memo : line.memo;
                            }
                            html += '<tr>';
                            html += '<td>' + escapeHtml(line.code) + '</td>';
                            html += '<td>' + escapeHtml(line.modifier) + '</td>';
                            html += '<td class="text-right">' + formatMoney(line.pay_amount) + '</td>';
                            html += '<td class="text-right">' + formatMoney(line.adj_amount) + '</td>';
                            html += '<td style="font-size:0.9em;">' + escapeHtml(memo) + '</td>';
                            html += '<td>' + escapeHtml(line.post_date) + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody><tfoot><tr class="font-weight-bold">';
                        html += '<td colspan="2"><?php echo xla("Total"); ?></td>';
                        html += '<td class="text-right">' + formatMoney(totalPayments) + '</td>';
                        html += '<td class="text-right">' + formatMoney(totalAdj) + '</td>';
                        html += '<td colspan="2"></td>';
                        html += '</tr></tfoot></table>';
                    }

                    if (preview.pid) {
                        html += '<button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="openLedgerTab(' + preview.pid + ')">';
                        html += '<i class="fa fa-file-invoice-dollar"></i> <?php echo xla("View Ledger"); ?></button>';
                        html += '<button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="openEncounterTab(' + preview.pid + ', ' + preview.encounter + ')">';
                        html += '<i class="fa fa-user"></i> <?php echo xla("Patient Dashboard"); ?></button>';
                    }

                } else if (preview.alreadyPosted) {
                    html += '<div class="alert alert-info py-1 px-2 mb-2" style="font-size:0.85em;"><i class="fa fa-check"></i> <?php echo xla("Already posted to OpenEMR"); ?></div>';
                } else {
                    // Not yet posted — show service lines preview with match info
                    if (preview.serviceLines && preview.serviceLines.length > 0) {
                        html += '<table class="table table-sm table-bordered mb-2" style="font-size:0.85em;">';
                        html += '<thead><tr>';
                        html += '<th><?php echo xla("Code"); ?></th>';
                        html += '<th><?php echo xla("Modifier"); ?></th>';
                        html += '<th class="text-right"><?php echo xla("Charged"); ?></th>';
                        html += '<th class="text-right"><?php echo xla("Paid"); ?></th>';
                        html += '<th class="text-right"><?php echo xla("Adjusted"); ?></th>';
                        html += '<th><?php echo xla("OE Match"); ?></th>';
                        html += '</tr></thead><tbody>';

                        preview.serviceLines.forEach(function(svc) {
                            html += '<tr>';
                            html += '<td>' + escapeHtml(svc.code) + '</td>';
                            html += '<td>' + escapeHtml(svc.modifier || '') + '</td>';
                            html += '<td class="text-right">' + formatMoney(svc.charged) + '</td>';
                            html += '<td class="text-right">' + formatMoney(svc.paid) + '</td>';
                            html += '<td class="text-right">' + formatMoney(svc.totalAdjusted) + '</td>';
                            html += '<td>';
                            if (svc.matched) {
                                html += '<span class="svc-matched"><i class="fa fa-check"></i> <?php echo xla("Matched"); ?></span>';
                            } else {
                                html += '<span class="svc-unmatched"><i class="fa fa-times"></i> <?php echo xla("No match"); ?></span>';
                            }
                            html += '</td></tr>';
                        });

                        html += '</tbody>';
                        html += '<tfoot><tr class="font-weight-bold">';
                        html += '<td colspan="2"><?php echo xla("Total"); ?></td>';
                        html += '<td class="text-right">' + formatMoney(preview.totalBilled) + '</td>';
                        html += '<td class="text-right">' + formatMoney(preview.totalPaid) + '</td>';
                        html += '<td class="text-right">' + formatMoney(preview.totalAdjusted) + '</td>';
                        html += '<td></td>';
                        html += '</tr></tfoot></table>';
                    }

                    // Post button — different style for items requiring approval
                    if (preview.canPost) {
                        if (preview.requiresApproval) {
                            var reason = getApprovalReason(preview.approvalReason);
                            html += '<button type="button" class="btn btn-warning btn-sm" onclick="postWithApproval(\'' + escapeHtml(paymentAdviceId) + '\', \'' + escapeHtml(reason) + '\')">';
                            html += '<i class="fa fa-exclamation-triangle"></i> <?php echo xla("Post with Approval"); ?></button>';
                        } else {
                            html += '<button type="button" class="btn btn-success btn-sm" onclick="postFromPreview(\'' + escapeHtml(paymentAdviceId) + '\')">';
                            html += '<i class="fa fa-upload"></i> <?php echo xla("Post to OpenEMR"); ?></button>';
                        }
                    }
                }

                html += '</div>';
                return html;
            }

            function getApprovalReason(approvalReason) {
                var reasons = {
                    'reversal': '<?php echo xla("This is a payment reversal. A negative adjustment will be applied. Are you sure?"); ?>',
                    'pended': '<?php echo xla("This claim is pended by the payer. Posting now may need correction later. Are you sure?"); ?>',
                    'secondary_before_primary': '<?php echo xla("Primary insurance has not been posted yet. Posting secondary first may result in incorrect adjustments. Continue?"); ?>',
                    'tertiary_before_secondary': '<?php echo xla("Secondary insurance has not been posted yet. Posting tertiary first may result in incorrect adjustments. Continue?"); ?>'
                };
                return reasons[approvalReason] || '<?php echo xla("This payment requires approval to post. Are you sure?"); ?>';
            }

            function postWithApproval(paymentAdviceId, reason) {
                if (!confirm(reason)) return;
                var data = paymentResults.find(function(d) { return d.paymentAdviceId === paymentAdviceId; });
                if (!data) return;
                var idx = paymentResults.indexOf(data);
                postSingle(idx, true);
            }

            function postSingle(idx, approved) {
                var data = paymentResults[idx];
                if (!data) return;

                if (!approved) {
                    if (!confirm('<?php echo xla("Post this payment to OpenEMR?"); ?>')) return;
                }

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('mode', 'single');
                formData.append('paymentAdviceId', data.paymentAdviceId);
                if (testMode) formData.append('testMode', '1');
                if (approved) formData.append('approved', '1');

                // Show spinner in the detail row while posting
                var detailRow = document.getElementById('detail-' + data.paymentAdviceId);
                var previewContainer = document.getElementById('previewContent-' + data.paymentAdviceId);
                if (detailRow && !detailRow.classList.contains('show')) {
                    detailRow.classList.add('show');
                }
                if (previewContainer) {
                    previewContainer.innerHTML = '<div class="text-muted"><i class="fa fa-spinner fa-spin"></i> <?php echo xla("Posting to OpenEMR..."); ?></div>';
                    previewContainer.dataset.loaded = 'false';
                }

                fetch('payment_advice_post.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.requiresApproval && !approved) {
                            // Server says this needs approval — prompt and retry
                            var reason = getApprovalReason(result.approvalReason || '');
                            if (confirm(reason)) {
                                postSingle(idx, true);
                            } else if (previewContainer) {
                                previewContainer.dataset.loaded = 'false';
                                loadPreview(data.paymentAdviceId);
                            }
                            return;
                        }
                        if (result.success) {
                            markRowAsPosted(data.paymentAdviceId);
                        }
                        showPostResult(data, result);
                    })
                    .catch(function(err) {
                        showPostResult(data, { success: false, message: err.message, posted_lines: 0, session_id: null });
                    });
            }

            function postFromPreview(paymentAdviceId) {
                var data = paymentResults.find(function(d) { return d.paymentAdviceId === paymentAdviceId; });
                if (!data) return;
                var idx = paymentResults.indexOf(data);
                postSingle(idx);
            }

            function batchPostAll() {
                // Collect all non-posted items (server will separate normal vs deferred)
                var postable = [];
                paymentResults.forEach(function(data) {
                    var csc = (data.paymentInfo || {}).claimStatusCode || '';
                    var id = data.paymentAdviceId || '';
                    var row = document.getElementById('row-' + id);
                    var isPosted = row && row.classList.contains('row-posted');
                    // Include processed (1,2,3), denied (4), reversals (22), and pended (5)
                    if (['1', '2', '3', '4', '5', '22'].indexOf(csc) !== -1 && !isPosted) {
                        postable.push(data);
                    }
                });

                if (postable.length === 0) {
                    alert('<?php echo xla("No eligible payments to post. All items are either already posted or have an unrecognized status."); ?>');
                    return;
                }

                // Count how many need approval
                var normalCount = 0;
                var approvalCount = 0;
                postable.forEach(function(data) {
                    var csc = (data.paymentInfo || {}).claimStatusCode || '';
                    if (csc === '5' || csc === '22') {
                        approvalCount++;
                    } else {
                        normalCount++;
                    }
                });

                var msg = '<?php echo xla("Post"); ?> ' + normalCount + ' <?php echo xla("payment(s) to OpenEMR?"); ?>';
                if (approvalCount > 0) {
                    msg += '\n\n' + approvalCount + ' <?php echo xla("item(s) require individual approval (reversals/pended) and will be shown separately."); ?>';
                }
                msg += '\n\n<?php echo xla("Already-posted items will be automatically skipped."); ?>';
                if (!confirm(msg)) {
                    return;
                }

                var progressEl = document.getElementById('batchProgress');
                var progressBar = document.getElementById('batchProgressBar');
                var progressText = document.getElementById('batchProgressText');
                var batchBtn = document.getElementById('batchPostBtn');

                progressEl.classList.add('active');
                batchBtn.disabled = true;

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('mode', 'batch');
                formData.append('paymentAdviceIds', JSON.stringify(postable.map(function(d) { return d.paymentAdviceId; })));
                if (testMode) formData.append('testMode', '1');

                progressText.textContent = '<?php echo xla("Posting"); ?> ' + normalCount + ' <?php echo xla("payment(s)..."); ?>';
                progressBar.style.width = '50%';

                fetch('payment_advice_post.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        progressBar.style.width = '100%';
                        progressEl.classList.remove('active');
                        batchBtn.disabled = false;

                        // Mark posted rows
                        if (result.results) {
                            result.results.forEach(function(r) {
                                if (r.success) {
                                    markRowAsPosted(r.paymentAdviceId);
                                }
                            });
                        }

                        showBatchResults(result);
                    })
                    .catch(function(err) {
                        progressEl.classList.remove('active');
                        batchBtn.disabled = false;
                        alert('<?php echo xla("Batch post failed"); ?>: ' + err.message);
                    });
            }

            function showBatchResults(result) {
                var html = '<div class="mb-3">';
                html += '<div class="row text-center">';
                html += '<div class="col"><h4 class="text-success">' + result.totalPosted + '</h4><small><?php echo xla("Posted"); ?></small></div>';
                html += '<div class="col"><h4 class="text-muted">' + result.totalSkipped + '</h4><small><?php echo xla("Skipped"); ?></small></div>';
                html += '<div class="col"><h4 class="text-danger">' + result.totalErrors + '</h4><small><?php echo xla("Errors"); ?></small></div>';
                if (result.totalDeferred > 0) {
                    html += '<div class="col"><h4 class="text-warning">' + result.totalDeferred + '</h4><small><?php echo xla("Need Approval"); ?></small></div>';
                }
                html += '</div></div>';

                if (result.results && result.results.length > 0) {
                    html += '<table class="table table-sm" style="font-size:0.85em;">';
                    html += '<thead><tr>';
                    html += '<th style="width:30px;"></th>';
                    html += '<th><?php echo xla("Patient"); ?></th>';
                    html += '<th><?php echo xla("Control #"); ?></th>';
                    html += '<th class="text-right"><?php echo xla("Amount"); ?></th>';
                    html += '<th><?php echo xla("Result"); ?></th>';
                    html += '</tr></thead><tbody>';
                    result.results.forEach(function(r) {
                        // Look up the original data for patient info
                        var orig = paymentResults.find(function(d) { return d.paymentAdviceId === r.paymentAdviceId; });
                        var pi = orig ? (orig.paymentInfo || {}) : {};
                        var patName = (pi.patientLastName || '') + ', ' + (pi.patientFirstName || '');
                        var pcn = pi.patientControlNumber || '';
                        var amt = parseFloat(pi.claimPaymentAmount || 0);

                        var cls = r.success ? 'table-success' : (r.skipped ? '' : 'table-danger');
                        var icon = r.success
                            ? '<i class="fa fa-check text-success"></i>'
                            : (r.skipped ? '<i class="fa fa-forward text-muted"></i>' : '<i class="fa fa-times text-danger"></i>');

                        html += '<tr class="' + cls + '">';
                        html += '<td>' + icon + '</td>';
                        html += '<td>' + escapeHtml(patName) + '</td>';
                        html += '<td>' + escapeHtml(pcn) + '</td>';
                        html += '<td class="text-right">' + formatMoney(amt) + '</td>';
                        html += '<td>' + escapeHtml(r.message) + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                }

                // Deferred items needing individual approval
                if (result.deferred && result.deferred.length > 0) {
                    html += '<hr/>';
                    html += '<h6 class="text-warning"><i class="fa fa-exclamation-triangle"></i> <?php echo xla("Items Requiring Approval"); ?></h6>';
                    html += '<p class="text-muted" style="font-size:0.85em;"><?php echo xla("These items were separated from the batch because they are reversals or pended claims. Review and approve each one individually."); ?></p>';
                    html += '<table class="table table-sm" style="font-size:0.85em;">';
                    html += '<thead><tr>';
                    html += '<th><?php echo xla("Patient"); ?></th>';
                    html += '<th><?php echo xla("Control #"); ?></th>';
                    html += '<th><?php echo xla("Type"); ?></th>';
                    html += '<th class="text-right"><?php echo xla("Amount"); ?></th>';
                    html += '<th><?php echo xla("Action"); ?></th>';
                    html += '</tr></thead><tbody>';

                    result.deferred.forEach(function(d) {
                        html += '<tr id="deferred-row-' + escapeHtml(d.paymentAdviceId) + '">';
                        html += '<td>' + escapeHtml(d.patientName) + '</td>';
                        html += '<td>' + escapeHtml(d.pcn) + '</td>';
                        html += '<td><span class="badge badge-warning">' + escapeHtml(d.reason) + '</span></td>';
                        html += '<td class="text-right">' + formatMoney(d.amount) + '</td>';
                        html += '<td>';
                        html += '<button type="button" class="btn btn-warning btn-sm" onclick="postDeferred(\'' + escapeHtml(d.paymentAdviceId) + '\', this)">';
                        html += '<i class="fa fa-check"></i> <?php echo xla("Approve & Post"); ?>';
                        html += '</button>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                }

                document.getElementById('batchResultsBody').innerHTML = html;
                $('#batchResultsModal').modal('show');
            }

            function postDeferred(paymentAdviceId, btn) {
                var data = paymentResults.find(function(d) { return d.paymentAdviceId === paymentAdviceId; });
                if (!data) return;

                var csc = (data.paymentInfo || {}).claimStatusCode || '';
                var reason = csc === '22'
                    ? '<?php echo xla("This is a payment reversal. A negative adjustment will be applied. Are you sure?"); ?>'
                    : '<?php echo xla("This claim is pended by the payer. Posting now may need correction later. Are you sure?"); ?>';

                if (!confirm(reason)) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('mode', 'single');
                formData.append('paymentAdviceId', data.paymentAdviceId);
                formData.append('approved', '1');
                if (testMode) formData.append('testMode', '1');

                fetch('payment_advice_post.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        var row = document.getElementById('deferred-row-' + paymentAdviceId);
                        if (result.success) {
                            markRowAsPosted(paymentAdviceId);
                            if (row) {
                                row.classList.add('table-success');
                                row.querySelector('td:last-child').innerHTML = '<i class="fa fa-check text-success"></i> <?php echo xla("Posted"); ?>';
                            }
                        } else {
                            if (row) {
                                row.classList.add('table-danger');
                                row.querySelector('td:last-child').innerHTML = '<i class="fa fa-times text-danger"></i> ' + escapeHtml(result.message);
                            }
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-check"></i> <?php echo xla("Approve & Post"); ?>';
                        alert('<?php echo xla("Error"); ?>: ' + err.message);
                    });
            }

            function showPostResult(data, result) {
                var id = data.paymentAdviceId;
                var detailRow = document.getElementById('detail-' + id);
                var container = document.getElementById('previewContent-' + id);

                // Make sure the detail row is visible
                if (detailRow && !detailRow.classList.contains('show')) {
                    detailRow.classList.add('show');
                }
                if (!container) return;

                var paymentInfo = data.paymentInfo || {};
                var checkInfo = data.checkInformation || {};
                var patientName = (paymentInfo.patientLastName || '') + ', ' + (paymentInfo.patientFirstName || '');
                var pcn = paymentInfo.patientControlNumber || '';

                var html = '<div class="preview-panel">';

                if (result.success) {
                    html += '<div class="alert alert-success py-2 px-3 mb-3">';
                    html += '<i class="fa fa-check-circle"></i> <strong><?php echo xla("Posted Successfully"); ?></strong>';
                    html += '<div class="mt-1" style="font-size:0.9em;">' + escapeHtml(result.message) + '</div>';
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-danger py-2 px-3 mb-3">';
                    html += '<i class="fa fa-times-circle"></i> <strong><?php echo xla("Post Failed"); ?></strong>';
                    html += '<div class="mt-1" style="font-size:0.9em;">' + escapeHtml(result.message) + '</div>';
                    html += '</div>';
                }

                // Summary details
                html += '<div class="row mb-2" style="font-size:0.85em;">';
                html += '<div class="col-md-3"><span class="detail-label"><?php echo xla("Patient"); ?></span><br/>' + escapeHtml(patientName) + '</div>';
                html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Control #"); ?></span><br/>' + escapeHtml(pcn) + '</div>';
                html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Check #"); ?></span><br/>' + escapeHtml(checkInfo.checkNumber || '') + '</div>';
                if (result.session_id) {
                    html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Session ID"); ?></span><br/>' + escapeHtml(String(result.session_id)) + '</div>';
                }
                html += '<div class="col-md-2"><span class="detail-label"><?php echo xla("Lines Posted"); ?></span><br/>' + (result.posted_lines || 0) + '</div>';
                html += '</div>';

                // Show service line breakdown from original data
                var serviceLines = paymentInfo.servicePaymentInfos || [];
                if (serviceLines.length > 0) {
                    html += '<table class="table table-sm table-bordered mb-2" style="font-size:0.85em;">';
                    html += '<thead><tr>';
                    html += '<th><?php echo xla("Code"); ?></th>';
                    html += '<th><?php echo xla("Modifier"); ?></th>';
                    html += '<th class="text-right"><?php echo xla("Charged"); ?></th>';
                    html += '<th class="text-right"><?php echo xla("Paid"); ?></th>';
                    html += '<th class="text-right"><?php echo xla("Adjusted"); ?></th>';
                    html += '</tr></thead><tbody>';

                    var totalCharged = 0, totalPaid = 0, totalAdj = 0;
                    serviceLines.forEach(function(svc) {
                        var charged = parseFloat(svc.chargeAmount || 0);
                        var paid = parseFloat(svc.paymentAmount || 0);
                        var adj = 0;
                        (svc.adjustmentGroups || []).forEach(function(g) {
                            (g.adjustments || []).forEach(function(a) {
                                adj += parseFloat(a.adjustmentAmount || 0);
                            });
                        });
                        totalCharged += charged;
                        totalPaid += paid;
                        totalAdj += adj;

                        html += '<tr>';
                        html += '<td>' + escapeHtml(svc.procedureCode || '') + '</td>';
                        html += '<td>' + escapeHtml(svc.modifier1 || '') + '</td>';
                        html += '<td class="text-right">' + formatMoney(charged) + '</td>';
                        html += '<td class="text-right">' + formatMoney(paid) + '</td>';
                        html += '<td class="text-right">' + formatMoney(adj) + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody><tfoot><tr class="font-weight-bold">';
                    html += '<td colspan="2"><?php echo xla("Total"); ?></td>';
                    html += '<td class="text-right">' + formatMoney(totalCharged) + '</td>';
                    html += '<td class="text-right">' + formatMoney(totalPaid) + '</td>';
                    html += '<td class="text-right">' + formatMoney(totalAdj) + '</td>';
                    html += '</tr></tfoot></table>';
                }

                if (result.success) {
                    var pi = data.paymentInfo || {};
                    var pcnParts = (pi.patientControlNumber || '').split(/[\s\-]/);
                    var resPid = pcnParts[0] || '';
                    var resEnc = pcnParts[1] || '';
                    if (resPid) {
                        html += '<button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="openLedgerTab(' + resPid + ')">';
                        html += '<i class="fa fa-file-invoice-dollar"></i> <?php echo xla("View Ledger"); ?></button>';
                        html += '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEncounterTab(' + resPid + ', ' + resEnc + ')">';
                        html += '<i class="fa fa-user"></i> <?php echo xla("Patient Dashboard"); ?></button>';
                    }
                }

                html += '</div>';
                container.innerHTML = html;
                container.dataset.loaded = 'true';
            }

            function markRowAsPosted(paymentAdviceId) {
                var row = document.getElementById('row-' + paymentAdviceId);
                if (row) {
                    row.classList.add('row-posted');
                    row.classList.remove('payment-row');
                    // Update checkbox cell
                    var checkboxCell = row.querySelector('td:first-child');
                    if (checkboxCell) {
                        checkboxCell.innerHTML = '<i class="fa fa-check text-success" title="<?php echo xla("Posted"); ?>"></i>';
                    }
                    // Remove post button
                    var postBtn = row.querySelector('.btn-outline-success');
                    if (postBtn) postBtn.remove();
                    // Add posted badge
                    var statusCell = row.querySelectorAll('td')[9]; // ERA Status column
                    if (statusCell && !statusCell.querySelector('.badge-success')) {
                        statusCell.innerHTML += '<br/><span class="badge badge-success badge-claim-status mt-1"><i class="fa fa-check"></i> <?php echo xla("Posted"); ?></span>';
                    }
                }
            }

            function toggleSelectAll(el) {
                document.querySelectorAll('.post-checkbox').forEach(function(cb) {
                    cb.checked = el.checked;
                });
                updateSelectedCount();
            }

            function updateSelectedCount() {
                var checked = document.querySelectorAll('.post-checkbox:checked');
                var btn = document.getElementById('batchPostSelectedBtn');
                var countEl = document.getElementById('selectedCount');
                if (checked.length > 0) {
                    btn.style.display = '';
                    countEl.textContent = checked.length;
                } else {
                    btn.style.display = 'none';
                }
            }

            function batchPostSelected() {
                var checked = document.querySelectorAll('.post-checkbox:checked');
                if (checked.length === 0) return;

                var postable = [];
                checked.forEach(function(cb) {
                    var idx = parseInt(cb.dataset.index);
                    var data = paymentResults[idx];
                    if (data) {
                        postable.push(data);
                    }
                });

                if (postable.length === 0) return;

                var normalCount = 0;
                var approvalCount = 0;
                postable.forEach(function(data) {
                    var csc = (data.paymentInfo || {}).claimStatusCode || '';
                    if (csc === '5' || csc === '22') {
                        approvalCount++;
                    } else {
                        normalCount++;
                    }
                });

                var msg = '<?php echo xla("Post"); ?> ' + postable.length + ' <?php echo xla("selected payment(s) to OpenEMR?"); ?>';
                if (approvalCount > 0) {
                    msg += '\n\n' + approvalCount + ' <?php echo xla("item(s) require individual approval (reversals/pended) and will be shown separately."); ?>';
                }
                msg += '\n\n<?php echo xla("Already-posted items will be automatically skipped."); ?>';
                if (!confirm(msg)) return;

                var progressEl = document.getElementById('batchProgress');
                var progressBar = document.getElementById('batchProgressBar');
                var progressText = document.getElementById('batchProgressText');
                var batchBtn = document.getElementById('batchPostSelectedBtn');

                progressEl.classList.add('active');
                batchBtn.disabled = true;

                var formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('mode', 'batch');
                formData.append('paymentAdviceIds', JSON.stringify(postable.map(function(d) { return d.paymentAdviceId; })));
                if (testMode) formData.append('testMode', '1');

                progressText.textContent = '<?php echo xla("Posting"); ?> ' + postable.length + ' <?php echo xla("selected payment(s)..."); ?>';
                progressBar.style.width = '50%';

                fetch('payment_advice_post.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        progressBar.style.width = '100%';
                        progressEl.classList.remove('active');
                        batchBtn.disabled = false;

                        if (result.results) {
                            result.results.forEach(function(r) {
                                if (r.success) {
                                    markRowAsPosted(r.paymentAdviceId);
                                }
                            });
                        }

                        // Uncheck all and hide the button
                        document.querySelectorAll('.post-checkbox:checked').forEach(function(cb) { cb.checked = false; });
                        document.getElementById('selectAll').checked = false;
                        updateSelectedCount();

                        showBatchResults(result);
                    })
                    .catch(function(err) {
                        progressEl.classList.remove('active');
                        batchBtn.disabled = false;
                        alert('<?php echo xla("Batch post failed"); ?>: ' + err.message);
                    });
            }

            function goToPage(page) {
                document.getElementById('pageIndex').value = page;
                document.getElementById('paymentSearchForm').querySelector('[name="SubmitButton"]').click();
            }

            function sortBy(field) {
                var currentField = document.getElementById('sortField').value;
                var currentDir = document.getElementById('sortDirection').value;
                if (currentField === field) {
                    document.getElementById('sortDirection').value = (currentDir === 'asc') ? 'desc' : 'asc';
                } else {
                    document.getElementById('sortField').value = field;
                    document.getElementById('sortDirection').value = 'asc';
                }
                document.getElementById('pageIndex').value = 0;
                document.getElementById('paymentSearchForm').querySelector('[name="SubmitButton"]').click();
            }

            function openEncounterTab(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr(\OpenEMR\Core\OEGlobalsBag::getInstance()->getString('webroot')); ?>/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(pid) + '&set_encounterid=' + encodeURIComponent(encounter);
            }

            function openLedgerTab(pid) {
                top.restoreSession();
                var url = '<?php echo attr(\OpenEMR\Core\OEGlobalsBag::getInstance()->getString('webroot')); ?>/interface/reports/pat_ledger.php?form=1&patient_id=' + encodeURIComponent(pid);
                top.navigateTab(url, 'ledger', function() {
                    top.activateTabByName('ledger', true);
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            function formatMoney(val) {
                return parseFloat(val || 0).toFixed(2);
            }
        </script>
    </body>
</html>
