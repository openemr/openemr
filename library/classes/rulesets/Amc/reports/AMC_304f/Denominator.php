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

class AMC_304f_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304f Denominator";
    }

    public function test( AmcPatient $patient, $beginDate, $endDate )
    {
        // All unique patients seen by the EP or admitted to the eligible
        // hospital’s or CAH’s inpatient or emergency department (POS 21 or 23)
        // Also need to have requested their records.
        //  (basically needs an encounter within the report dates and to have requested their
        //   records within the report dates)
        $amccheck =  sqlQuery("SELECT * FROM `amc_misc_data` WHERE `amc_id`=? AND `pid`=? AND `date_created`>=? AND `date_created`<=?", array('provide_rec_pat_amc',$patient->id,$beginDate,$endDate) );
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if ( (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) &&
             !(empty($amccheck)) ) {
            return true;
        }
        else {
            return false;
        }
    }
    
}
