<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class Communication extends ClinicalType
{   
    const DIET_CNSLT = 'comm_diet_cnslt'; // communication provider to provider: dietary consultation order
    const COUNS_NUTRITION = 'comm_couns_nutrition'; // Communication to patient: counseling for nutrition
    const COUNS_PHYS_ACTIVITY = 'comm_couns_phys_activity'; // Communication to patient: counseling for physical activity
    
    public function getListId() {
        return 'Clinical_Rules_Comm_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) 
    {
        // TODO read from referrals to check for ditary consult
        return true;
    }
    
}
