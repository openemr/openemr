<?php
class NFQ_0038_Numerator2 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 2";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        $options = array( Medication::OPTION_COUNT => 3, Medication::OPTION_UNIQUE_DATES => true );  
        if ( Helper::check( ClinicalType::MEDICATION, Medication::IPV, $patient, $dobPlus42Days, $dobPlus2Years, $options ) &&
            !( Helper::check( ClinicalType::ALLERGY, Allergy::IPV, $patient ) ||
               Helper::check( ClinicalType::ALLERGY, Allergy::NEOMYCIN, $patient ) ||
               Helper::check( ClinicalType::ALLERGY, Allergy::STREPTOMYCIN, $patient ) || 
               Helper::check( ClinicalType::ALLERGY, Allergy::POLYMYXIN, $patient ) ) ) {
            return true;
        }
        
        return false;
    }
}
