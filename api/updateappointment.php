<?php
/**
 * api/updateappointment.php Update appointment.
 *
 * API is allowed to update patient appointment information.
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
$appointmentId = $_POST['appointmentId'];
$pc_catid = $_POST['pc_catid'];
$pc_hometext = $_POST['pc_hometext'];
$appointmentDate = $_POST['appointmentDate'];
$appointmentTime = date("H:i:s", strtotime($_POST['appointmentTime']));
$app_status = $_POST['pc_apptstatus'];
$pc_title = $_POST['pc_title'];
$patientId = $_POST['patientId'];
$admin_id = $_POST['uprovider_id'];
$facility = $_POST['pc_facility'];
$pc_billing_location = $_POST['pc_billing_location'];
$pc_duration = $_POST['pc_duration'];

$app_status = $app_status == 'p' ? '+' : $app_status;

$endTime = date('H:i:s', strtotime($_POST['appointmentTime']) + $pc_duration);

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);

    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    
    $provider_username = getProviderUsername($admin_id);
    $acl_allow = acl_check('patients', 'appt', $username);

    if ($acl_allow) {

        $strQuery = "UPDATE openemr_postcalendar_events SET 
                        pc_title = '" . add_escape_custom($pc_title) . "', 
                        pc_hometext = '" . add_escape_custom($pc_hometext) . "' , 
                        pc_catid = '" . add_escape_custom($pc_catid) . "' , 
                        pc_eventDate = '" . add_escape_custom($appointmentDate) . "', 
                        pc_startTime = '" . add_escape_custom($appointmentTime) . "', 
                        pc_endTime = '" . add_escape_custom($endTime) . "', 
                        pc_aid = '" . add_escape_custom($admin_id) . "', 
                        pc_facility = '" . add_escape_custom($facility) . "',
                        pc_billing_location = '" . add_escape_custom($pc_billing_location) . "',
                        pc_duration = '" . add_escape_custom($pc_duration) . "',
                        pc_pid = '" . add_escape_custom($patientId) . "',
                        pc_apptstatus = '" . add_escape_custom($app_status) . "' 
                    WHERE pc_eid=?";

        $result = sqlStatement($strQuery,array($appointmentId));

        $device_token_badge = getDeviceTokenBadge($provider_username, 'appointment');
        $badge = $device_token_badge ['badge'];
        $deviceToken = $device_token_badge ['device_token'];
        if ($deviceToken) {
            $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'Appointment Updated!');
        }

        if ($result !== FALSE) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Appointment has been updated.';
            if ($notification_res) {
                $xml_array['notification'] = 'Update Appointment Notification(' . $notification_res . ')';
            } else {
                $xml_array['notification'] = 'Notificaiotn Failed.';
            }
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your request. Please re-submit the information again.';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'Appointment');
echo $xml;
?>