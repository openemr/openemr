<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0043_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate ) 
    {
        if ( Helper::checkMed( Medication::PNEUMOCOCCAL_VAC, $patient, $patient->dob, $endDate ) ) {
            return true;
        }
        
        return false;
    }
}
