<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\GlobalConfig;
use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

/** @var iterable<\stdClass> $benefits */

$benefitPatResponse = ["B","C","G","J","Y"];

// Optional EB01 filter: comma-separated benefit information codes from
// the global (oe_claimrev_benefit_code_filter). When non-empty, only
// benefits whose benefitInformation matches are rendered. Filtering is
// purely local; the upstream 271 still carries everything.
$benefitFilterRaw = OEGlobalsBag::getInstance()->getString(GlobalConfig::CONFIG_BENEFIT_CODE_FILTER);
$benefitFilter = $benefitFilterRaw !== ''
    ? array_values(array_filter(array_map(trim(...), explode(',', $benefitFilterRaw)), static fn(string $s): bool => $s !== ''))
    : [];

foreach ($benefits as $benefit) {
    $benefitInfoDesc = property_exists($benefit, 'benefitInformationDesc') && is_string($benefit->benefitInformationDesc) ? $benefit->benefitInformationDesc : '';
    $benefitInfo = property_exists($benefit, 'benefitInformation') && is_string($benefit->benefitInformation) ? $benefit->benefitInformation : '';

    if ($benefitFilter !== [] && !in_array($benefitInfo, $benefitFilter, true)) {
        continue;
    }
    $serviceTypes = property_exists($benefit, 'serviceTypes') && is_iterable($benefit->serviceTypes) ? $benefit->serviceTypes : [];
    $quantityQualifierDesc = property_exists($benefit, 'quantityQualifierDesc') && is_string($benefit->quantityQualifierDesc) ? $benefit->quantityQualifierDesc : '';
    ?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"> <?php echo xlt("Benefit"); ?> - <?php echo text($benefitInfoDesc); ?> </h5>

        <div class="row">
            <div class="col">
                <strong> <?php echo xlt("Service Type");?></strong>
            </div>
            <div class="col">
                <ul>
                    <?php
                    foreach ($serviceTypes as $st) {
                        if (!is_object($st)) {
                            continue;
                        }
                        $stDesc = property_exists($st, 'serviceTypeDesc') && is_string($st->serviceTypeDesc) ? $st->serviceTypeDesc : '';
                        ?>
                        <li><?php echo text($stDesc) ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>

        </div>
    <?php
            PrintProperty::displayProperty("Coverage Level", $benefit->coverageLevel ?? '');
            PrintProperty::displayProperty("Insurance Type", $benefit->insuranceTypeCodeDesc ?? '');
            PrintProperty::displayProperty("Coverage Description", $benefit->planCoverageDescription ?? '');
            PrintProperty::displayProperty("Time Period", $benefit->timePeriodQualifierDesc ?? '');
    if (in_array($benefitInfo, $benefitPatResponse, true)) {
        PrintProperty::displayProperty("Patient Responsibility", $benefit->benefitAmount ?? '', "$");
    } else {
        PrintProperty::displayProperty("Amount", $benefit->benefitAmount ?? '', "$");
    }
    if ($benefitInfo === 'A') {
        PrintProperty::displayProperty("Patient Responsibility", $benefit->benefitPercent ?? '', "", "%");
    } else {
        PrintProperty::displayProperty("Benefit Percent", $benefit->benefitPercent ?? '', "", "%");
    }
            PrintProperty::displayProperty("Benefit Quantity", $benefit->benefitQuantity ?? '', "", " - " . $quantityQualifierDesc);
            PrintProperty::displayProperty("Authorization/Certification Indicator", $benefit->certificationIndicator ?? '');
            PrintProperty::displayProperty("In Plan Network", $benefit->inPlanNetworkIndicator ?? '');
            include 'service_delivery.php';
            include 'procedure_info.php';
            include 'date_information.php';
            include 'identifier_info.php';
            include 'additional_info.php';
            include 'related_entity.php';
            include 'messages.php';
    ?>
    </div>
</div>
    <?php
}
