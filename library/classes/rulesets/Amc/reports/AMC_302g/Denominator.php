<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
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

class AMC_302g_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302g Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // All unique patients 13 years or older seen by the EP or admitted to the eligible
        // hospital’s or CAH’s inpatient or emergency department (POS 21 or 23)
        //  (basically needs an encounter within the report dates and needs to be 13 by the end report date)
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if (
            (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) &&
             ($patient->calculateAgeOnDate($endDate) >= 13)
        ) {
            return true;
        } else {
            return false;
        }
    }
}
