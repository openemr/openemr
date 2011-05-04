<?php
class NFQ_0024_Denominator implements CqmFilterIF
{
    public function getTitle() {
        return "Denominator";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd ) 
    {
        $oneEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if ( Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OUT_PCP_OBGYN, $patient, $dateBegin, $dateEnd, $oneEncounter ) &&
            !Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::PREGNANCY, $patient, $dateBegin, $dateEnd ) ||
            !Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_PREGNANCY, $patient, $dateBegin, $dateEnd, $oneEncounter ) ) {
            return true;
        }
        
        return false;
    }
}