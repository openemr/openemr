<?php
class NFQ_0038_Denominator implements FilterIF
{
    public function getTitle() {
        return "Denominator";
    }
    
    public function test( Rs_Patient $Rs_Patient, $dateBegin, $dateEnd ) 
    {
        $oneEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if ( Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OUT_PCP_OBGYN, $Rs_Patient, $dateBegin, $dateEnd, $oneEncounter ) ) {
            return true;
        }
        
        return false;
    }
}
