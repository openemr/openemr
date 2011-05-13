<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//

class AMC_302f_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f Denominator";
    }
    
    public function test( AmcPatient $patient, $dateBegin, $dateEnd ) 
    {
        // All unique patients seen by the EP or admitted to the eligible
        // hospital’s or CAH’s inpatient or emergency department (POS 21 or 23)
        //  (basically needs an encounter before the end date)
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if (Helper::checkAnyEncounter($patient, $dateBegin, $dateEnd, $options)) {
            return true;
        }
        else {
            return false;
        }
    }
}
