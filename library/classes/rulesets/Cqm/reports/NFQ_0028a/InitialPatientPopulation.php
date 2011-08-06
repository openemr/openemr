<?php
class NFQ_0028a_InitialPatientPopulation implements CqmFilterIF
{ 
    public function getTitle() 
    {
        return "Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $oneEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        $twoEncounters = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );
    
        if ( convertDobtoAgeYearDecimal( $patient->dob, $dateBegin ) >= 18 &&
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OFF_VIS, $patient, $dateBegin, $dateEnd, $twoEncounters ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_HEA_AND_BEH, $patient, $dateBegin, $dateEnd, $twoEncounters ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OCC_THER, $patient, $dateBegin, $dateEnd, $twoEncounters ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PSYCH_AND_PSYCH, $patient, $dateBegin, $dateEnd, $twoEncounters ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_SER_18_OLDER, $patient, $dateBegin, $dateEnd, $oneEncounter ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PRE_IND_COUNSEL, $patient, $dateBegin, $dateEnd, $oneEncounter ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_GROUP_COUNSEL, $patient, $dateBegin, $dateEnd, $oneEncounter ) ||
            Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_OTHER_SERV, $patient, $dateBegin, $dateEnd, $oneEncounter ) ) {
            return true;
        } 
        
        return false;
    }
}