<?php
class NFQ_0421_Denominator implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Denominator";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $oneEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if ( Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OUTPATIENT, $patient, $dateBegin, $dateEnd, $oneEncounter ) ) {
            return true;
        }
        
        return false;
    }
}