<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304a_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304a Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Simply need a prescription within the report dates.
        $check = sqlQuery("SELECT * FROM `prescriptions` WHERE `patient_id`=? AND `date_added`>=? AND `date_added`<=?", array($patient->id,$beginDate,$endDate) );        
        if (!(empty($check)))
        {
            return true;
        }
        else {
            return false;
        }
    }
}
