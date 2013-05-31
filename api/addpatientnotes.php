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
require_once 'classes.php';

$xml_array = array();

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$notes = $_POST['notes'];
$title = isset($_POST['title']) ? $_POST['title'] : 'Unassigned';
$authorized = isset($_POST['authorized']) ? $_POST['title'] : '0';
$activity = isset($_POST['activity']) ? $_POST['activity'] : '1';
$assigned_to = isset($_POST['assigned_to']) ? $_POST['assigned_to'] : '';
$datetime = isset($_POST['datetime']) ? $_POST['datetime'] : date('YYYY-MM-DD HH:MM:SS');
$message_status = isset($_POST['message_status']) ? $_POST['message_status'] : 'New';

if ($userId = validateToken($token)) {
    
    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    if ($acl_allow) {
    
        $result = addPnote($patientId, $notes, $authorized, $activity, $title, $assigned_to, $datetime, $message_status, $username);

        if ($result) {
            $xml_array['status'] = 0;
            $xml_array['result'] = $result;
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