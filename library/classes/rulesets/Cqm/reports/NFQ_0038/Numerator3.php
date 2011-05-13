<?php
class NFQ_0038_Numerator3 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 3";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        $dobPlus1Year = date( 'Y-m-d 00:00:00', strtotime( '+1 year', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        $dateMinus2Years = date( 'Y-m-d 00:00:00', strtotime( '-2 year', strtotime( $dateEnd ) ) ); 
        $options = array( Medication::OPTION_COUNT => 1 );  
        if ( Helper::checkMed( Medication::MMR, $patient, $dobPlus1Year, $dobPlus2Years, $options ) ||
             ( Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::MEASLES, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::MUMPS, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::RUBELLA, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) ) &&
              !( Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $dateBegin, $dateEnd ) ||
                 Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $dateBegin, $dateEnd ) ||
                 Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $dateBegin, $dateEnd ) ||
                 Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $dateBegin, $dateEnd ) ||
                 Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $dateBegin, $dateEnd ) ||
                 Helper::checkAllergy( Allergy::MMR, $patient, $patient->dob, $dateMinus2Years ) ||
                 Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $dateBegin, $dateEnd ) ) ) {
            return true;
        }
        
        return false;
    }
}
