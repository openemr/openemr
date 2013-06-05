<?php
/**
 * api/addspeechdictation.php add patient's Speech Dictation.
 *
 * Api add's patient Speech Dictation against particular visit/encounter.
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
$xml_string = "<speechdictation>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];

$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : NULL;
$dictation = $_POST['dictation'];
$additional_notes = $_POST['additional_notes'];
$authorized = isset($_POST['authorized']) ? $_POST['authorized'] : 0;
$activity = isset($_POST['activity']) ? $_POST['activity'] : 1;

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);

    if ($acl_allow) {
        $strQuery = "INSERT INTO form_dictation 
            (pid, user, date, groupname, authorized, activity, dictation, additional_notes) 
            VALUES (" . $patientId . ", '" . add_escape_custom($user) . "', '" . date('Y-m-d H:i:s') . "','" . add_escape_custom($groupname) . "', '" . add_escape_custom($authorized) . "','" . add_escape_custom($activity) . "',  '" . add_escape_custom($dictation) . "' , '" . add_escape_custom($additional_notes) . "')";

        $result = sqlInsert($strQuery);
        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'Speech Dictation', $last_inserted_id, $formdir = 'dictation', $patientId, $authorized = "1", $date = "NOW()", $user, $group = "Default");

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Speech Dictation has been added</reason>";
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

$xml_string .= "</speechdictation>";
echo $xml_string;
?>