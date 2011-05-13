<?php
class NFQ_0038_Numerator10 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 10";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $options = array( Medication::OPTION_COUNT => 2 );
        $dobPlus180Days = date( 'Y-m-d 00:00:00', strtotime( '+180 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::INFLUENZA_VAC, $dobPlus180Days, $dobPlus2Years, $options ) &&
            !( Helper::checkAllergy( Allergy::INFLUENZA_VAC, $patient, $patient->dob, $dateEnd ) || 
               Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $patient->dob, $dateEnd ) ||
               Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $patient->dob, $dateEnd ) ) ) {
            return true;
        }
        return false;
    }
}
