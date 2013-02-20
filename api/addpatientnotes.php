<?php
/**
 * api/addpatientnotes.php add patient's notes.
 *
 * Api add's patient notes.
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
require_once 'includes/class.database.php';
require_once 'includes/functions.php';
require_once 'includes/class.arraytoxml.php';

$xml_array = array();
$token = $_POST['token'];
$patientId = $_POST['patientId'];
$notes = $_POST['notes'];
$title = $_POST['title'];

if ($userId = validateToken($token)) {
    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {

        $strQuery = "INSERT INTO pnotes (date, body, pid, user, title, assigned_to, deleted, message_status) 
					 VALUES ('" . date('Y-m-d H:i:s') . "', '" . add_escape_custom($notes) . "', " . add_escape_custom($patientId) . ", '" . add_escape_custom($username) . "', '" . add_escape_custom($title) . "', '" . add_escape_custom($username) . "', 0, 'New')";
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Patient notes has been added successfully';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'PatientNotes');
echo $xml;
?>