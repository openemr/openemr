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

class AMC_304i_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304i Denominator";
    }

    public function test( AmcPatient $patient, $beginDate, $endDate )
    {
        //  (basically needs a referral within the report dates,
        //   which are already filtered for, so all the objects are a positive)
        return true;
    }
    
}
