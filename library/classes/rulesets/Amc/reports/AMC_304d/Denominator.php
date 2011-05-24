<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//

class AMC_304d_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304d Denominator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // All unique patients with age greater than or equal to 65
        //   or less than or equal to 5 at the end report date.
        if ( ($patient->calculateAgeOnDate($endDate) >= 65) || 
             ($patient->calculateAgeOnDate($endDate) <= 5) ) {
            return true;
        }
        else {
            return false;
        }
    }
}
