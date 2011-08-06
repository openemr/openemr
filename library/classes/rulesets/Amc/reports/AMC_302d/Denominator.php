<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains a function to keep track of which issues
// types get modified.
//

class AMC_302d_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302d Denominator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Seen by the EP or admitted to the eligible hospitals or CAHs inpatient or emergency department (POS 21 or 23)
        //  (basically needs an encounter within the report dates)
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) {
            return true;
        }
        else {
            return false;
        }
    }
}
