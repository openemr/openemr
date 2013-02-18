<?php
/**
 * api/getprescription.php Get prescription.
 *
 * API is allowed to get patient prescription.
 *
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');

$xml_string = "";
$xml_string .= "<PrescriptionList>\n";

$token = $_POST['token'];
$patientId = $_POST['patientID'];
$visit_id = isset($_POST['visit_id']) && !empty($_POST['visit_id']) ? $_POST['visit_id'] : '';

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);
    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('patients', 'med', $username);
    if ($acl_allow) {
        if ($visit_id) {
            $strQuery = "SELECT p.*,u.id AS provider_id,u.fname AS provider_fname,u.lname AS provider_lname,u.mname AS provider_mname 
                            FROM prescriptions as p
                            LEFT JOIN `users` as u ON u.id = p.provider_id
                            WHERE patient_id = ? AND encounter =?";

            $result = sqlStatement($strQuery, array($patientId, $visit_id));
            if ($result->_numOfRows > 0) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>The Patient Employer Record has been fetched</reason>\n";
                $data = "";
                while ($res = sqlFetchArray($result)) {
                    $data .= "<prescription>\n";
                    foreach ($res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $data .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                    $data .= "</prescription>\n";
                }
                $xml_string .= "<data>" . $data . "</data>";
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
            }
        } else {

            $strQuery = "SELECT p.*,u.id AS provider_id,u.fname AS provider_fname,u.lname AS provider_lname,u.mname AS provider_mname 
                            FROM prescriptions as p
                            LEFT JOIN `users` as u ON u.id = p.provider_id
                            WHERE patient_id =?";

            $result = sqlStatement($strQuery, array($patientId));
            if ($result->_numOfRows > 0) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>The Patient Employer Record has been fetched</reason>\n";
                $data = "";
                while ($res = sqlFetchArray($result)) {
                    $data .= "<prescription>\n";
                    foreach ($res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $data .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                    $data .= "</prescription>\n";
                }
                $xml_string .= "<data>" . $data . "</data>";
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
            }
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}

$xml_string .= "</PrescriptionList>\n";
echo $xml_string;
?>