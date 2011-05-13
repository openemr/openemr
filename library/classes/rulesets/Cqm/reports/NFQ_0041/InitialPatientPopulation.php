<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0041_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle() 
    {
        return "NFQ 0041 Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate ) 
    {
        $twoCount = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );
        if ( $patient->calculateAgeOnDate( $beginDate ) >= 50 &&
            ( Helper::checkEncounter( Encounter::ENC_PRE_MED_SER_40_OLDER, $patient, $beginDate, $endDate, $twoCount ) ||
              Helper::checkEncounter( Encounter::ENC_PRE_MED_GROUP_COUNSEL, $patient, $beginDate, $endDate ) ||
              Helper::checkEncounter( Encounter::ENC_PRE_IND_COUNSEL, $patient, $beginDate, $endDate ) ||
              Helper::checkEncounter( Encounter::ENC_PRE_MED_OTHER_SERV, $patient, $beginDate, $endDate ) ||
              Helper::checkEncounter( Encounter::ENC_NURS_FAC, $patient, $beginDate, $endDate ) ||
              Helper::checkEncounter( Encounter::ENC_NURS_DISCHARGE, $patient, $beginDate, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
}
