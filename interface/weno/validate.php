<?php
/**
 *
 * Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com> Open Med Practice
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 **/

include_once('../globals.php');
include_once('transmitDataClass.php');

$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$validation = new transmitData();

$patient = $validation->validatePatient($pid);
$pharmacy = $validation->patientPharmacyInfo($pid);

if(empty($GLOBALS['weno_account_id'])){
	print xlt("Weno Account ID information missing")."<br>";
	exit;
}
if(empty($GLOBALS['weno_provider_id'])){
	print xlt("Weno Account Clinic ID information missing")."<br>";
	exit;
}
if(empty($patient['DOB'])){
	print xlt("Patient DOB missing"). "<br>";
	exit;
}
if(empty($patient['street'])){
	print xlt("Patient street missing"). "<br>";
	exit;
}
if(empty($patient['postal_code'])){
	print xlt("Patient Zip Code missing"). "<br>";
	exit;
}
if(empty($patient['city'])){
	print xlt("Patient city missing"). "<br>";
	exit;
}
if(empty($patient['state'])){
	print xlt("Patient state missing"). "<br>";
	exit;
}
if(empty($patient['sex'])){
	print xlt("Patient sex missing"). "<br>";
	exit;
}
if(empty($pharmacy['name'])){
	print xlt("Pharmacy not assigned to the patient"). "<br>";
	exit;
}
header('Location: confirm.php');
