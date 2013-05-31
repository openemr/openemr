<?php

/**
 * api/getnotifications.php patient notifications.
 *
 * API returns patients notifications.
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
$xml_string = "<notifications>";

$token = $_POST['token'];
$primary_business_entity = 0;

if ($userId = validateToken($token)) {

    if ($GLOBALS['push_notification']) {
        $strQuery = "SELECT * FROM `api_tokens` WHERE token = ?";
        $result = sqlQuery($strQuery, array($token));

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Notifications fetching.</reason>";
            $xml_string .= "<message_badge>{$result['message_badge']}</message_badge>";
            $xml_string .= "<appointment_badge>{$result['appointment_badge']}</appointment_badge>";
            $xml_string .= "<labreports_badge>{$result['labreports_badge']}</labreports_badge>";
            $xml_string .= "<prescription_badge>{$result['prescription_badge']}</prescription_badge>";
            $xml_string .= "<total_badge>" . ($result['message_badge'] + $result['appointment_badge'] + $result['labreports_badge'] + $result['prescription_badge']) . "</total_badge>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>Notifications fetching.</reason>";
        $xml_string .= "<message_badge>0</message_badge>";
        $xml_string .= "<appointment_badge>0</appointment_badge>";
        $xml_string .= "<labreports_badge>0</labreports_badge>";
        $xml_string .= "<prescription_badge>0</prescription_badge>";
        $xml_string .= "<total_badge>0</total_badge>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</notifications>";
echo $xml_string;
?>