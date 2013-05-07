<?php
/**
 * api/updatevisitvitals.php Update vitals against visit.
 *
 * API is allowed to update vitals for patient visit.
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
$patientId = $_POST['patientId'];
$vital_id = $_POST['vital_id'];

$date = date('Y-m-d H:i:s');
$groupname = $_POST['groupname'];
$authorized = $_POST['authorized'];
$activity = $_POST['activity'];
$bps = $_POST['bps'];
$bpd = $_POST['bpd'];
$weight = $_POST['weight'];
$height = $_POST['height'];
$temperature = $_POST['temperature'];
$temp_method = $_POST['temp_method'];
$pulse = $_POST['pulse'];
$respiration = $_POST['respiration'];
$note = $_POST['note'];
$BMI = $_POST['BMI'];
$BMI_status = $_POST['BMI_status'];
$waist_circ = $_POST['waist_circ'];
$head_circ = $_POST['head_circ'];
$oxygen_saturation = $_POST['oxygen_saturation'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);

    $acl_allow = acl_check('encounters', 'auth_a', $user);

    if ($acl_allow) {
        $strQuery = "UPDATE `form_vitals` SET 
                                        `date`='" . add_escape_custom($date) . "',
                                        `pid`='" . add_escape_custom($patientId) . "',
                                        `user`='" . add_escape_custom($user) . "',
                                        `groupname`='" . add_escape_custom($groupname) . "',
                                        `authorized`='" . add_escape_custom($authorized) . "',
                                        `activity`='" . add_escape_custom($activity) . "',
                                        `bps`='" . add_escape_custom($bps) . "',
                                        `bpd`='" . add_escape_custom($bpd) . "',
                                        `weight`='" . add_escape_custom($weight) . "',
                                        `height`='" . add_escape_custom($height) . "',
                                        `temperature`='" . add_escape_custom($temperature) . "',
                                        `temp_method`='" . add_escape_custom($temp_method) . "',
                                        `pulse`='" . add_escape_custom($pulse) . "',
                                        `respiration`='" . add_escape_custom($respiration) . "',
                                        `note`='" . add_escape_custom($note) . "',
                                        `BMI`='" . add_escape_custom($BMI) . "',
                                        `BMI_status`='" . add_escape_custom($BMI_status) . "',
                                        `waist_circ`='" . add_escape_custom($waist_circ) . "',
                                        `head_circ`='" . add_escape_custom($head_circ) . "',
                                        `oxygen_saturation`='" . add_escape_custom($oxygen_saturation) . "' 
                                         WHERE id = ?";

        $result = sqlStatement($strQuery, array($vital_id));

        if ($result !== FALSE) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'Visit vital update successfully';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'Could not update isit vital';
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'visitvitals');
echo $xml;
?>