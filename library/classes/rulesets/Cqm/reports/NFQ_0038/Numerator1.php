<?php
class NFQ_0038_Numerator1 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 1";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        $options = array( Medication::OPTION_COUNT => 4, Medication::OPTION_UNIQUE_DATES => true );  
        if ( Helper::check( ClinicalType::MEDICATION, Medication::DTAP, $patient, $dobPlus42Days, $dobPlus2Years, $options ) &&
            !( Helper::check( ClinicalType::ALLERGY, Allergy::DTAP_VAC, $patient ) ||
               Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::ENCEPHALOPATHY, $patient, $dateBegin, $dateEnd ) ||
               Helper::check( ClinicalType::DIAGNOSIS, Diagnosis::PROG_NEURO_DISORDER, $patient, $dateBegin, $dateEnd ) ) ) {
            return true;
        }
        
        return false;
    }
}
