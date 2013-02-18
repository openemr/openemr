<?php
/**
 * api/vitals.php List of patient vitals.
 *
 * API is allowed to get patient vitals list with details.
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
$xml_string .= "<PatientVitals>\n";

$token = $_POST['token'];
$visit_id = $_POST['visit_id'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {
        $strQuery = "SELECT fv.* 
                                FROM  `forms` AS f
                                INNER JOIN  `form_vitals` AS fv ON f.form_id = fv.id
                                WHERE  `encounter` = ?
                                AND  `form_name` =  'Vitals'
                                ORDER BY f.id DESC";
        $result = sqlStatement($strQuery,array($visit_id));

        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>Success processing patient vitals records</reason>\n";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<Vital>\n";

                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                
                
                $user_query = "SELECT  `fname` ,`lname` 
                                                FROM  `users` 
                                                WHERE username LIKE  ?";
                
                $user_result = sqlQuery($user_query,array($res['user']));
                
                $xml_string .= "<firstname>".$user_result['fname']."</firstname>\n";
                $xml_string .= "<lastname>".$user_result['lname']."</lastname>\n";
                $xml_string .= "</Vital>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>\n";
            $xml_string .= "<reason>Cound not find results</reason>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}
$xml_string .= "</PatientVitals>\n";
echo $xml_string;
?>