<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

if (property_exists($eligibilityData, 'medicarePartADate')) {
    if (property_exists($eligibilityData->medicarePartADate, 'startDate')) {
        PrintProperty::displayDateProperty("Medicare Part A Start Date:", $eligibilityData->medicarePartADate->startDate);
    }
    if (property_exists($eligibilityData->medicarePartADate, 'endDate')) {
        PrintProperty::displayDateProperty("Medicare Part A End Date:", $eligibilityData->medicarePartADate->endDate);
    }
}
if (property_exists($eligibilityData, 'medicarePartBDate')) {
    if (property_exists($eligibilityData->medicarePartBDate, 'startDate')) {
        PrintProperty::displayDateProperty("Medicare Part B Start Date:", $eligibilityData->medicarePartBDate->startDate);
    }
    if (property_exists($eligibilityData->medicarePartBDate, 'endDate')) {
        PrintProperty::displayDateProperty("Medicare Part B End Date:", $eligibilityData->medicarePartBDate->endDate);
    }
}

if (property_exists($eligibilityData, 'isMedicarePartAOnly')) {
    PrintProperty::displayProperty("Is Medicare Part A Only", $eligibilityData->isMedicarePartAOnly);
}
if (property_exists($eligibilityData, 'isMedicareReplacementPlan')) {
    PrintProperty::displayProperty("Is Medicare Replacement Plan", $eligibilityData->isMedicareReplacementPlan);
}
if (property_exists($eligibilityData, 'medicareReplacementPayer')) {
    PrintProperty::displayProperty("Medicare Replacement Payer", $eligibilityData->medicareReplacementPayer);
}
if (property_exists($eligibilityData, 'medicareSupplementalPlanName')) {
    PrintProperty::displayProperty("Medicare Supplemental Plan Name", $eligibilityData->medicareSupplementalPlanName);
}
if (property_exists($eligibilityData, 'qualifiedMedicareBeneficiary')) {
    PrintProperty::displayProperty("Qualified Medicare Beneficiary", $eligibilityData->qualifiedMedicareBeneficiary);
}
if (property_exists($eligibilityData, 'qualifiedMedicareBeneficiary')) {
    PrintProperty::displayProperty("Railroad Medicare Beneficiary", $eligibilityData->railroadMedicareBeneficiary);
}
