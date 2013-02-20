<?php
/**
 * api/updatevisit.php Update Patient visit.
 *
 * API is allowed to update patient visit details.
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
$xml_string .= "<PatientVisit>";

$token = $_POST['token'];

$patientId = $_POST['patientId'];
$reason = $_POST['reason'];
$facility = $_POST['facility'];
$facility_id = $_POST['facility_id'];
$encounter = $_POST['encounter'];
$dateService = $_POST['dateService'];
$sensitivity = $_POST['sensitivity'];
$pc_catid = $_POST['pc_catid'];
$billing_facility = $_POST['billing_facility'];
$list = $_POST['list'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {
        $strQuery = "UPDATE form_encounter 
                    SET date = '" . date('Y-m-d H:i:s') . "', 
                        reason = '" . add_escape_custom($reason) . "', 
                        facility = '" . add_escape_custom($facility) . "', 
                        facility_id = " . add_escape_custom($facility_id) . ", 
                        onset_date = '" . add_escape_custom($dateService) . "', 
                        sensitivity = '" . add_escape_custom($sensitivity) . "', 
                        billing_facility  = " . add_escape_custom($billing_facility) . ",
                        pc_catid = '" . add_escape_custom($pc_catid) . "'    
                    WHERE pid = ? " . " AND encounter = ?";
        $result = sqlStatement($strQuery, array($patientId, $encounter));

        $list_res = 1;
        if (!empty($list)) {

            $del_list_query = "DELETE FROM `issue_encounter` WHERE `pid` = ? AND `encounter` = ?";
            $list_res = sqlStatement($del_list_query, array($patientId, $encounter));
            $list_array = explode(',', $list);


            foreach ($list_array as $list_item) {
                $sql_list_query = "INSERT INTO `issue_encounter`(`pid`, `list_id`, `encounter`, `resolved`) 
                            VALUES (".add_escape_custom($patientId).",".add_escape_custom($list_item).",".add_escape_custom($encounter).",0)";
                $result1 = sqlStatement($sql_list_query);
                if (!$list_res)
                    $list_res = 0;
            }
        }
        if ($result !== FALSE || $list_res !== FALSE) {

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Patient visit updated successfully</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Couldn't update record</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</PatientVisit>";
echo $xml_string;
?>
