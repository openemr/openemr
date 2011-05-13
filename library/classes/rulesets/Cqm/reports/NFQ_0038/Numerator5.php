<?php
class NFQ_0038_Numerator5 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 5";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $options = array( Medication::OPTION_COUNT => 3 );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::HEP_B, $patient->dob, $dobPlus2Years, $options ) ||
            Helper::checkDiagResolved( Diagnosis::HEP_B, $patient, $patient->dob, $dateEnd ) &&
            !( Helper::checkAllergy( Allergy::HEP_B_VAC, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkAllergy( Allergy::BAKERS_YEAST, $patient, $patient->dob, $dateEnd ) ) ) {
            return true;
        }
        return false;
    }
}
