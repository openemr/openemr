<?php
    use OpenEMR\Modules\ClaimRevConnector\PrintProperty;


    if (property_exists($eligibilityData, 'medicarePartADate'))
    {
        if (property_exists($eligibilityData->medicarePartADate, 'startDate'))
        {
            PrintProperty::DisplayDateProperty("Medicare Part A Start Date:",$eligibilityData->medicarePartADate->startDate);
        }
        if (property_exists($eligibilityData->medicarePartADate, 'endDate'))
        {
            PrintProperty::DisplayDateProperty("Medicare Part A End Date:",$eligibilityData->medicarePartADate->endDate);
        }
    }
    if (property_exists($eligibilityData, 'medicarePartBDate'))
    {
        if (property_exists($eligibilityData->medicarePartBDate, 'startDate'))
        {
            PrintProperty::DisplayDateProperty("Medicare Part B Start Date:",$eligibilityData->medicarePartBDate->startDate);
        }
        if (property_exists($eligibilityData->medicarePartBDate, 'endDate'))
        {
            PrintProperty::DisplayDateProperty("Medicare Part B End Date:",$eligibilityData->medicarePartBDate->endDate);
        }
    }

    if (property_exists($eligibilityData, 'isMedicarePartAOnly'))
    {
        PrintProperty::DisplayProperty("Is Medicare Part A Only",$eligibilityData->isMedicarePartAOnly);
    }
    if (property_exists($eligibilityData, 'isMedicareReplacementPlan'))
    {
        PrintProperty::DisplayProperty("Is Medicare Replacement Plan",$eligibilityData->isMedicareReplacementPlan);
    }
    if (property_exists($eligibilityData, 'medicareReplacementPayer'))
    {
        PrintProperty::DisplayProperty("Medicare Replacement Payer",$eligibilityData->medicareReplacementPayer);
    }
    if (property_exists($eligibilityData, 'medicareSupplementalPlanName'))
    {
        PrintProperty::DisplayProperty("Medicare Supplemental Plan Name",$eligibilityData->medicareSupplementalPlanName);
    }
    if (property_exists($eligibilityData, 'qualifiedMedicareBeneficiary'))
    {
        PrintProperty::DisplayProperty("Qualified Medicare Beneficiary",$eligibilityData->qualifiedMedicareBeneficiary);
    }
    if (property_exists($eligibilityData, 'qualifiedMedicareBeneficiary'))
    {
        PrintProperty::DisplayProperty("Railroad Medicare Beneficiary",$eligibilityData->railroadMedicareBeneficiary);
    }
?>