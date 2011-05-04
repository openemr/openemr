<?php
class NFQ_0024_Numerator1 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 1";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        if ( Helper::check( ClinicalType::PHYSICAL_EXAM, PhysicalExam::FINDING_BMI_PERC, $patient, $dateBegin, $dateEnd ) ) {
            return true;
        }
        
        return false;
    }
}