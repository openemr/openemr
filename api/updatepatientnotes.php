<?php
/**
 * api/updatepatientnotes.php Update patient notes.
 *
 * API is allowed to update patient notes. 
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
$noteId = $_POST['noteId'];
$patientId = $_POST['patientId'];
$notes = $_POST['notes'];
$title = $_POST['title'];

if ($userId = validateToken($token)) {
    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    $_SESSION['authUser'] = $username;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {
        $strQuery = "UPDATE pnotes SET date = '" . date('Y-m-d H:i:s') . "', body = '" . add_escape_custom($notes) . "', user = '" . add_escape_custom($username) . "', title = '" . add_escape_custom($title) . "', assigned_to = '" . add_escape_custom($username) . "' WHERE id = ?";
        $result = sqlStatement($strQuery, array($noteId));

        if ($result !== FALSE) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Patient notes has been updated';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}
$xml = ArrayToXML::toXml($xml_array, 'PatientNotes');
echo $xml;
?>