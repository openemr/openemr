<?php
class NFQ_0421_Numerator1 implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator 1";
    }

    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {        
        // Flow of control loop
        $return = false;
        do {
            // See if BMI has been recorded between >=22kg/m2 and <30kg/m2 6 months before, or simultanious to the encounter
            $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
          			 "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 22 AND form_vitals.BMI < 30 " .
                     "AND DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) " .
                     "AND DATE( form_vitals.date ) <= DATE( form_encounter.date ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
            $res = sqlStatement( $query, array( $patient->id ) );
            $number = sqlNumRows($res);
            if ( $number >= 1 ) {
                $return = true;
                break;
            }

            // See if BMI has been recorded >=30kg/m2 6 months before, or simultanious to the encounter
            // AND ÒCare goal: follow-up plan BMI managementÓ OR ÒCommunication provider to provider: dietary consultation orderÓ
            $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 30 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
            $res = sqlStatement( $query, array( $patient->id ) );
            $number = sqlNumRows($res);
            if ( $number >= 1 &&
                ( Helper::check( ClinicalType::CARE_GOAL, CareGoal::FOLLOW_UP_PLAN_BMI_MGMT ) ||
                  Helper::check( ClinicalType::COMMUNICATION, Communication::DIET_CNSLT ) ) ) {
                $return = true;
                break;
            }

            // See if BMI has been recorded <22kg/m2 6 months before, or simultanious to the encounter
            // AND ÒCare goal: follow-up plan BMI managementÓ OR ÒCommunication provider to provider: dietary consultation orderÓ
            $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI < 22 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
            $res = sqlStatement( $query, array( $patient->id ) );
            $number = sqlNumRows($res);
            if ( $number >= 1 && 
                ( Helper::check( ClinicalType::CARE_GOAL, CareGoal::FOLLOW_UP_PLAN_BMI_MGMT ) ||
                  Helper::check( ClinicalType::COMMUNICATION, Communication::DIET_CNSLT ) ) ) {
                $return = true;
                break;
            }
        } while( false );

        return $return;
    }
}