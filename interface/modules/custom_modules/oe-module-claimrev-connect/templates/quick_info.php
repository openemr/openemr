<?php
use OpenEMR\Modules\ClaimRevConnector\PrintProperty;
    if (property_exists($eligibilityData, 'status'))
    {
        if($eligibilityData->status == "Active Coverage")
        {
            PrintProperty::DisplayProperty("Coverage Status:",$eligibilityData->status,"color:green");
        }
        else
        {
            PrintProperty::DisplayProperty("Coverage Status:",$eligibilityData->status,"color:red");
        }
       
    }
    if (property_exists($eligibilityData, 'hierarchy'))
    {
        PrintProperty::DisplayProperty("Hierarchy:",$eligibilityData->hierarchy);
    }
    if (property_exists($eligibilityData, 'confidenceScoreReason'))
    {
        PrintProperty::DisplayProperty("Confidence Score Reason:",$eligibilityData->confidenceScoreReason);
    }
    if (property_exists($eligibilityData, 'confidenceScore'))
    {
        PrintProperty::DisplayProperty("Confidence Score:",$eligibilityData->confidenceScore);
    }
    if (property_exists($eligibilityData, 'insuranceType'))
    {
        PrintProperty::DisplayProperty("Insurance Type:",$eligibilityData->insuranceType);
    }

    if (property_exists($eligibilityData, 'subscriberId'))
    {
        PrintProperty::DisplayProperty("Subscriber ID:",$eligibilityData->subscriberId);
    }
    if (property_exists($eligibilityData, 'mbi'))
    {
        PrintProperty::DisplayProperty("MBI:",$eligibilityData->mbi);
    }

    if (property_exists($eligibilityData, 'dateOfDeath'))
    {
        PrintProperty::DisplayDateProperty("Date of Death:",$eligibilityData->dateOfDeath);
    }
    if (property_exists($eligibilityData, 'groupNumber'))
    {
        PrintProperty::DisplayProperty("Group Number:",$eligibilityData->groupNumber);
    }
    if (property_exists($eligibilityData, 'groupName'))
    {
        PrintProperty::DisplayProperty("Group Name:",$eligibilityData->groupName);
    }
    if (property_exists($eligibilityData, 'planSponsor'))
    {
        PrintProperty::DisplayProperty("Plan Sponsor:",$eligibilityData->planSponsor);
    }
    if (property_exists($eligibilityData, 'planCode'))
    {
        PrintProperty::DisplayProperty("Plan Code:",$eligibilityData->planCode);
    }
    if (property_exists($eligibilityData, 'insurancePlan'))
    {
        PrintProperty::DisplayProperty("Insurance Plan Name:",$eligibilityData->insurancePlan);
    }
    if (property_exists($eligibilityData, 'policyDate'))
    {
        if (property_exists($eligibilityData->policyDate, 'startDate'))
        {
            PrintProperty::DisplayDateProperty("Policy Start Date:",$eligibilityData->policyDate->startDate);
        }
        if (property_exists($eligibilityData->policyDate, 'endDate'))
        {
            PrintProperty::DisplayDateProperty("Policy End Date:",$eligibilityData->policyDate->endDate);
        }
    }
    if (property_exists($eligibilityData->policyDate, 'addedDate'))
    {
        PrintProperty::DisplayDateProperty("Date Added:",$eligibilityData->addedDate);
    }

    if (property_exists($eligibilityData, 'cobDate'))
    {
        if (property_exists($eligibilityData->cobDate, 'startDate'))
        {
            PrintProperty::DisplayDateProperty("COB Start Date:",$eligibilityData->cobDate->startDate);
        }
        if (property_exists($eligibilityData->cobDate, 'endDate'))
        {
            PrintProperty::DisplayDateProperty("COB End Date:",$eligibilityData->cobDate->endDate);
        }
    }

    if (property_exists($eligibilityData, 'managedCarePlan'))
    {
        PrintProperty::DisplayProperty("Managed Care:",$eligibilityData->managedCarePlan);
    }
    if (property_exists($eligibilityData, 'managedCareProgram'))
    {
        PrintProperty::DisplayProperty("Managed Care Program:",$eligibilityData->managedCareProgram);
    }
    if (property_exists($eligibilityData, 'managedCareSubscriberId'))
    {
        PrintProperty::DisplayProperty("Managed Care Subscriber Id:",$eligibilityData->managedCareSubscriberId);
    }
    if (property_exists($eligibilityData, 'providerName'))
    {
        PrintProperty::DisplayProperty("Provider Name:",$eligibilityData->providerName);
    }
    if (property_exists($eligibilityData, 'healthInsuranceClaimNumber'))
    {
        PrintProperty::DisplayProperty("Health Insurance Claim Number:",$eligibilityData->healthInsuranceClaimNumber);
    }
    if (property_exists($eligibilityData, 'electronicVerificationCode'))
    {
        PrintProperty::DisplayProperty("Electronic Verification Code:",$eligibilityData->electronicVerificationCode);
    }
    if (property_exists($eligibilityData, 'medicaidCoverageDetails'))
    {
        PrintProperty::DisplayProperty("Medicaid Coverage Details:",$eligibilityData->medicaidCoverageDetails);
    }
    if (property_exists($eligibilityData, 'medicaidRecipientId'))
    {
        PrintProperty::DisplayProperty("Medicaid Recipient Id:",$eligibilityData->medicaidRecipientId);
    }
    if (property_exists($eligibilityData, 'tpaName'))
    {
        PrintProperty::DisplayProperty("TPA Name:",$eligibilityData->tpaName);
    }
    if (property_exists($eligibilityData, 'tpaSubscriberId'))
    {
        PrintProperty::DisplayProperty("TPA Subscriber Id:",$eligibilityData->tpaSubscriberId);
    }
    if (property_exists($eligibilityData, 'ipaIdentifier'))
    {
        PrintProperty::DisplayProperty("IPA Identifier:",$eligibilityData->ipaIdentifier);
    }
    if (property_exists($eligibilityData, 'ipaDescription:'))
    {
        PrintProperty::DisplayProperty("IPA Description:",$eligibilityData->ipaDescription);
    }
    if (property_exists($eligibilityData, 'planNetworkIdentificationNumber:'))
    {
        PrintProperty::DisplayProperty("Plan Network Identification Number:",$eligibilityData->planNetworkIdentificationNumber);
    }
?>





