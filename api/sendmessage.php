<?php

/**
 * api/sendmessage.php Send message.
 *
 * API is allowed to send message.
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

$xml_string = "<Message>";



$token = $_POST['token'];

$patientId = $_POST['patientId'];

$authorized = $_POST['authorized'] ? $_POST['authorized'] : 1;

$activity = $_POST['activity'] ? $_POST['activity'] : 1;

$title = $_POST['title'];

$newtext = $_POST['newtext'];

$assigned_to = $_POST['assigned_to'];

$message_status = $_POST['message_status'];



$message_id = isset($_POST['message_id']) && !empty($_POST['message_id']) ? $_POST['message_id'] : '';





if ($userId = validateToken($token)) {

    $user = getUsername($userId);

    $acl_allow = acl_check('patients', 'notes', $user);



    if ($acl_allow) {

    $provider_id = $userId;



    $assigned_to_array = explode(',', $assigned_to);

    $_SESSION['authUser'] = $user;

    $_SESSION['authProvider'] = 'Default';



    foreach ($assigned_to_array as $assignee) {

        if ($message_status == 'Done' && !empty($message_id)) {

            updatePnoteMessageStatus($message_id, $message_status);

            $result = 1;

            break;

        } else {

            $result = addPnote($patientId, $newtext, $authorized, $activity, $title, $assignee, $datetime = '', $message_status);

            $device_token_badge = getDeviceTokenBadge($assignee,'message');

            $badge = $device_token_badge ['badge'];

            $deviceToken = $device_token_badge ['device_token'];

            if ($deviceToken) {

                $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Message Notification!');

            }

        }

    }

    if ($result) {

        $xml_string .= "<status>0</status>";

        $xml_string .= "<reason>Message send successfully</reason>";

        if ($notification_res) {

            $xml_string .= "<notification>Notification({$notification_res}) Sent.</notification>";

        } else {

            $xml_string .= "<notification>Notification Failed.</notification>";

        }

    } else {

        $xml_string .= "<status>-1</status>";

        $xml_string .= "<reason>Could not send message</reason>";

    }

    } else {

        $xml_string .= "<status>-2</status>\n";

        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";

    }

} else {

    $xml_string .= "<status>-2</status>";

    $xml_string .= "<reason>Invalid Token</reason>";

}



$xml_string .= "</Message>";

echo $xml_string;

?>