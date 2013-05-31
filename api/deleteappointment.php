<?php
/**
 * api/deleteappointment.php Cancel appointment.
 *
 * Api allows to cancel appointment for a patient.
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
$xml_string = "<appointment>";

$token = $_POST['token'];
$id = $_POST['id'];

if ($userId = validateToken($token)) {

    $username = getUsername($userId);
    
    $acl_allow = acl_check('admin', 'super', $username);
    if ($acl_allow) {
        $strQuery = "DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?";
	$result = sqlStatement($strQuery, array($id));

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Appointment has been deleted/removed.</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your request. Please re-submit the information again.</reason>";
        }

    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</appointment>";
echo $xml_string;
?>