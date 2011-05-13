<?php
require_once( 'ClinicalType.php' );

class Diagnosis extends ClinicalType
{
    const OPTION_STATE = 'state';
    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_RESOLVED = 'resolved';
    
    const HYPERTENSION = 'diag_hypertension';
    const PREGNANCY = 'diag_pregnancy';
    const ENCEPHALOPATHY = 'diag_encephalopathy';
    const PROG_NEURO_DISORDER = 'diag_prog_neuro_disorder';
    const CANCER_LYMPH_HIST = 'diag_cancer_lypmh_hist';
    const ASYMPTOMATIC_HIV = 'diag_asymptomatic_hiv';
    const MULT_MYELOMA = 'diag_mult_myeloma';
    const LUKEMIA = 'diag_lukemia';
    const IMMUNODEF = 'diag_immunodef';
    const MEASLES = 'diag_measles';
    const MUMPS = 'diag_mumps';
    const RUBELLA = 'diag_rubella';
    const HEP_B = 'diag_hep_b';
    const HEP_A = 'diag_hep_a';
    const VZV = 'diag_vzv';
        
    public function getListType() {
        return 'medical_problem';
    }
    
    public function getListColumn() {
        return 'diagnosis';
    }
    
    public function getListId() {
        return 'Clinical_Rules_Diagnosis_Types';
    }
    
    /*
     * Check if the patient has this diagnosis
     * 
     * @param (CqmPatient) $patient
     * @param (date) $beginMeasurement
     * @param (date) $endMeasurement
     * 
     * @return true if patient meets criteria, false ow
     */
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) {
        $data = Codes::lookup( $this->getOptionId() );
        $type = $this->getListType();
        foreach( $data as $codeType => $codes ) {
            foreach ( $codes as $code ) {
                if ( exist_lists_item( $patient->id, $type, $codeType.'::'.$code, $endDate ) ) {
                    return true;
                }
            }
        }
        return false;
    }
}