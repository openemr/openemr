<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once('ClinicalType.php');

class Communication extends ClinicalType
{
    const DIET_CNSLT = 'comm_diet_cnslt'; // communication provider to provider: dietary consultation order
    const COUNS_NUTRITION = 'comm_couns_nutrition'; // Communication to patient: counseling for nutrition
    const COUNS_PHYS_ACTIVITY = 'comm_couns_phys_activity'; // Communication to patient: counseling for physical activity
    const PREV_RECEIPT_VACCINE = 'comm_previous_receipt_vaccine';
    public function getListId()
    {
        return 'Clinical_Rules_Comm_Types';
    }

    public function doPatientCheck(RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        // TODO Read from referrals to check for ditary consult?
        // TODO How to check for patient communication?
        // for now, check for any encounter
        $encounters = getEncounters($patient->id, $beginDate, $endDate);
        ( empty($encounters) ) ? $totalNumberAppt = 0 : $totalNumberAppt = count($encounters);
        if ($totalNumberAppt < 1) {
            return false;
        } else {
            return true;
        }
    }
}
