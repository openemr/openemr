<?php
class NFQ_0013_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $twoEncounters = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );
        if ( convertDobtoAgeYearDecimal( $patient->dob, $dateBegin ) >= 18 &&
            Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::HYPERTENSION, $patient, $dateBegin, $dateEnd ) &&
            ( Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OUTPATIENT, $patient, $dateBegin, $dateEnd, $twoEncounters ) ||
              Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_NURS_FAC, $patient, $dateBegin, $dateEnd, $twoEncounters ) ) ) {
            return true;
        } 
        
        return false;
    }
}