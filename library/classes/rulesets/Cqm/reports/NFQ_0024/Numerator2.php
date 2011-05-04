<?php
class NFQ_0024_Numerator2 implements CqmFilterIF
{
    public function getTitle() {
        return "Numerator 2";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd ) 
    {
        if ( Helper::check( ClinicalType::COMMUNICATION, Communication::COUNS_NUTRITION, $patient, $dateBegin, $dateEnd ) ) {
            return true;
        }
        
        return false;
    }
}
