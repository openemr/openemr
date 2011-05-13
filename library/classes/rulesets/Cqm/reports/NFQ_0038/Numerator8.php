<?php
class NFQ_0038_Numerator8 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 8";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $options = array( Medication::OPTION_COUNT => 2 );
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::HEP_A, $dobPlus42Days, $dobPlus2Years, $options ) ||
            ( Helper::checkDiagResolved( Diagnosis::HEP_A, $patient, $patient->dob, $endDate ) &&
              !Helper::checkAllergy( Allergy::HEP_A_VAC, $patient, $patient->dob, $endDate ) ) ) {
            return true;
        }
        return false;
    }
}
