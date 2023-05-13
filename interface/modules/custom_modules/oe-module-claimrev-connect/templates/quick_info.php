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

if (property_exists($eligibilityData, 'payerInfo')) {
    if (property_exists($eligibilityData->payerInfo, 'payerName')) {
        PrintProperty::displayProperty("Payer Name:", $eligibilityData->payerInfo->payerName);
    }
}
if (property_exists($eligibilityData, 'status')) {
    if ($eligibilityData->status == "Active Coverage") {
        PrintProperty::displayProperty("Coverage Status:", $eligibilityData->status, "", "", "color:green");
    } else {
        PrintProperty::displayProperty("Coverage Status:", $eligibilityData->status, "", "", "color:red");
    }
}
if (property_exists($eligibilityData, 'hierarchy')) {
    PrintProperty::displayProperty("Hierarchy:", $eligibilityData->hierarchy);
}
if (property_exists($eligibilityData, 'confidenceScoreReason')) {
    PrintProperty::displayProperty("Confidence Score Reason:", $eligibilityData->confidenceScoreReason);
}
if (property_exists($eligibilityData, 'confidenceScore')) {
    PrintProperty::displayProperty("Confidence Score:", $eligibilityData->confidenceScore);
}
if (property_exists($eligibilityData, 'insuranceType')) {
    PrintProperty::displayProperty("Insurance Type:", $eligibilityData->insuranceType);
}

if (property_exists($eligibilityData, 'subscriberId')) {
    PrintProperty::displayProperty("Subscriber ID:", $eligibilityData->subscriberId);
}
if (property_exists($eligibilityData, 'mbi')) {
    PrintProperty::displayProperty("MBI:", $eligibilityData->mbi);
}

if (property_exists($eligibilityData, 'dateOfDeath')) {
    PrintProperty::displayDateProperty("Date of Death:", $eligibilityData->dateOfDeath);
}
if (property_exists($eligibilityData, 'groupNumber')) {
    PrintProperty::displayProperty("Group Number:", $eligibilityData->groupNumber);
}
if (property_exists($eligibilityData, 'groupName')) {
    PrintProperty::displayProperty("Group Name:", $eligibilityData->groupName);
}
if (property_exists($eligibilityData, 'planSponsor')) {
    PrintProperty::displayProperty("Plan Sponsor:", $eligibilityData->planSponsor);
}
if (property_exists($eligibilityData, 'planCode')) {
    PrintProperty::displayProperty("Plan Code:", $eligibilityData->planCode);
}
if (property_exists($eligibilityData, 'insurancePlan')) {
    PrintProperty::displayProperty("Insurance Plan Name:", $eligibilityData->insurancePlan);
}
if (property_exists($eligibilityData, 'policyDate')) {
    if (property_exists($eligibilityData->policyDate, 'startDate')) {
        PrintProperty::displayDateProperty("Policy Start Date:", $eligibilityData->policyDate->startDate);
    }
    if (property_exists($eligibilityData->policyDate, 'endDate')) {
        PrintProperty::displayDateProperty("Policy End Date:", $eligibilityData->policyDate->endDate);
    }
}
if (property_exists($eligibilityData->policyDate, 'addedDate')) {
    PrintProperty::displayDateProperty("Date Added:", $eligibilityData->addedDate);
}

if (property_exists($eligibilityData, 'cobDate')) {
    if (property_exists($eligibilityData->cobDate, 'startDate')) {
        PrintProperty::displayDateProperty("COB Start Date:", $eligibilityData->cobDate->startDate);
    }
    if (property_exists($eligibilityData->cobDate, 'endDate')) {
        PrintProperty::displayDateProperty("COB End Date:", $eligibilityData->cobDate->endDate);
    }
}

if (property_exists($eligibilityData, 'managedCarePlan')) {
    PrintProperty::displayProperty("Managed Care:", $eligibilityData->managedCarePlan);
}
if (property_exists($eligibilityData, 'managedCareProgram')) {
    PrintProperty::displayProperty("Managed Care Program:", $eligibilityData->managedCareProgram);
}
if (property_exists($eligibilityData, 'managedCareSubscriberId')) {
    PrintProperty::displayProperty("Managed Care Subscriber Id:", $eligibilityData->managedCareSubscriberId);
}
if (property_exists($eligibilityData, 'providerName')) {
    PrintProperty::displayProperty("Provider Name:", $eligibilityData->providerName);
}
if (property_exists($eligibilityData, 'healthInsuranceClaimNumber')) {
    PrintProperty::displayProperty("Health Insurance Claim Number:", $eligibilityData->healthInsuranceClaimNumber);
}
if (property_exists($eligibilityData, 'electronicVerificationCode')) {
    PrintProperty::displayProperty("Electronic Verification Code:", $eligibilityData->electronicVerificationCode);
}
if (property_exists($eligibilityData, 'medicaidCoverageDetails')) {
    PrintProperty::displayProperty("Medicaid Coverage Details:", $eligibilityData->medicaidCoverageDetails);
}
if (property_exists($eligibilityData, 'medicaidRecipientId')) {
    PrintProperty::displayProperty("Medicaid Recipient Id:", $eligibilityData->medicaidRecipientId);
}
if (property_exists($eligibilityData, 'tpaName')) {
    PrintProperty::displayProperty("TPA Name:", $eligibilityData->tpaName);
}
if (property_exists($eligibilityData, 'tpaSubscriberId')) {
    PrintProperty::displayProperty("TPA Subscriber Id:", $eligibilityData->tpaSubscriberId);
}
if (property_exists($eligibilityData, 'ipaIdentifier')) {
    PrintProperty::displayProperty("IPA Identifier:", $eligibilityData->ipaIdentifier);
}
if (property_exists($eligibilityData, 'ipaDescription:')) {
    PrintProperty::displayProperty("IPA Description:", $eligibilityData->ipaDescription);
}
if (property_exists($eligibilityData, 'planNetworkIdentificationNumber:')) {
    PrintProperty::displayProperty("Plan Network Identification Number:", $eligibilityData->planNetworkIdentificationNumber);
}
