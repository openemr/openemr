<?php
/**
 * api/getspeechdictation.php Get Speech Dictation.
 *
 * API is allowed to get patient Speech Dictation for a visit/encounter 
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
$xml_string = "<speechdictations>";


$token = $_POST['token'];
$visit_id = !empty($_POST['visit_id']) ? $_POST['visit_id'] : -1;

if ($userId = validateToken($token)) {
    
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    
    if ($acl_allow) {
        $strQuery = "SELECT fdictation.id, fdictation.date, dictation, additional_notes, fdictation.user
				FROM `forms` AS f
				INNER JOIN `form_dictation` AS fdictation ON f.form_id = fdictation.id
				WHERE `encounter` = ?
				AND `form_name` = 'Speech Dictation'
                                ORDER BY id DESC";
        $result = sqlStatement($strQuery, array($visit_id));

        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Speech Dictations Record has been fetched</reason>";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<speechdictation>\n";
      
                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $userName = $res['user'];
                $user_query = "SELECT  `fname` ,  `lname` 
                                                    FROM  `users` 
                                                    WHERE username LIKE ? ";
                $user_result = sqlQuery($user_query, array($userName));
                $xml_string .= "<firstname>{$user_result['fname']}</firstname>\n";
                $xml_string .= "<lastname>{$user_result['lname']}</lastname>\n";
                $xml_string .= "</speechdictation>\n";
            }
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

$xml_string .= "</speechdictations>";
echo $xml_string;
?>