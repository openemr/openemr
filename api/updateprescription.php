<?php
/**
 * api/updateprescription.php Update prescription.
 *
 * API is allowed to update patient prescription. 
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
$xml_string = "<prescription>";

$token = $_POST['token'];

$id = $_POST['id'];
$startDate = $_POST['startDate'];
$drug = $_POST['drug'];

$dosage = $_POST['dosage'];
$quantity = $_POST['quantity'];

$per_refill = $_POST['refill'];
$medication = $_POST['medication'];
$note = $_POST['note'];
$provider_id = $_POST['provider_id'];

$patientId = $_POST['patientId'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'med', $user);

    if ($acl_allow) {
        $provider_username = getProviderUsername($provider_id);

        $strQuery = "UPDATE `prescriptions` set
                                        provider_id = " . add_escape_custom($provider_id) . ", 
                                        start_date = '" . add_escape_custom($startDate) . "', 
                                        drug = '" . add_escape_custom($drug) . "', 
                                        dosage = '" . add_escape_custom($dosage) . "', 
                                        quantity = '" . add_escape_custom($quantity) . "',  
                                        refills = '" . add_escape_custom($per_refill) . "', 
                                        medication = '" . add_escape_custom($medication) . "',
                                        date_modified = '" . date('Y-m-d') . "',
                                        note = '" . add_escape_custom($note) . "'
                             WHERE id = ?";
        $result = sqlStatement($strQuery, array($id));

        $list_result = 1;
        if ($medication) {
            $select_medication = "SELECT * FROM  `lists` 
                                    WHERE  `type` LIKE  'medication'
                                            AND  `title` LIKE  ? 
                                            AND  `pid` = ?";
            $result1 = sqlQuery($select_medication, array($drug, $patient_id));
            if (!$result1) {
                $list_query = "insert into lists(date,begdate,type,activity,pid,user,groupname,title) 
                            values (now(),cast(now() as date),'medication',1," . add_escape_custom($patientId) . ",'" . add_escape_custom($user) . "','','" . add_escape_custom($drug) . "')";
                $list_result = sqlStatement($list_query);
            }
        }

        $device_token_badge = getDeviceTokenBadge($provider_username, 'prescription');
        $badge = $device_token_badge ['badge'];
        $deviceToken = $device_token_badge ['device_token'];
        if ($deviceToken) {
            $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'Update Prescription Notification!');
        }
        if ($result !== FALSE && $list_result !== FALSE) {

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Patient prescription has been updated</reason>";
            if ($notification_res) {
                $xml_array['notification'] = 'Update Appointment Notification(' . $notification_res . ')';
            } else {
                $xml_array['notification'] = 'Notificaiotn Failed.';
            }
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

$xml_string .= "</prescription>";
echo $xml_string;
?>