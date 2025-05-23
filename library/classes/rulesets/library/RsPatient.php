<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once(dirname(__FILE__) . "/../../../patient.inc.php");

class RsPatient
{
    public $id;
    public $dob;

    public function __construct($id)
    {
        $this->id = $id;
        $this->dob = $this->get_DOB($id);
    }

    /* Function to get patient dob
     * @param $patient_id
     * @return (string) containing date of birth in the format "YYYY mm dd"
     */
    private function get_DOB($patient_id)
    {
        $dob = getPatientData($patient_id, "DOB as TS_DOB");
        $dob = $dob['TS_DOB'];
        $date = ($dob . ' 00:00:00'); // MYSQL Date Format
        return $date;
    }

    public function calculateAgeOnDate($date)
    {
        $ageInfo = parseAgeInfo($this->dob, $date);
        return $ageInfo['age'];
    }
}
