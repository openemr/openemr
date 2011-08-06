<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0038_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle()
    {
        return "Initial Patient Population";
    }

    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        // Rs_Patient characteristic: birth dateÓ (age) >=1 year and <2 years to capture all Rs_Patients who will reach 2 years during the Òmeasurement periodÓ;
        $age = $patient->calculateAgeOnDate( $beginDate );
        if ( $age >= 1 &&
            $age < 2 ) { 
            return true;        
        }
        
        return false;
    }
}
