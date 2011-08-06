<?php
require_once( 'ClinicalType.php' );

class CareGoal extends ClinicalType
{
    const FOLLOW_UP_PLAN_BMI_MGMT = 'flwup_bmi_mgmt';
    
    public function getListId() {
        return 'Clinical_Rules_Care_Goal_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginMeasurement = null, $endMeasurement = null, $options = null ) {
        return true;
    }
}