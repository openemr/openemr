<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302e_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302e Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Have at least one entry or an indication that no problems are known for the 
        // patient recorded as structured data.
        //  (true if an entry in lists_touch or an active entry in lists preceding the date-end)
        $firstCheck = sqlQuery("SELECT * FROM `lists_touch` WHERE `pid`=? AND `type`=? AND `date`<=?", array($patient->id,'allergy',$endDate) );
        $secondCheck = sqlQuery("SELECT * FROM `lists` WHERE `activity`='1' AND `pid`=? AND `type`=? AND `date`<=?", array($patient->id,'allergy',$endDate) );
        if ( !(empty($firstCheck)) || !(empty($secondCheck)) ) {
          return true;
        }
        else {
          return false;
        }
    }
}
