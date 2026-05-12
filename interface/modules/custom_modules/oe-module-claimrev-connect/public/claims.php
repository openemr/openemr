<?php

/**
 * Claims search page with advanced filters, pagination, sorting, and expandable details.
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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\ClaimsPage;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePage;
use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;

$tab = "claims";

// Ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - Claims", xl("ClaimRev Connect - Claims"));
}

$claimStatuses = ClaimsPage::getClaimStatuses();

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$portalUrl = $bootstrap->getGlobalConfig()->getPortalUrl();
$csrfToken = CsrfHelper::collectCsrfToken('claims');
$webRoot = OEGlobalsBag::getInstance()->getString('webroot');

// Pull all known POST filters once into typed locals; the form/template echoes these.
$sortFieldRaw = ModuleInput::postString('sortField');
$sortDirectionRaw = ModuleInput::postString('sortDirection');
$startDate = ModuleInput::postString('startDate');
$endDate = ModuleInput::postString('endDate');
$patFirstName = ModuleInput::postString('patFirstName');
$patLastName = ModuleInput::postString('patLastName');
$statusIdFilter = ModuleInput::postString('statusId');
$serviceDateStart = ModuleInput::postString('serviceDateStart');
$serviceDateEnd = ModuleInput::postString('serviceDateEnd');
$patientBirthDate = ModuleInput::postString('patientBirthDate');
$patientGender = ModuleInput::postString('patientGender');
$payerName = ModuleInput::postString('payerName');
$payerNumber = ModuleInput::postString('payerNumber');
$billingProviderNpi = ModuleInput::postString('billingProviderNpi');
$traceNumber = ModuleInput::postString('traceNumber');
$patientControlNumber = ModuleInput::postString('patientControlNumber');
$payerControlNumber = ModuleInput::postString('payerControlNumber');
$payerPaidAmtStart = ModuleInput::postString('payerPaidAmtStart');
$payerPaidAmtEnd = ModuleInput::postString('payerPaidAmtEnd');
$errorMessageFilter = ModuleInput::postString('errorMessage');

$searchFilters = [
    'sortField' => $sortFieldRaw,
    'sortDirection' => $sortDirectionRaw,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'patFirstName' => $patFirstName,
    'patLastName' => $patLastName,
    'statusId' => $statusIdFilter,
    'serviceDateStart' => $serviceDateStart,
    'serviceDateEnd' => $serviceDateEnd,
    'patientBirthDate' => $patientBirthDate,
    'patientGender' => $patientGender,
    'payerName' => $payerName,
    'payerNumber' => $payerNumber,
    'billingProviderNpi' => $billingProviderNpi,
    'traceNumber' => $traceNumber,
    'patientControlNumber' => $patientControlNumber,
    'payerControlNumber' => $payerControlNumber,
    'payerPaidAmtStart' => $payerPaidAmtStart,
    'payerPaidAmtEnd' => $payerPaidAmtEnd,
    'errorMessage' => $errorMessageFilter,
    'pageIndex' => ModuleInput::postInt('pageIndex'),
];
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Claims"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .claim-detail-row { display: none; }
            .claim-detail-row.show { display: table-row; }
            .claim-row { cursor: pointer; }
            .claim-row:hover { background-color: rgba(0,0,0,.05); }
            .claim-row.row-rejected { background-color: #ffe5e5; }
            .claim-row.row-rejected:hover { background-color: #ffd6d6; }
            .claim-row.row-accepted { background-color: #e6ffed; }
            .claim-row.row-accepted:hover { background-color: #d6ffe0; }
            .claim-row.row-pending { background-color: #fff9e5; }
            .claim-row.row-pending:hover { background-color: #fff3cc; }
            .badge-status { font-size: 0.85em; padding: 4px 8px; }
            .claim-detail-cell { background-color: rgba(0,0,0,.02); }
            .detail-label { font-weight: bold; color: #666; font-size: 0.85em; }
            .detail-value { font-size: 0.85em; }
            .sortable-header { cursor: pointer; user-select: none; white-space: nowrap; }
            .sortable-header:hover { background-color: rgba(0,0,0,.075); }
            .status-icons { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
            .status-icon { font-size: 1.1em; }
            .status-icon.text-accepted { color: #28a745; }
            .status-icon.text-rejected { color: #dc3545; }
            .status-icon.text-pending { color: #6c757d; }
            .status-icon.text-warning { color: #ff9800; }
            .status-icon.text-processing { color: #ffc107; }
            .status-icon.text-payer-pending { color: #9c27b0; }
            .status-icon.text-era-paid { color: #28a745; }
            .status-icon.text-era-partial { color: #4caf50; }
            .status-icon.text-era-denied { color: #f44336; }
            .status-icon.text-era-pending { color: #6c757d; }
            .status-label { font-size: 0.8em; display: block; margin-top: 2px; }
            .badge-oe-status { font-size: 0.8em; padding: 3px 7px; }
            .oe-status-cell { min-width: 80px; }
            .action-btn-group .btn { padding: 2px 6px; font-size: 0.8em; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="claims.php" id="claimSearchForm">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrfToken); ?>"/>
                <input type="hidden" name="sortField" id="sortField" value="<?php echo attr($sortFieldRaw); ?>"/>
                <input type="hidden" name="sortDirection" id="sortDirection" value="<?php echo attr($sortDirectionRaw); ?>"/>
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <?php echo xlt("Search Claims"); ?>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSearchBtn" title="<?php echo xla("Clear search and saved filters"); ?>">
                                <i class="fa fa-times"></i> <?php echo xlt("Clear"); ?>
                            </button>
                            <button class="btn btn-sm btn-link" type="button" data-toggle="collapse" data-target="#moreFilters" aria-expanded="false">
                                <?php echo xlt("More Filters"); ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <!-- Primary filters -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="startDate"><?php echo xlt("Send Date Start"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="startDate" name="startDate" value="<?php echo attr($startDate); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="endDate"><?php echo xlt("Send Date End"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="endDate" name="endDate" value="<?php echo attr($endDate); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patFirstName"><?php echo xlt("Patient First"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patFirstName" name="patFirstName" value="<?php echo attr($patFirstName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patLastName"><?php echo xlt("Patient Last"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patLastName" name="patLastName" value="<?php echo attr($patLastName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="statusId"><?php echo xlt("Claim Status"); ?></label>
                                <select class="form-control form-control-sm" id="statusId" name="statusId">
                                    <option value=""><?php echo xlt("All"); ?></option>
                                    <?php foreach ($claimStatuses as $status) {
                                        $statusOptId = TypeCoerce::asString($status['listItemId'] ?? '');
                                        $statusOptName = TypeCoerce::asString($status['listName'] ?? '');
                                        if ($statusOptName === '') {
                                            continue;
                                        }
                                        ?>
                                        <option value="<?php echo attr($statusOptId); ?>" <?php echo $statusIdFilter !== '' && $statusIdFilter === $statusOptId ? 'selected' : ''; ?>><?php echo text($statusOptName); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Search"); ?></button>
                            </div>
                        </div>

                        <!-- Additional filters - collapsible -->
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
                                    <label for="patientBirthDate"><?php echo xlt("Date of Birth"); ?></label>
                                    <input type="date" class="form-control form-control-sm" id="patientBirthDate" name="patientBirthDate" value="<?php echo attr($patientBirthDate); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="patientGender"><?php echo xlt("Gender"); ?></label>
                                    <select class="form-control form-control-sm" id="patientGender" name="patientGender">
                                        <option value=""><?php echo xlt("All"); ?></option>
                                        <option value="M" <?php echo $patientGender === 'M' ? 'selected' : ''; ?>><?php echo xlt("Male"); ?></option>
                                        <option value="F" <?php echo $patientGender === 'F' ? 'selected' : ''; ?>><?php echo xlt("Female"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="payerName"><?php echo xlt("Payer Name"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="payerNumber"><?php echo xlt("Payer Number"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="payerNumber" name="payerNumber" value="<?php echo attr($payerNumber); ?>"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="billingProviderNpi"><?php echo xlt("Billing NPI"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="billingProviderNpi" name="billingProviderNpi" value="<?php echo attr($billingProviderNpi); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="traceNumber"><?php echo xlt("Trace Number"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="traceNumber" name="traceNumber" value="<?php echo attr($traceNumber); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="patientControlNumber"><?php echo xlt("Patient Control #"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="patientControlNumber" name="patientControlNumber" value="<?php echo attr($patientControlNumber); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="payerControlNumber"><?php echo xlt("Payer Control #"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="payerControlNumber" name="payerControlNumber" value="<?php echo attr($payerControlNumber); ?>"/>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="payerPaidAmtStart"><?php echo xlt("Paid Min"); ?></label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="payerPaidAmtStart" name="payerPaidAmtStart" value="<?php echo attr($payerPaidAmtStart); ?>"/>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="payerPaidAmtEnd"><?php echo xlt("Paid Max"); ?></label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="payerPaidAmtEnd" name="payerPaidAmtEnd" value="<?php echo attr($payerPaidAmtEnd); ?>"/>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="errorMessage"><?php echo xlt("Error Message"); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="errorMessage" name="errorMessage" value="<?php echo attr($errorMessageFilter); ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php
            $datas = [];
            $totalRecords = 0;
            $pageIndex = ModuleInput::postInt('pageIndex');
            $pageSize = 50;
            $hasSubmit = ModuleInput::postExists('SubmitButton') || ModuleInput::postExists('pageIndex');
        if ($hasSubmit) {
            try {
                $pagedResult = ClaimsPage::searchClaims($searchFilters);
                $datas = $pagedResult['results'];
                $totalRecords = $pagedResult['totalRecords'];
            } catch (\RuntimeException | \LogicException $t) {
                echo "<div class='alert alert-danger mt-3'>" . text($t->getMessage()) . "</div>";
            }
        }
        if ($datas === []) {
            if ($hasSubmit) {
                echo "<div class='alert alert-info mt-3'>" . xlt("No results found") . "</div>";
            }
        } else {
            $totalPages = (int) ceil($totalRecords / $pageSize);
            // Validate sort inputs against allowlists. The integer index is
            // laundered through intval() so the final $currentSort value is
            // sourced from a static literal array, not from $_POST.
            $validSortFields = [
                '',
                'MainProperties.PatientLastName',
                'PayerName',
                'MainProperties.StartServiceDate',
                'ReceivedDate',
            ];
            $rawIdx = array_search($sortFieldRaw, $validSortFields, true);
            $sortIdx = intval($rawIdx === false ? 0 : $rawIdx);
            $currentSort = $validSortFields[$sortIdx];
            $currentDir = $sortDirectionRaw === 'desc' ? 'desc' : 'asc';
            // Helper closure to render sort indicator (defined inside the
            // template scope to capture $currentSort/$currentDir without
            // creating a global function).
            $sortIcon = static function (string $field) use ($currentSort, $currentDir): string {
                if ($currentSort !== $field) {
                    return ' <i class="fa fa-sort text-muted"></i>';
                }
                return $currentDir === 'desc'
                    ? ' <i class="fa fa-sort-down"></i>'
                    : ' <i class="fa fa-sort-up"></i>';
            };
            ?>
                <div class="mt-3 mb-2 d-flex justify-content-between align-items-center">
                    <span><?php echo text((string) $totalRecords) . " " . xlt("total results"); ?></span>
                    <div>
                        <span class="text-muted small mr-3"><?php echo xlt("Click a row to expand details"); ?></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportCsvBtn">
                            <i class="fa fa-download"></i> <?php echo xlt("Export CSV"); ?>
                        </button>
                    </div>
                    <span><?php echo xlt("Page") . " " . text((string) ($pageIndex + 1)) . " " . xlt("of") . " " . text((string) $totalPages); ?></span>
                </div>
                <table class="table table-sm table-bordered" id="claimsTable">
                <thead class="thead-light">
                    <tr>
                        <th scope="col"><?php echo xlt("Status"); ?></th>
                        <th scope="col" class="sortable-header" data-sort="MainProperties.PatientLastName"><?php echo xlt("Patient"); ?><?php echo $sortIcon('MainProperties.PatientLastName'); ?></th>
                        <th scope="col" class="sortable-header" data-sort="PayerName"><?php echo xlt("Payer"); ?><?php echo $sortIcon('PayerName'); ?></th>
                        <th scope="col"><?php echo xlt("Provider"); ?></th>
                        <th scope="col" class="sortable-header" data-sort="MainProperties.StartServiceDate"><?php echo xlt("Service Date"); ?><?php echo $sortIcon('MainProperties.StartServiceDate'); ?></th>
                        <th scope="col" class="sortable-header" data-sort="ReceivedDate"><?php echo xlt("Received"); ?><?php echo $sortIcon('ReceivedDate'); ?></th>
                        <th scope="col" class="text-right"><?php echo xlt("Billed"); ?></th>
                        <th scope="col" class="text-right"><?php echo xlt("Paid"); ?></th>
                        <th scope="col"><?php echo xlt("OE Status"); ?></th>
                        <th scope="col" class="text-center"><?php echo xlt("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rowIndex = 0;
                    foreach ($datas as $data) {
                        $statusName = TypeCoerce::asString($data->statusName ?? '');
                        $statusId = TypeCoerce::asInt($data->statusId ?? 0);
                        $payerFileStatusId = TypeCoerce::asInt($data->payerFileStatusId ?? 0);
                        $payerFileStatusName = TypeCoerce::asString($data->payerFileStatusName ?? '');
                        $payerAcceptanceStatusId = TypeCoerce::asInt($data->payerAcceptanceStatusId ?? 0);
                        $payerAcceptance = TypeCoerce::asString($data->payerAcceptanceStatusName ?? '');
                        $paymentAdviceStatusId = TypeCoerce::asInt($data->paymentAdviceStatusId ?? 0);
                        $paymentAdvice = TypeCoerce::asString($data->paymentAdviceStatusName ?? '');
                        $eraClassification = TypeCoerce::asString($data->eraClassification ?? '');

                        // Claim status icon (received/processing)
                        if ($statusId === 10) {
                            $claimIcon = 'fa-times-circle';
                            $claimIconClass = 'text-rejected';
                        } elseif (in_array($statusId, [16, 17])) {
                            $claimIcon = 'fa-ban';
                            $claimIconClass = 'text-rejected';
                        } elseif (in_array($statusId, [7, 8, 9, 18])) {
                            $claimIcon = 'fa-paper-plane';
                            $claimIconClass = 'text-accepted';
                        } else {
                            $claimIcon = 'fa-cogs';
                            $claimIconClass = 'text-processing';
                        }

                        // File status icon
                        if ($payerFileStatusId === 3) {
                            $fileIcon = 'fa-times-circle';
                            $fileIconClass = 'text-rejected';
                        } elseif ($payerFileStatusId === 2) {
                            $fileIcon = 'fa-check-circle';
                            $fileIconClass = 'text-accepted';
                        } elseif ($payerFileStatusId === 1) {
                            $fileIcon = 'fa-hourglass-half';
                            $fileIconClass = 'text-pending';
                        } else {
                            $fileIcon = 'fa-clock';
                            $fileIconClass = 'text-pending';
                        }

                        // Payer acceptance icon
                        if ($payerAcceptanceStatusId === 3) {
                            $payerIcon = 'fa-times-circle';
                            $payerIconClass = 'text-rejected';
                        } elseif ($payerAcceptanceStatusId === 4) {
                            $payerIcon = 'fa-thumbs-up';
                            $payerIconClass = 'text-accepted';
                        } elseif ($payerAcceptanceStatusId === 5) {
                            $payerIcon = 'fa-clock';
                            $payerIconClass = 'text-payer-pending';
                        } elseif ($payerAcceptanceStatusId === 6) {
                            $payerIcon = 'fa-question-circle';
                            $payerIconClass = 'text-warning';
                        } elseif (in_array($payerAcceptanceStatusId, [1, 2])) {
                            $payerIcon = 'fa-hourglass-half';
                            $payerIconClass = 'text-pending';
                        } else {
                            $payerIcon = 'fa-clock';
                            $payerIconClass = 'text-pending';
                        }

                        // ERA icon
                        $eraIcon = '';
                        $eraIconClass = '';
                        if ($paymentAdviceStatusId > 0 || $eraClassification !== '') {
                            if (stripos($eraClassification, 'denied') !== false) {
                                $eraIcon = 'fa-times-circle';
                                $eraIconClass = 'text-era-denied';
                            } elseif (stripos($eraClassification, 'partial') !== false) {
                                $eraIcon = 'fa-adjust';
                                $eraIconClass = 'text-era-partial';
                            } elseif (stripos($eraClassification, 'paid') !== false || stripos($eraClassification, 'contractual') !== false) {
                                $eraIcon = 'fa-dollar-sign';
                                $eraIconClass = 'text-era-paid';
                            } elseif (stripos($eraClassification, 'pending') !== false) {
                                $eraIcon = 'fa-clock';
                                $eraIconClass = 'text-era-pending';
                            } elseif ($paymentAdviceStatusId > 0) {
                                $eraIcon = 'fa-file-invoice-dollar';
                                $eraIconClass = 'text-pending';
                            }
                        }

                        // Row class based on portal logic
                        $rowClass = '';
                        if ($statusId === 10 || $payerAcceptanceStatusId === 3) {
                            $rowClass = 'row-rejected';
                        } elseif ($payerAcceptanceStatusId === 4) {
                            $rowClass = 'row-accepted';
                        } elseif ($statusId === 1) {
                            $rowClass = 'row-pending';
                        }

                        // Look up OpenEMR claim status
                        $pcn = TypeCoerce::asString($data->patientControlNumber ?? '');
                        $oeStatus = PaymentAdvicePage::getOpenEmrClaimStatus($pcn);
                        $isRejected = in_array($statusId, [10, 16, 17]) || $payerAcceptanceStatusId === 3;

                        $isWorked = TypeCoerce::asBool($data->isWorked ?? false);
                        $patientLastName = TypeCoerce::asString($data->pLastName ?? '');
                        $patientFirstName = TypeCoerce::asString($data->pFirstName ?? '');
                        $birthDate = TypeCoerce::asString($data->birthDate ?? '');
                        $payerName = TypeCoerce::asString($data->payerName ?? '');
                        $payerNumberData = TypeCoerce::asString($data->payerNumber ?? '');
                        $providerLastName = TypeCoerce::asString($data->providerLastName ?? '');
                        $providerFirstName = TypeCoerce::asString($data->providerFirstName ?? '');
                        $providerNpiData = TypeCoerce::asString($data->providerNpi ?? '');
                        $serviceDate = TypeCoerce::asString($data->serviceDate ?? '');
                        $serviceDateEndData = TypeCoerce::asString($data->serviceDateEnd ?? '');
                        $receivedDate = TypeCoerce::asString($data->receivedDate ?? '');
                        $billedAmount = TypeCoerce::asFloat($data->billedAmount ?? 0);
                        $payerPaidAmount = TypeCoerce::asFloat($data->payerPaidAmount ?? 0);
                        $objectId = TypeCoerce::asString($data->objectId ?? '');
                        $claimTypeId = TypeCoerce::asInt($data->claimTypeId ?? 1);
                        $editorRoute = '';
                        if ($objectId !== '') {
                            $editorRoute = match ($claimTypeId) {
                                2 => '/claimeditor/institutionaleditor/',
                                3 => '/claimeditor/dentaleditor/',
                                default => '/claimeditor/professionaleditor/',
                            };
                        }
                        $errorCount = TypeCoerce::asInt($data->errorCount ?? 0);
                        ?>
                        <tr class="claim-row <?php echo attr($rowClass); ?>" data-target="#detail-<?php echo attr((string) $rowIndex); ?>">
                            <td>
                                <div class="status-icons">
                                    <span class="status-icon <?php echo attr($claimIconClass); ?>" title="<?php echo xla("Claim"); ?>: <?php echo attr($statusName); ?>">
                                        <i class="fa <?php echo attr($claimIcon); ?>"></i>
                                    </span>
                                    <span class="status-icon <?php echo attr($fileIconClass); ?>" title="<?php echo xla("File"); ?>: <?php echo attr($payerFileStatusName); ?>">
                                        <i class="fa <?php echo attr($fileIcon); ?>"></i>
                                    </span>
                                    <span class="status-icon <?php echo attr($payerIconClass); ?>" title="<?php echo xla("Payer"); ?>: <?php echo attr($payerAcceptance); ?>">
                                        <i class="fa <?php echo attr($payerIcon); ?>"></i>
                                    </span>
                                    <?php if ($eraIcon !== '') { ?>
                                        <span class="status-icon <?php echo attr($eraIconClass); ?>" title="<?php echo xla("ERA"); ?>: <?php echo attr($eraClassification !== '' ? $eraClassification : $paymentAdvice); ?>">
                                            <i class="fa <?php echo attr($eraIcon); ?>"></i>
                                        </span>
                                    <?php } ?>
                                    <?php if ($errorCount > 0) { ?>
                                        <span class="status-icon text-rejected" title="<?php echo attr((string) $errorCount); ?> <?php echo xla("errors"); ?>">
                                            <i class="fa fa-exclamation-triangle"></i>
                                        </span>
                                    <?php } ?>
                                </div>
                                <span class="status-label text-muted"><?php echo text($statusName); ?></span>
                            </td>
                            <td>
                                <?php echo text($patientLastName); ?>, <?php echo text($patientFirstName); ?>
                                <br/><small class="text-muted"><?php echo xlt("DOB"); ?>: <?php echo text(substr($birthDate, 0, 10)); ?></small>
                            </td>
                            <td>
                                <?php echo text($payerName); ?>
                                <?php if ($payerNumberData !== '') { ?>
                                    <br/><small class="text-muted">#<?php echo text($payerNumberData); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo text($providerLastName); ?>, <?php echo text($providerFirstName); ?>
                                <?php if ($providerNpiData !== '') { ?>
                                    <br/><small class="text-muted"><?php echo xlt("NPI"); ?>: <?php echo text($providerNpiData); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo text(substr($serviceDate, 0, 10)); ?>
                                <?php if ($serviceDateEndData !== '') { ?>
                                    <br/><small class="text-muted"><?php echo xlt("to"); ?> <?php echo text(substr($serviceDateEndData, 0, 10)); ?></small>
                                <?php } ?>
                            </td>
                            <td><?php echo text(substr($receivedDate, 0, 10)); ?></td>
                            <td class="text-right"><?php echo text(number_format($billedAmount, 2)); ?></td>
                            <td class="text-right"><?php echo text(number_format($payerPaidAmount, 2)); ?></td>
                            <td class="oe-status-cell" id="oe-status-<?php echo attr((string) $rowIndex); ?>">
                                <?php if ($oeStatus !== null) {
                                    $oeBadgeClass = match ($oeStatus['status']) {
                                        0 => 'badge-light text-dark',
                                        1 => 'badge-warning',
                                        2 => 'badge-primary',
                                        6 => 'badge-info',
                                        7 => 'badge-danger',
                                        -1 => 'badge-light text-muted',
                                        default => 'badge-secondary',
                                    };
    ?>
                                    <span class="badge <?php echo attr($oeBadgeClass); ?> badge-oe-status"><?php echo text($oeStatus['status_label']); ?></span>
                                <?php } else { ?>
                                    <span class="text-muted small">—</span>
                                <?php } ?>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="btn-group btn-group-sm action-btn-group">
                                    <?php if ($oeStatus !== null && $oeStatus['status'] !== -1) { ?>
                                        <a href="<?php echo attr($webRoot); ?>/interface/billing/sl_eob_search.php?form_pid=<?php echo attr((string) $oeStatus['pid']); ?>&form_encounter=<?php echo attr((string) $oeStatus['encounter']); ?>"
                                           target="_blank" class="btn btn-outline-info" title="<?php echo xla("Open Encounter in Billing"); ?>">
                                            <i class="fa fa-folder-open"></i>
                                        </a>
                                    <?php } ?>
                                    <?php if ($isRejected && $oeStatus !== null && $oeStatus['status'] !== 7) { ?>
                                        <button type="button" class="btn btn-outline-danger sync-status-btn"
                                            data-rowindex="<?php echo attr((string) $rowIndex); ?>"
                                            data-objectid="<?php echo attr($objectId); ?>"
                                            title="<?php echo xla("Sync rejected status to OpenEMR"); ?>">
                                            <i class="fa fa-sync-alt"></i>
                                        </button>
                                    <?php } ?>
                                    <?php if ($oeStatus !== null && in_array($oeStatus['status'], [2, 7])) { ?>
                                        <button type="button" class="btn btn-outline-warning requeue-btn"
                                            data-rowindex="<?php echo attr((string) $rowIndex); ?>"
                                            data-pcn="<?php echo attr($pcn); ?>"
                                            title="<?php echo xla("Requeue for billing"); ?>">
                                            <i class="fa fa-redo"></i>
                                        </button>
                                    <?php } ?>
                                    <?php if ($objectId !== '') { ?>
                                        <a href="<?php echo attr($portalUrl . $editorRoute . $objectId); ?>" target="_blank" class="btn btn-outline-primary" title="<?php echo xla("Edit in Portal"); ?>">
                                            <i class="fa fa-external-link-alt"></i>
                                        </a>
                                    <?php } ?>
                                    <button type="button" class="btn worked-toggle <?php echo $isWorked ? 'btn-success' : 'btn-outline-secondary'; ?>"
                                        data-objectid="<?php echo attr($objectId); ?>"
                                        data-worked="<?php echo $isWorked ? '1' : '0'; ?>"
                                        title="<?php echo $isWorked ? xla("Worked - click to unmark") : xla("Not worked - click to mark"); ?>"
                                        onclick="toggleWorked(this);">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="claim-detail-row" id="detail-<?php echo attr((string) $rowIndex); ?>">
                            <td colspan="10" class="claim-detail-cell p-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="detail-label"><?php echo xlt("ClaimRev Status"); ?></div>
                                        <div class="detail-value"><?php echo text($statusName); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("File Status"); ?></div>
                                        <div class="detail-value"><?php echo text($data->payerFileStatusName ?? ''); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("Payer Acceptance"); ?></div>
                                        <div class="detail-value"><?php echo text($payerAcceptance); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("ERA Status"); ?></div>
                                        <div class="detail-value"><?php echo text($paymentAdvice); ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="detail-label"><?php echo xlt("Member #"); ?></div>
                                        <div class="detail-value"><?php echo text($data->memberNumber ?? ''); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("Trace #"); ?></div>
                                        <div class="detail-value"><?php echo text($data->traceNumber ?? ''); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("Patient Control #"); ?></div>
                                        <div class="detail-value"><?php echo text($data->patientControlNumber ?? ''); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("Payer Control #"); ?></div>
                                        <div class="detail-value"><?php echo text($data->payerControlNumber ?? ''); ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="detail-label"><?php echo xlt("Received Date"); ?></div>
                                        <div class="detail-value"><?php echo text(substr($data->receivedDate ?? '', 0, 10)); ?></div>
                                        <div class="detail-label mt-2"><?php echo xlt("Claim Type"); ?></div>
                                        <div class="detail-value"><?php echo text($data->claimType ?? ''); ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="detail-label"><?php echo xlt("Worked"); ?></div>
                                        <div class="detail-value worked-detail-<?php echo attr($objectId); ?>">
                                            <?php if ($isWorked) { ?>
                                                <span class="text-success"><i class="fa fa-check-circle"></i> <?php echo xlt("Yes"); ?></span>
                                            <?php } else { ?>
                                                <span class="text-muted"><i class="fa fa-circle"></i> <?php echo xlt("No"); ?></span>
                                            <?php } ?>
                                        </div>
                                        <?php if ($oeStatus !== null && $oeStatus['status'] !== -1) { ?>
                                            <div class="detail-label mt-2"><?php echo xlt("OpenEMR Status"); ?></div>
                                            <div class="detail-value">
                                                <?php
                                                    $oeBadgeClass = match ($oeStatus['status']) {
                                                        0 => 'badge-light text-dark',
                                                        1 => 'badge-warning',
                                                        2 => 'badge-primary',
                                                        6 => 'badge-info',
                                                        7 => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
    ?>
                                                <span class="badge <?php echo attr($oeBadgeClass); ?>"><?php echo text($oeStatus['status_label']); ?></span>
                                                <small class="text-muted ml-1">(<?php echo text($oeStatus['pid'] . '-' . $oeStatus['encounter']); ?>)</small>
                                            </div>
                                        <?php } ?>
                                        <div class="mt-3">
                                            <?php if ($objectId !== '') { ?>
                                                <a href="<?php echo attr($portalUrl . $editorRoute . $objectId); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-external-link-alt"></i> <?php echo xlt("Edit in Portal"); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if ($oeStatus !== null && $oeStatus['status'] !== -1) { ?>
                                                <a href="<?php echo attr($webRoot); ?>/interface/billing/sl_eob_search.php?form_pid=<?php echo attr((string) $oeStatus['pid']); ?>&form_encounter=<?php echo attr((string) $oeStatus['encounter']); ?>"
                                                   target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fa fa-folder-open"></i> <?php echo xlt("Open Encounter"); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($errorCount > 0) { ?>
                                    <div class="mt-3 claim-errors-section" data-claimid="<?php echo attr($objectId); ?>" data-loaded="0">
                                        <div class="detail-label"><?php echo xlt("Errors"); ?> (<?php echo text((string) $errorCount); ?>)</div>
                                        <div class="claim-errors-content">
                                            <span class="text-muted small"><i class="fa fa-spinner fa-spin"></i> <?php echo xlt("Loading errors..."); ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                        $rowIndex++;
                    } ?>
                  </tbody>
                </table>
            <?php if ($totalPages > 1) { ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($pageIndex <= 0) ? 'disabled' : ''; ?>">
                            <button type="submit" name="pageIndex" value="<?php echo attr((string) ($pageIndex - 1)); ?>" form="claimSearchForm" class="page-link"><?php echo xlt("Previous"); ?></button>
                        </li>
                        <?php
                        $startPage = max(0, $pageIndex - 2);
                        $endPage = min($totalPages - 1, $pageIndex + 2);
                        for ($i = $startPage; $i <= $endPage; $i++) { ?>
                            <li class="page-item <?php echo ($i == $pageIndex) ? 'active' : ''; ?>">
                                <button type="submit" name="pageIndex" value="<?php echo attr((string) $i); ?>" form="claimSearchForm" class="page-link"><?php echo text((string) ($i + 1)); ?></button>
                            </li>
                        <?php } ?>
                        <li class="page-item <?php echo ($pageIndex >= $totalPages - 1) ? 'disabled' : ''; ?>">
                            <button type="submit" name="pageIndex" value="<?php echo attr((string) ($pageIndex + 1)); ?>" form="claimSearchForm" class="page-link"><?php echo xlt("Next"); ?></button>
                        </li>
                    </ul>
                </nav>
            <?php } ?>
        <?php }
        ?>
        </div>
        <script>
            var csrfToken = <?php echo json_encode($csrfToken); ?>;

            function toggleWorked(btn) {
                var $btn = $(btn);
                var objectId = $btn.data('objectid');
                var currentlyWorked = $btn.data('worked') === 1 || $btn.data('worked') === '1';
                var newWorked = !currentlyWorked;
                $btn.prop('disabled', true);
                $.post('claim_mark_worked.php', {
                    objectId: objectId,
                    isWorked: newWorked ? '1' : '0',
                    csrf_token: csrfToken
                }, function(response) {
                    if (response.success) {
                        $btn.data('worked', newWorked ? '1' : '0');
                        if (newWorked) {
                            $btn.removeClass('btn-outline-secondary').addClass('btn-success');
                        } else {
                            $btn.removeClass('btn-success').addClass('btn-outline-secondary');
                        }
                        $('.worked-toggle[data-objectid="' + objectId + '"]').each(function() {
                            var $b = $(this);
                            $b.data('worked', newWorked ? '1' : '0');
                            if (newWorked) {
                                $b.removeClass('btn-outline-secondary').addClass('btn-success');
                            } else {
                                $b.removeClass('btn-success').addClass('btn-outline-secondary');
                            }
                        });
                        var $detail = $('.worked-detail-' + objectId);
                        if ($detail.length) {
                            if (newWorked) {
                                $detail.html('<span class="text-success"><i class="fa fa-check-circle"></i> ' + <?php echo xlj("Yes"); ?> + '</span>');
                            } else {
                                $detail.html('<span class="text-muted"><i class="fa fa-circle"></i> ' + <?php echo xlj("No"); ?> + '</span>');
                            }
                        }
                    }
                }, 'json').always(function() {
                    $btn.prop('disabled', false);
                });
            }

            $(document).ready(function() {
                $('.claim-row').on('click', function() {
                    var target = $(this).data('target');
                    var $detail = $(target);
                    $detail.toggleClass('show');

                    if ($detail.hasClass('show')) {
                        $detail.find('.claim-errors-section').each(function() {
                            var $section = $(this);
                            if ($section.data('loaded') === 1) {
                                return;
                            }
                            $section.data('loaded', 1);
                            var claimId = $section.data('claimid');
                            $.get('claim_errors.php', { claimId: claimId }, function(response) {
                                var $content = $section.find('.claim-errors-content');
                                if (response.success && response.errors && response.errors.length > 0) {
                                    var html = '<ul class="mb-0 small">';
                                    response.errors.forEach(function(err) {
                                        html += '<li class="text-danger">';
                                        html += $('<span>').text(err.errorMessage || '').html();
                                        if (err.segment) {
                                            html += ' <span class="text-muted">(' + $('<span>').text('Segment: ' + err.segment).html();
                                            if (err.loopId) {
                                                html += ', Loop: ' + $('<span>').text(err.loopId).html();
                                            }
                                            html += ')</span>';
                                        }
                                        html += '</li>';
                                    });
                                    html += '</ul>';
                                    $content.html(html);
                                } else if (response.success) {
                                    $content.html('<span class="text-muted small">' + <?php echo xlj("No errors found"); ?> + '</span>');
                                } else {
                                    $content.html('<span class="text-danger small">' + <?php echo xlj("Failed to load errors"); ?> + '</span>');
                                }
                            }, 'json').fail(function() {
                                $section.find('.claim-errors-content').html('<span class="text-danger small">' + <?php echo xlj("Failed to load errors"); ?> + '</span>');
                                $section.data('loaded', 0);
                            });
                        });
                    }
                });

                $('.sortable-header').on('click', function() {
                    var field = $(this).data('sort');
                    var currentField = $('#sortField').val();
                    var currentDir = $('#sortDirection').val();
                    if (currentField === field) {
                        $('#sortDirection').val(currentDir === 'asc' ? 'desc' : 'asc');
                    } else {
                        $('#sortField').val(field);
                        $('#sortDirection').val('asc');
                    }
                    $('<input>').attr({ type: 'hidden', name: 'pageIndex', value: '0' }).appendTo('#claimSearchForm');
                    $('#claimSearchForm').submit();
                });

                $('#clearSearchBtn').on('click', function() {
                    localStorage.removeItem('claimrev_claim_search');
                    window.location.href = 'claims.php';
                });

                $('#exportCsvBtn').on('click', function() {
                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + <?php echo xlj("Exporting..."); ?>);
                    var formData = $('#claimSearchForm').serialize();
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'claim_export_csv.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.responseType = 'blob';
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var disposition = xhr.getResponseHeader('Content-Disposition');
                            var fileName = 'claims_export.csv';
                            if (disposition && disposition.indexOf('filename=') !== -1) {
                                fileName = disposition.split('filename=')[1].replace(/"/g, '');
                            }
                            var blob = xhr.response;
                            var link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = fileName;
                            link.click();
                        } else {
                            alert(<?php echo xlj("Failed to export CSV"); ?>);
                        }
                        $btn.prop('disabled', false).html('<i class="fa fa-download"></i> ' + <?php echo xlj("Export CSV"); ?>);
                    };
                    xhr.onerror = function() {
                        alert(<?php echo xlj("Failed to export CSV"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-download"></i> ' + <?php echo xlj("Export CSV"); ?>);
                    };
                    xhr.send(formData);
                });

                // Sync Status button handler
                $('.sync-status-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    var rowIndex = $btn.data('rowindex');
                    var claimrevObjectId = $btn.data('objectid');

                    if (!confirm(<?php echo xlj("Sync this rejected claim status to OpenEMR? This will mark the claim as denied."); ?>)) {
                        return;
                    }

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                    $.post('claim_sync_status.php', {
                        csrf_token: csrfToken,
                        claimrevObjectId: claimrevObjectId
                    }, function(response) {
                        if (response.success && response.action === 'denied') {
                            // Update OE status badge
                            var $cell = $('#oe-status-' + rowIndex);
                            $cell.html('<span class="badge badge-danger badge-oe-status">' + <?php echo xlj("Denied"); ?> + '</span>');
                            $btn.replaceWith('<span class="text-success small"><i class="fa fa-check"></i></span>');
                            alert(<?php echo xlj("Claim status synced to OpenEMR"); ?>);
                        } else {
                            alert(response.message || <?php echo xlj("No sync needed"); ?>);
                            $btn.prop('disabled', false).html('<i class="fa fa-sync-alt"></i>');
                        }
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed to sync status"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-sync-alt"></i>');
                    });
                });

                // Requeue for Billing button handler
                $('.requeue-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    var rowIndex = $btn.data('rowindex');
                    var pcn = $btn.data('pcn');

                    if (!confirm(<?php echo xlj("Requeue this claim for billing? This will reopen the encounter and create a new claim version so it appears in the next billing batch."); ?>)) {
                        return;
                    }

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                    $.post('claim_requeue.php', {
                        csrf_token: csrfToken,
                        patientControlNumber: pcn
                    }, function(response) {
                        if (response.success) {
                            // Update OE status badge
                            var $cell = $('#oe-status-' + rowIndex);
                            $cell.html('<span class="badge badge-warning badge-oe-status">' + <?php echo xlj("Unbilled"); ?> + '</span>');
                            $btn.replaceWith('<span class="text-success small"><i class="fa fa-check"></i> ' + <?php echo xlj("Requeued"); ?> + '</span>');
                            alert(<?php echo xlj("Claim requeued for billing"); ?>);
                        } else {
                            alert(response.message || <?php echo xlj("Requeue failed"); ?>);
                            $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
                        }
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed to requeue claim"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
                    });
                });
            });
        </script>
    </body>
</html>
