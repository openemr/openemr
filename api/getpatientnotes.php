<?php

/**
 * api/getpatientnotes.php get patient's notes.
 *
 * Api to get patient notes.
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

$xml_array = array();

$token = $_POST['token'];
$patient_id = $_POST['patientId'];
$active = isset($_POST['active']) ? $_POST['active'] : 1;

if ($userId = validateToken($token)) {

    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    if ($acl_allow) {

        $patient_data = getPnotesByDate("", $active, 'id,date,body,user,activity,title,assigned_to,message_status', $patient_id);

        if ($patient_data) {

            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Patient notes has been fetched successfully';
            foreach ($patient_data as $key => $patientnote) {
                $xml_array['patientnote' . $key]['id'] = $patientnote['id'];
                $xml_array['patientnote' . $key]['date'] = $patientnote['date'];
                $xml_array['patientnote' . $key]['body'] = $patientnote['body'];
                $xml_array['patientnote' . $key]['user'] = $patientnote['user'];
                $xml_array['patientnote' . $key]['activity'] = $patientnote['activity'];
                $xml_array['patientnote' . $key]['title'] = $patientnote['title'];
                $xml_array['patientnote' . $key]['assigned_to'] = $patientnote['assigned_to'];
                $xml_array['patientnote' . $key]['message_status'] = $patientnote['message_status'];
            }
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