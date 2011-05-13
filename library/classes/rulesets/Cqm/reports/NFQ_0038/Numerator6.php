<?php
class NFQ_0038_Numerator6 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 6";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $options = array( Medication::OPTION_COUNT => 1 );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::VZV, $patient->dob, $dobPlus2Years, $options ) ||
             ( Helper::checkDiagResolved( Diagnosis::VZV, $patient, $patient->dob, $dateEnd ) &&
               !( Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $dateBegin, $dateEnd ) ||
                  Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $dateBegin, $dateEnd ) ||
                  Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $dateBegin, $dateEnd ) ||
                  Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $dateBegin, $dateEnd ) ||
                  Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $dateBegin, $dateEnd ) ||
                  Helper::checkAllergy( Allergy::VZV, $patient, $patient->dob, $dateEnd ) ||
                  Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $dateBegin, $dateEnd ) ) ) ) {
            return true;
        }
        return false;
    }
}
