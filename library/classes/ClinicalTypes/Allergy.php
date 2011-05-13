<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class Allergy extends ClinicalType
{
    const DTAP_VAC = 'med_allergy_dtap_vac';
    const IPV = 'med_allergy_ipv';
    const NEOMYCIN = 'med_allergy_neomycin';
    const STREPTOMYCIN = 'med_allergy_streptomycin';
    const POLYMYXIN = 'med_allergy_polymyxin';
    const HIB = 'med_allergy_hib';
    const MUMPS_VAC = 'med_allergy_mumps_vac';
    const MEASLES_VAC = 'med_allergy_measles_vac';
    const RUBELLA_VAC = 'med_allergy_rubella_vac';
    const MMR = 'med_allergy_mmr';
    const BAKERS_YEAST = 'subst_allergy_bakers_yeast';
    const VZV = 'med_allergy_vzv';
    const PNEUM_VAC = 'med_allergy_pneum_vac';
    const HEP_A_VAC = 'med_allergy_hep_a_vac';
    const HEP_B_VAC = 'med_allergy_hep_b_vac';
    const ROTAVIRUS_VAC = 'med_allergy_rotavirus_vac';
    const INFLUENZA_VAC = 'med_allergy_flu_vac';
    const INFLUENZA_IMMUN = 'med_allergy_flu_immun';
    const EGGS = 'subst_allergy_eggs';
    
    public function getType() {
        return 'allergy';
    }
    
    public function getListId() {
        return 'Clinical_Rules_Allergy_Types';
    }
    
    /*
     * 	Check to see if a patient had an allergy to THIS thing between $beginDate and $endDate
     * 	$beginDate and $endDate can be the same, indicating a check for allergy on particular date
     * 
     * 	@param	(RsPatient) $patient	Patient to check
     * 	@param	(date) $beginDate		Lower bound on date to check for allergy
     * 	@param	(date) $endDate			Upper bound on date to check for allergy
     */
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) {
        // TODO check for allergy (wherever it exists... lists 'allergy' type probably.)
        return false;
    }
}
