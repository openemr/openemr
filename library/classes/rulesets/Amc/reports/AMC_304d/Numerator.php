<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304d_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304d Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Were sent an appropriate reminder during the EHR reporting period
        $result_query = sqlQuery("SELECT * FROM `patient_reminders` WHERE `pid`=? AND `date_sent`>=? AND `date_sent`<=?", array($patient->id,$beginDate,$endDate) );
        if ( !(empty($result_query)) ) {
            return true;
        }
        else {
            return false;
        }
    }
}
