<?php
require_once( 'ClinicalType.php' );
/*
 * This class represents types of medication administered to a patient.
 */
class Medication extends ClinicalType
{
    const OPTION_COUNT = 'count';
    const OPTION_UNIQUE_DATES = 'unique';
    
    const DTAP = 'med_dtap';
    const HEP_A_VAC = 'med_hep_a_vac';
    const HEP_B_VAC = 'med_hep_b_vac';
    const HIB = 'med_hib';
    const MEASLES_VAC = 'med_meas_vac';
    const MMR = 'med_mmr';
    const IPV = 'med_ipv';
    const MUMPS_VAC = 'med_mumps_vac';
    const PNEUMOCOCCAL_VAC = 'med_pneumococcal_vac';
    const ROTAVIRUS_VAC = 'med_rotavirus_vac';
    const RUBELLA_VAC = 'med_rubella_vac';
    const VZV = 'med_vzv';
    const INFLUENZA_VAC = 'med_influenza_vac';
    
    public function getListId() {
        return "Clinical_Rules_Med_Types";
    }   
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) 
    {
        $return = false;
        $listOptions = Codes::lookup( $this->getOptionId(), 'OPTION_ID' );
        if ( count( $listOptions ) > 0 ) 
        {
            $query = "SELECT immunizations.administered_date, immunizations.patient_id, immunizations.immunization_id, list_options.title, patient_data.pid, patient_data.DOB " .
        	"FROM immunizations " .
        	"LEFT JOIN list_options " .
            "ON immunizations.immunization_id = list_options.option_id AND list_id = immunizations" .
            "LEFT JOIN patient_data " .
            "ON immunizations.patient_id = patient_data.pid " .
        	"WHERE immunizations.patient_id = ? " .
            "AND DATE( immunizations.administered_date ) >= ? " .
            "AND DATE( immunizations.administered_date ) < ? " .
            "AND ( ";
            foreach( $listOptions as $option_id ) {
            	$query.= "list_options.option_id = ".$option_id." "; 
            }
            $query.= " ) "; 
            $result = sqlStatement( $query, array( $patient->id, $beginDate, $endDate ) );
            if ( isset( $options[self::OPTION_COUNT] ) && 
                count( $result ) >= $options[self::OPTION_COUNT] ) {
                $return = true;    
            } else if ( count( $result ) > 0 ) {
                $return = true;
            }
        }
        
        return $return;
    }
}