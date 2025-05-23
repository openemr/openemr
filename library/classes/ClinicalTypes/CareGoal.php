<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once('ClinicalType.php');

class CareGoal extends ClinicalType
{
    const FOLLOW_UP_PLAN_BMI_MGMT = 'flwup_bmi_mgmt';

    public function getListId()
    {
        return 'Clinical_Rules_Care_Goal_Types';
    }

    public function doPatientCheck(RsPatient $patient, $beginMeasurement = null, $endMeasurement = null, $options = null)
    {
        return true;
    }
}
