<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class LabResult extends ClinicalType
{   
    const OPTION_RANGE = 'range';
    
    const HB1AC_TEST = 'lab_hb1ac_test';
    const LDL_TEST = 'lab_ldl_test';
    
    public function getListId() 
    {
        return 'Clinical_Rules_Lab_Res_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null )
    {
        // TODO where to check for lab result ???
        if ( isset( $options[self::OPTION_RANGE] ) ) {
            $range = $options[self::OPTION_RANGE];
            if ( is_a( $range, 'Range' ) ) {
                // search through vitals to find the most recent lab result in the date range
                // if the result value is within range using Range->test(val), return true
            }
        } else {
            // range is not set, check for any lab result of this type within the date range
        }
        
        return false;
    }
}
