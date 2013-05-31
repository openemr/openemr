<?php
/**
 * api/updatenotificationbadge.php Update notification badge.
 *
 * API is allowed to update notification badge for push notifications.
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
$xml_string = "<badge>";

$token = $_POST['token'];

$message_badge = $_POST['message_badge'];
$appointment_badge = $_POST['appointment_badge'];
$labreports_badge = $_POST['labreports_badge'];
$prescription_badge = $_POST['prescription_badge'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'demo', $user);

    if ($acl_allow) {
        $badges = getAllBadges($token);

        $message_badge = $message_badge >= 0 ? $message_badge : $badges['message_badge'];
        $appointment_badge = $appointment_badge >= 0 ? $appointment_badge : $badges['appointment_badge'];
        $labreports_badge = $labreports_badge >= 0 ? $labreports_badge : $badges['labreports_badge'];
        $prescription_badge = $prescription_badge >= 0 ? $prescription_badge : $badges['prescription_badge'];

        $strQuery = "UPDATE `api_tokens` SET 
        `message_badge`= ".add_escape_custom($message_badge).",`appointment_badge`= ".add_escape_custom($appointment_badge).",
        `labreports_badge`= ".add_escape_custom($labreports_badge).",`prescription_badge`= ".add_escape_custom($prescription_badge)." WHERE token=?";


        $result = sqlStatement($strQuery,array($token));

        if ($result !== FALSE) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Badges has been updated</reason>";
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

$xml_string .= "</badge>";
echo $xml_string;
?>