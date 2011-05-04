<?php
class NFQ_0024_Numerator3 implements CqmFilterIF
{
    public function getTitle() {
        return "Numerator 3";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd ) 
    {
        if ( Helper::check( ClinicalType::COMMUNICATION, Communication::COUNS_PHYS_ACTIVITY, $patient, $dateBegin, $dateEnd ) ) {
            return true;
        }
        
        return false;
    }
}
