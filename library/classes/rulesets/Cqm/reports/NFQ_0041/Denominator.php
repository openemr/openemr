<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0041_Denominator implements CqmFilterIF
{
    public function getTitle()
    {
        return "NFQ 0041 Denominator";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        $periodPlus58Days = date( 'Y-m-d 00:00:00', strtotime( '+58 day', strtotime( $beginDate ) ) );
        $periodMinus122Days = date( 'Y-m-d 00:00:00', strtotime( '-122 day', strtotime( $endDate ) ) );
        if ( Helper::checkEncounter( Encounter::ENC_INFLUENZA, $patient, $beginDate, $periodPlus58Days ) || 
            Helper::checkEncounter( Encounter::ENC_INFLUENZA, $patient, $periodMinus122Days, $endDate ) ) {
            return true;
        }
        
        return false;
    }
}
