<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304i_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304i Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Needs a summary send to place of referral.
        //  (so basically an amc element needs to exist)
        $amcElement = amcCollect('send_sum_amc',$patient->id,'transactions',$patient->object['id']);
        if (!(empty($amcElement))) {
          return true;
        }
        else {
          return false;
        }
    }
}
