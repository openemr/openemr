<?php

/**
 *
 * CQM NQF 0028(2014) Numerator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

class NQF_0028_2014_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        // See if user has been counseled to stop smoking or been prescribed a smoking cessations medication within last 24 months
        foreach ($this->getApplicableEncounters() as $encType) {
            $dates = Helper::fetchEncounterDates($encType, $patient, $beginDate, $endDate);
            foreach ($dates as $date) {
                // encounters time stamp is always 00:00:00, so change it to 23:59:59 or 00:00:00 as applicable
                $date = date('Y-m-d 23:59:59', strtotime($date));
                $date = date("Y-m-d H:i:s", strtotime('+1 days', strtotime($date)));
                $beginMinus24Months = strtotime('-24 month', strtotime($date));
                $beginMinus24Months = date('Y-m-d 00:00:00', $beginMinus24Months);
                // this is basically a check to see if the patient is an reported as an active smoker on their last encounter
                if (Helper::check(ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_USER, $patient, $beginMinus24Months, $date)) {
                    //return true;
                    // encounters time stamp is always 00:00:00, so change it to 23:59:59 or 00:00:00 as applicable
                    $date = date("Y-m-d H:i:s", strtotime('+1 days', strtotime($date)));
                    $date = date('Y-m-d 23:59:59', strtotime($date));
                    $beginMinus24Months = strtotime('-24 month', strtotime($date));
                    $beginMinus24Months = date('Y-m-d 00:00:00', $beginMinus24Months);
                    $smoke_cess = sqlQuery("SELECT * FROM `rule_patient_data` " .
                                           "WHERE `category`='act_cat_inter' AND `item`='act_tobacco' AND `complete`='YES' " .
                                           "AND `pid`=? AND `date`>=? AND `date`<=?", array($patient->id,$beginMinus24Months,$date));
                    // this is basically a check to see if the patient's action has occurred in the two years previous to encounter.
                    // TODO: how to check for the smoking cessation medication types (can also just be a smoking cessation order, ie. prescription)
                    if (
                        !(empty($smoke_cess)) ||
                         Helper::checkMed(Medication::SMOKING_CESSATION, $patient, $beginMinus24Months, $date) ||
                         Helper::checkMed(Medication::SMOKING_CESSATION_ORDER, $patient, $beginMinus24Months, $date)
                    ) {
                        return true;
                    }
                } elseif (Helper::check(ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_NON_USER, $patient, $beginMinus24Months, $date)) {
                    return false;
                } else {
                    // nothing reported during this date period, so move on to next encounter
                }
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
