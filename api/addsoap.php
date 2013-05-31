<?php
/**
 * api/addsoap.php add patient's Subjective Objective Assessment and Plan.
 *
 * Api add's patient SOAP against particular visit.
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
$xml_string = "<soap>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];

$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : NULL;
$subjective = $_POST['subjective'];
$objective = $_POST['objective'];
$assessment = $_POST ['assessment'];
$plan = $_POST['plan'];
$authorized = isset($_POST['authorized']) ? $_POST['authorized'] : 0;
$activity = isset($_POST['activity']) ? $_POST['activity'] : 1;

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);

    if ($acl_allow) {
        $strQuery = "INSERT INTO form_soap 
            (pid, user, date, groupname, authorized, activity, subjective, objective, assessment,  plan) 
            VALUES (" . $patientId . ", '" . add_escape_custom($user) . "', '" . date('Y-m-d H:i:s') . "','" . add_escape_custom($groupname) . "', '" . add_escape_custom($authorized) . "','" . add_escape_custom($activity) . "',  '" . add_escape_custom($subjective) . "' , '" . add_escape_custom($objective) . "' , '" . add_escape_custom($assessment) . "', '" . add_escape_custom($plan) . "')";

        $result = sqlInsert($strQuery);
        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'SOAP', $last_inserted_id, $formdir = 'soap', $patientId, $authorized = "1", $date = "NOW()", $user, $group = "Default");

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Soap has been added</reason>";
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

$xml_string .= "</soap>";
echo $xml_string;
?>