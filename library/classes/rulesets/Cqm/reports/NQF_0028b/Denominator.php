<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0028b_Denominator implements CqmFilterIF
{
    public function getTitle()
    {
        return "NQF 0028b Denominator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        // See if user has been a tobacco user before or simultaneosly to the encounter within two years (24 months)
        $date_array = array();
        foreach ($this->getApplicableEncounters() as $encType) {
            $dates = Helper::fetchEncounterDates($encType, $patient, $beginDate, $endDate);
            $date_array = array_merge($date_array, $dates);
        }

        // sort array to get the most recent encounter first
        $date_array = array_unique($date_array);
        rsort($date_array);

        // go through each unique date from most recent
        foreach ($date_array as $date) {
            // encounters time stamp is always 00:00:00, so change it to 23:59:59 or 00:00:00 as applicable
            $date = date('Y-m-d 23:59:59', strtotime($date));
            $beginMinus24Months = strtotime('-24 month', strtotime($date));
            $beginMinus24Months = date('Y-m-d 00:00:00', $beginMinus24Months);
            // this is basically a check to see if the patient is an reported as an active smoker on their last encounter
            if (Helper::check(ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_USER, $patient, $beginMinus24Months, $date)) {
                return true;
            } elseif (Helper::check(ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_NON_USER, $patient, $beginMinus24Months, $date)) {
                return false;
            } else {
                // nothing reported during this date period, so move on to next encounter
            }
        }

        return false;
    }

    private function getApplicableEncounters()
    {
        return array(
            Encounter::ENC_OFF_VIS,
            Encounter::ENC_HEA_AND_BEH,
            Encounter::ENC_OCC_THER,
            Encounter::ENC_PSYCH_AND_PSYCH,
            Encounter::ENC_PRE_MED_SER_18_OLDER,
            Encounter::ENC_PRE_IND_COUNSEL,
            Encounter::ENC_PRE_MED_GROUP_COUNSEL,
            Encounter::ENC_PRE_MED_OTHER_SERV );
    }
}
