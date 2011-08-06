<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0013_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        $twoEncounters = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );
        if ( $patient->calculateAgeOnDate( $beginDate ) >= 18 &&
            Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::HYPERTENSION, $patient, $beginDate, $endDate ) &&
            ( Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OUTPATIENT, $patient, $beginDate, $endDate, $twoEncounters ) ||
              Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_NURS_FAC, $patient, $beginDate, $endDate, $twoEncounters ) ) ) {
            return true;
        } 
        
        return false;
    }
}
