<?php
/**
 * api/getlistbyvisitid.php retrieve all list element by visit id.
 *
 * API returns all list items related to any particular visit id.
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
require_once("$srcdir/lists.inc");

$xml_string = "";
$xml_string .= "<Listitems>\n";

$token = $_POST['token'];
$pid = $_POST['patientId'];
$visit_id = isset($_POST['visit_id']) && !empty($_POST['visit_id']) ? $_POST['visit_id'] : '';

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);
    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('encounters', 'auth_a', $username);
    if ($acl_allow) {
        if ($visit_id) {
            $strQuery = "select l.* from lists as l
                        LEFT JOIN `issue_encounter` as i ON i.list_id = l.id
                        where l.pid= ? AND i.encounter = ?  order by l.date DESC";

            $result = sqlStatement($strQuery, array($pid, $visit_id));

            if ($result->_numOfRows > 0) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>The Patient Record has been fetched</reason>\n";
                $count = 0;
                while ($res = sqlFetchArray($result)) {
                    $xml_string .= "<listitem>\n";

                    foreach ($res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }

                    $diagnosis_title = getDrugTitle($res['diagnosis'], $db);
                    $xml_string .= "<diagnosistitle>{$diagnosis_title}</diagnosistitle>";
                    $xml_string .= "</listitem>\n";
                }
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
            }
        } else {


            $strQuery = "select l.* from lists as l
                        LEFT JOIN `issue_encounter` as i ON i.list_id = l.id
                        where l.pid=?  order by l.date DESC";

            $result = sqlStatement($strQuery, array($pid));

            if ($result->_numOfRows > 0) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>The Patient Record has been fetched</reason>\n";
                $count = 0;
                while ($res = sqlFetchArray($result)) {
                    $xml_string .= "<listitem>\n";

                    foreach ($res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }

                    $diagnosis_title = getDrugTitle($res['diagnosis'], $db);
                    $xml_string .= "<diagnosistitle>{$diagnosis_title}</diagnosistitle>";
                    $xml_string .= "</listitem>\n";
                }
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
            }
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}
$xml_string .= "</Listitems>\n";
echo $xml_string;
?>