<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304f_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304f Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // For all record request need to be completed within three business days
        $amcResults = sqlStatement("SELECT * FROM `amc_misc_data` WHERE `amc_id`=? AND `pid`=? AND `date_created`>=? AND `date_created`<=?", array('provide_rec_pat_amc',$patient->id,$beginDate,$endDate) );

        while ($res = sqlFetchArray($amcResults)) {
        
          if (empty($res['date_completed'])) {
            // Records requested but not given
            return false;
          }

          $businessDaysDifference = businessDaysDifference(date("Y-m-d",strtotime($res['date_created'])),date("Y-m-d",strtotime($res['date_completed'])));
          if ($businessDaysDifference > 3) {
            // Records not given within 3 business days of request
            return false;
          }

        }

        // All requested records for patient were given within 3 business days
        return true;

    }
}
