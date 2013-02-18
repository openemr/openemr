<?php
/**
 * api/updatefeesheet.php Update fee sheet.
 *
 * API is allowed to update feehsheet items details for billing. 
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
require_once 'classes.php';

$xml_string = "";
$xml_string = "<feesheet>";

$token = $_POST['token'];
$id = add_escape_custom($_POST['id']);

$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];
$provider_id = $_POST['provider_id'];
$supervisor_id = $_POST['supervisor_id'];
$auth = $_POST['auth'];
$code_type = $_POST['code_type'];
$code = $_POST['code'];
$modifier = $_POST['modifier'];
$units = max(1, intval(trim($_POST['units'])));
$price = $_POST['price'];
$priceLevel = $_POST['priceLevel'];
$justify = $_POST['justify'];

$ndc_info = !empty($_POST['ndc_info']) ? $_POST['ndc_info'] : '';
$noteCodes = !empty($_POST['noteCodes']) ? $_POST['noteCodes'] : '';
$fee = sprintf('%01.2f', (0 + trim($price)) * $units);

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('acct', 'bill', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {

        $strQuery = 'UPDATE billing SET ';
        $strQuery .= ' code_type = "' . add_escape_custom($code_type) . '",';
        $strQuery .= ' code = "' . add_escape_custom($code) . '",';
        $strQuery .= ' modifier = "' . add_escape_custom($modifier) . '",';
        $strQuery .= ' justify = "' . add_escape_custom($justify) . '",';
        $strQuery .= ' authorized = "' . add_escape_custom($auth) . '",';
        $strQuery .= ' provider_id = "' . add_escape_custom($provider_id) . '",';
        $strQuery .= ' units = "' . add_escape_custom($units) . '",';
        $strQuery .= ' bill_process = 0,';
        $strQuery .= ' notecodes = "' . add_escape_custom($notesCodes) . '",';
        $strQuery .= ' fee = "' . add_escape_custom($fee) . '"';
        $strQuery .= ' WHERE id = ?';

        $result = sqlStatement($strQuery,array($id));

        $strQuery1 = 'UPDATE `patient_data` SET';
        $strQuery1 .= ' pricelevel  = "' . add_escape_custom($priceLevel) . '"';
        $strQuery1 .= ' WHERE pid = ?';

        $result1 = sqlStatement($strQuery1,array($patientId));

        $strQuery2 = 'UPDATE `form_encounter` SET';
        $strQuery2 .= ' provider_id  = "' . add_escape_custom($provider_id) . '",';
        $strQuery2 .= ' supervisor_id  = "' . add_escape_custom($supervisor_id) . '"';
        $strQuery2 .= ' WHERE pid = ?' . ' AND encounter = ?';

        $result2 = sqlStatement($strQuery2,array($patientId,$visit_id));


        if ($result !== FALSE && $result1 !== FALSE && $result2 !== FALSE) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Feesheet has been updated</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</feesheet>";
echo $xml_string;
?>