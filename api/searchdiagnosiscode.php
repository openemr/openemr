<?php
/**
 * api/searchdiagnosiscode.php Search diagnosis code.
 *
 * API is allowed to search diagnois code.
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
$xml_string = "<DiagnosisCodes>";

$token = $_POST['token'];
$search_term = $_POST['search_term'];
$code_type = isset($_POST['code_type']) ? $_POST['code_type'] : '2';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {

        if (!empty($search_term)) {

            $strQuery = "SELECT code_text,code_text_short,code,code_type 
                                    FROM  `codes` 
                                    WHERE `code_type` = ?  AND `code_text` LIKE ? ";
            $result = sqlStatement($strQuery, array($code_type, "%" . $search_term . "%"));
        } else {

            $strQuery = "SELECT code_text,code_text_short,code,code_type 
                                    FROM  `codes` 
                                    WHERE `code_type` = ? LIMIT 1000";
            $result = sqlStatement($strQuery, array($code_type));
        }

        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Facilities Processed successfully</reason>";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<DiagnosisCode>\n";

                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }

                $xml_string .= "</DiagnosisCode>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could find results</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</DiagnosisCodes>";
echo $xml_string;
?>