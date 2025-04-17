<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0421_Numerator2 implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator 2";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        // Flow of control loop
        $return = false;
        do {
            // See if BMI has been recorded between >=18.5kg/m2 and <25kg/m2 6 months before, or simultanious to the encounter
            $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 18.5 AND form_vitals.BMI < 25 " .
                     "AND DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) " .
                     "AND DATE( form_vitals.date ) <= DATE( form_encounter.date ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
            $res = sqlStatement($query, array( $patient->id ));
            $number = sqlNumRows($res);
            if ($number >= 1) {
                $return = true;
                break;
            }

            // See if BMI has been recorded >=25kg/m2 6 months before, or simultanious to the encounter
            // AND 'Care goal: follow-up plan BMI management' OR 'Communication provider to provider: dietary consultation order'
            $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 25 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
            $res = sqlStatement($query, array( $patient->id ));
            $number = sqlNumRows($res);
            if (
                $number >= 1 &&
                ( Helper::check(ClinicalType::CARE_GOAL, CareGoal::FOLLOW_UP_PLAN_BMI_MGMT, $patient) ||
                  Helper::check(ClinicalType::COMMUNICATION, Communication::DIET_CNSLT, $patient) )
            ) {
                $return = true;
                break;
            }
        } while (false);

        return $return;
    }
}
