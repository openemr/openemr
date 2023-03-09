<?php

namespace OpenEMR\OemrAd;


class PatientVerification {
	
	/*Constructor*/
	public function __construct() {	
	}

	/*
	Author: Hardik Khatri
	Description: Check is patient exists or not
	*/

	/*
	public static function isPatientExists($firstName = "", $lastName = "", $dob = "") {
		$sql = "select * from patient_data where fname= ? AND lname = ? AND DOB = ?";
		$row = sqlQuery($sql, array($firstName, $lastName, $dob));
		return $row;
	}*/
}
