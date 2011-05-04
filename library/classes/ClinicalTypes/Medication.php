<?php
require_once( 'ClinicalType.php' );
/*
 * This class represents types of medication administered to a patient.
 */
class Medication extends ClinicalType
{
    const OPTION_COUNT = 'count';
    
    const DTAP = 'med_dtap';
    const HEP_A = 'med_hep_a_vac';
    const HEP_B = 'med_hep_b_vac';
    const HIB = 'med_hib';
    const MEASLES = 'med_meas_vac';
    const MMR = 'med_mmr';
    const IPV = 'med_ipv';
    const MUMPS = 'med_mumps_vac';
    const PNEUMOCOCCAL = 'mad_pneumococcal_vac';
    const ROTAVIRUS = 'mad_rotavirus_vac';
    const RUBELLA = 'med_rubella_vac';
    const VZV = 'med_vzv';
    
    public function getListId() {
        return "Clinical_Rules_Med_Types";
    }   
    
    public function doPatientCheck( RsPatient $patient, $beginMeasurement = null, $endMeasurement = null, $options = null ) {
        return true;
    }
}