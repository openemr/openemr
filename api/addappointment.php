<?php

/**
 * api/addappointment.php Schedule new appointment.
 *
 * Api allows to schedule new appointment for a patient.
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
$pc_catid = $_POST['pc_catid'];
$patientId = $_POST['patientId'];
$pc_title = $_POST['pc_title'];
$appointmentDate = $_POST['appointmentDate'];
$appointmentTime = date("H:i:s", strtotime($_POST['appointmentTime']));
$app_status = $_POST['pc_apptstatus'];
$admin_id = $_POST['uprovider_id'];
$facility = $_POST['pc_facility'];
$pc_billing_location = $_POST['pc_billing_location'];
$comments = $_POST['pc_hometext'];
$pc_duration = $_POST['pc_duration'];


$endTime = date('H:i:s', strtotime($_POST['appointmentTime']) + $pc_duration);


$recurrspecs = array("event_repeat_freq" => "",
    "event_repeat_freq_type" => "",
    "event_repeat_on_num" => "1",
    "event_repeat_on_day" => "0",
    "event_repeat_on_freq" => "0",
    "exdate" => ""
);
$recurrspec = serialize($recurrspecs);

$locationspecs = array("event_location" => "",
    "event_street1" => "",
    "event_street2" => "",
    "event_city" => "",
    "event_state" => "",
    "event_postal" => ""
);
$locationspec = serialize($locationspecs);

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

        $strQuery = "INSERT INTO openemr_postcalendar_events 
                            (pc_pid, pc_title, pc_hometext , pc_time, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_catid, pc_aid, pc_facility, pc_billing_location, pc_duration , pc_informant, pc_eventstatus, pc_sharing, pc_recurrspec, pc_location) 
                                VALUES (" . add_escape_custom($patientId) . ",
                                        '" . add_escape_custom($pc_title) . "' ,
                                        '" . add_escape_custom($comments) . "' ,
                                        '" . date('Y-m-d H:i:s') . "',
                                        '" . add_escape_custom($appointmentDate) . "',
                                        '" . add_escape_custom($appointmentTime) . "',
                                        '" . add_escape_custom($endTime) . "',
                                        '" . add_escape_custom($app_status) . "',
                                        '" . add_escape_custom($pc_catid) . "',
                                        '" . add_escape_custom($admin_id) . "',
                                        '" . add_escape_custom($facility) . "',
                                        '" . add_escape_custom($pc_billing_location) . "',
                                        '" . add_escape_custom($pc_duration) . "',
                                        1,
                                        1,
                                        1,
                                        '" . add_escape_custom($recurrspec) . "',
                                        '" . add_escape_custom($locationspec) . "')";
        $result = sqlStatement($strQuery);

        $device_token_badge = getDeviceTokenBadge($provider_username, 'appointment');
        $badge = $device_token_badge ['badge'];
        $deviceToken = $device_token_badge ['device_token'];
        if ($deviceToken) {
            $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Appointment Notification!');
        }

        if ($result) {

            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Appointment has been added.';
            if ($notification_res) {
                $xml_array['notification'] = 'Add Appointment Notification(' . $notification_res . ')';
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