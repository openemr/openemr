<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class PhysicalExam extends ClinicalType
{   
    const NOT_DONE_PATIENT = 'phys_exm_not_done_patient';
    const NOT_DONE_MEDICAL = 'phys_exm_not_done_medical';
    const NOT_DONE_SYSTEM = 'phys_exm_not_done_system';
    const FINDING_BMI_PERC = 'phys_exm_finding_bmi_perc';
    
    public function getListId() {
        return 'Clinical_Rules_Phys_Exm_Type';
    }
    
    public function getListType() {
        return "medical_problem"; // TODO this may not be the correct type for BMI icd9 codes
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null )
    {
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
