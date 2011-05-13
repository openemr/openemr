<?php
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
    
    public function getType() {
        return 'allergy';
    }
    
    public function getListId() {
        return 'Clinical_Rules_Allergy_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) {
        // TODO check for allergy (wherever it exists... lists 'allergy' type probably.)
        return false;
    }
}
