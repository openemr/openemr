<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302j_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302j Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Need a medication reconciliation completed.
        //  (so basically the completed element of the object can't be empty
        if ( !(empty($patient->object['completed'])) ) {
          return true;
        }
        else {
          return false;
        }
    }
}
