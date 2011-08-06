<?php
class NFQ_0421_Exclusion implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Exclusion";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        // Check for terminal illness within 6 months of encounter
        $dates = Helper::fetchEncounterDates( Encounter::ENC_OUTPATIENT, $patient, $dateBegin, $dateEnd );
        foreach ( $dates as $date ) 
        {
            $dateMinusSixMonths = strtotime( '-6 month' , strtotime ( $date ) );
            $dateMinusSixMonths = date( 'Y-m-d 00:00:00' , $dateMinusSixMonths );
            if ( Helper::check( ClinicalType::CHARACTERISTIC, Characteristic::TERMINAL_ILLNESS, $patient, $dateMinusSixMonths, $date ) ) {
                return true;    
            }     
        }
        
        if ( Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::PREGNANCY, $patient, $dateBegin, $dateEnd ) ||
            Helper::check( ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_PATIENT, $patient, $dateBegin, $dateEnd ) ||
            Helper::check( ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_MEDICAL, $patient, $dateBegin, $dateEnd ) ||
            Helper::check( ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_SYSTEM, $patient, $dateBegin, $dateEnd ) ) {
            return true;
        }
        
        return false;
    }
}