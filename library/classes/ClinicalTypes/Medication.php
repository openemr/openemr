<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class Medication extends ClinicalType
{
    const OPTION_COUNT = 'count';
    const OPTION_UNIQUE_DATES = 'unique';
    
    const DTAP_VAC = 'med_admin_dtap';
    const HEP_A_VAC = 'med_admin_hep_a_vac';
    const HEP_B_VAC = 'med_admin_hep_b_vac';
    const HIB = 'med_admin_hib';
    const MEASLES_VAC = 'med_admin_meas_vac';
    const MMR = 'med_admin_mmr';
    const IPV = 'med_admin_ipv';
    const MUMPS_VAC = 'med_admin_mumps_vac';
    const PNEUMOCOCCAL_VAC = 'med_admin_pneumococcal_vac';
    const ROTAVIRUS_VAC = 'med_admin_rotavirus_vac';
    const RUBELLA_VAC = 'med_admin_rubella_vac';
    const VZV = 'med_admin_vzv';
    const INFLUENZA_VAC = 'med_admin_influenza_vac';
    
    const NO_INFLUENZA_CONTRADICTION = 'med_not_done_flu_immun_contradiction';
    const NO_INFLUENZA_DECLINED = 'med_not_done_flu_immun_declined';
    const NO_INFLUENZA_PATIENT = 'med_not_done_flu_vac_pat_reas';
    const NO_INFLUENZA_MEDICAL = 'med_not_done_flu_vac_med_reas';
    const NO_INFLUENZA_SYSTEM = 'med_not_done_flu_vac_sys_reas';
    
    const ADVERSE_EVT_FLU_IMMUN = 'med_adverse_evt_flu_immun';
    const INTOLERANCE_FLU_IMMUN = 'med_intolerance_flu_immun';
    
    const DISP_DIABETES = 'med_disp_diabetes';
    const ORDER_DIABETES = 'med_order_diabetes';
    const ACTIVE_DIABETES = 'med_active_diabetes';
        
    public function getListId() {
        return "Clinical_Rules_Med_Types";
    }   
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) 
    {
        $return = false;
        $listOptions = Codes::lookup( $this->getOptionId(), 'OPTION_ID' );
        if ( count( $listOptions ) > 0 ) 
        {
            $query = "SELECT * " .
        	"FROM immunizations " .
        	"LEFT JOIN list_options " .
            "ON immunization_id = list_options.option_id AND list_options.list_id = 'immunizations' " .
            "LEFT JOIN patient_data " .
            "ON patient_id = patient_data.pid " .
        	"WHERE patient_id = ? " .
            "AND administered_date >= ? " .
            "AND administered_date < ? ";
            $query.= "AND ( ";
            $count = 0;
            foreach( $listOptions as $option_id ) {
            	$query.= "list_options.option_id = '".$option_id."' ";
            	$count++;
            	if ( $count < count( $listOptions ) ) {
            	    $query.= "OR ";
            	}
            }
            $query.= " ) "; 

            $result = sqlStatement( $query, array( $patient->id, $beginDate, $endDate ) );
            $rows = array();
            for( $iter = 0; $row = sqlFetchArray( $result ); $iter++ ) {
                    $rows[$iter] = $row;
            }
            
            if ( isset( $options[self::OPTION_COUNT] ) && 
                count( $rows ) >= $options[self::OPTION_COUNT] ) {
                $return = true;    
            } else if ( !isset( $options[self::OPTION_COUNT] ) &&
                count( $rows ) > 0 ) {
                $return = true;
            }
        }
        
        return $return;
    }
}