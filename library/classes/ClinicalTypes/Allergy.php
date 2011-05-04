<?php
require_once( 'ClinicalType.php' );

class Allergy extends ClinicalType
{
    const MED_ALLERGY_DTAP = 'med_allergy_dtap';
    
    public function getType() {
        return 'allergy';
    }
    
    public function getListId() {
        return 'Clinical_Rules_Allergy_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginMeasurement = null, $endMeasurement = null, $options = null ) {
        return true;
    }
    
}