<?php
/**
 * api/addvisit.php add patient vitals.
 *
 * Api add's patient visit.
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
$dateService = $_POST['dateService'];
$onset_date =$_POST['onset_date'];
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

        $conn = $GLOBALS['adodb']['db'];
        $encounter = $conn->GenID("sequences");


        sqlStatement("lock tables form_encounter read");

        $result_encounter_id = sqlQuery("select max(encounter)+1 as encounter_id from form_encounter");

        sqlStatement("unlock tables");

        if ($result_encounter_id['encounter_id'] > 1) {
            $encounter_id = $result_encounter_id['encounter_id'];
        } elseif (empty($result_encounter_id['encounter_id'])) {
            $encounter_id = 1;
        }
        $strQuery = "INSERT INTO form_encounter (date, reason, facility, facility_id, pid, encounter, onset_date, sensitivity, pc_catid, billing_facility) 
        VALUES ('".add_escape_custom( $dateService )."', '".add_escape_custom( $reason )."', '".add_escape_custom( $facility )."', ".add_escape_custom( $facility_id ).", ".add_escape_custom( $patientId ).", ".add_escape_custom( $encounter ).", '".add_escape_custom( $onset_date )."', '".add_escape_custom( $sensitivity )."', ".add_escape_custom( $pc_catid ).", ".add_escape_custom( $billing_facility ).")";
        $result = sqlStatement($strQuery);

        if ($result) {
            if (!empty($list)) {
                $list_array = explode(',', $list);
                foreach ($list_array as $list_item) {
                    $sql_list_query = "INSERT INTO `issue_encounter`(`pid`, `list_id`, `encounter`, `resolved`) 
                            VALUES ('".add_escape_custom($patientId)."','".add_escape_custom($list_item)."','".add_escape_custom($encounter_id)."',0)";
                    sqlStatement($sql_list_query);
                }
            }
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Patient visit has been added</reason>";
            $xml_string .= "<visit_id>{$encounter_id}</visit_id>";
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

$xml_string .= "</PatientVisit>";
echo $xml_string;
?>