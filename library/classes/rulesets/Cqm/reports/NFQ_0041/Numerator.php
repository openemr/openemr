<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0041_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "NFQ 0041 Numerator";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate ) 
    {
        // TODO check logic
        $encDates = Helper::fetchEncounterDates( Encounter::ENC_INFLUENZA, $patient );
        foreach ( $encDates as $encDate ) {
            if ( Helper::checkMed( Medication::INFLUENZA_VAC, $patient, $encDate, $encDate ) ) {
                return true;
            }
        }
        
        return false;
    }
}
