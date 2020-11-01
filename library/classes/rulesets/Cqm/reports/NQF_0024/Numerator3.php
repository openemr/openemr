<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0024_Numerator3 implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator 3";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        //if ( Helper::check( ClinicalType::COMMUNICATION, Communication::COUNS_PHYS_ACTIVITY, $patient, $beginDate, $endDate ) ) {
        //    return true;
        //}

        $exercise = sqlQuery("SELECT * FROM `rule_patient_data` " .
                             "WHERE `category`='act_cat_edu' AND `item`='act_exercise' AND `complete`='YES' " .
                             "AND `pid`=? AND `date`>=? AND `date`<=?", array($patient->id,$beginDate,$endDate));
        if (!empty($exercise)) {
            return true;
        }

        return false;
    }
}
