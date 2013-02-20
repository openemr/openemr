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
//ini_set('display_errors', 'On');
require_once 'classes.php';

$xml_string = "";
$xml_string = "<DiagnosisCodes>";

//$token = $_POST['token'];
//$search_term = $_POST['search_term'];
//$code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'icd9';

$token = '722192de79af528200a232e958b64f49';
$search_term = 'Paratyphoid';
//$code_type =  'icd9';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {

        if (!empty($search_term)) {
            switch ($code_type) {
                case 'rxnorm':
                    $strQuery = "SELECT `RXAUI` AS `code` , `AUI` AS `code_text_short` , `STR` AS `code_text` , `CODE` AS `code_type`
                                FROM `RXNATOMARCHIVE`
                                WHERE `STR` LIKE ? LIMIT 1000";
                    $result = sqlStatement($strQuery, array("%" . $search_term . "%"));
                    break;
                case 'snomed':
                    $strQuery = "SELECT `ConceptId` AS `code` , `FullySpecifiedName` AS `code_text` , `SNOMEDID` AS `code_text_short` , `CTV3ID` AS `code_type`
                                FROM `sct_concepts`
                                WHERE `FullySpecifiedName` LIKE ? LIMIT 1000";
                    $result = sqlStatement($strQuery, array("%" . $search_term . "%"));
                    break;
                case 'icd9':
                    $strQuery = "SELECT code_text,code_text_short,code,code_type 
                                    FROM  `codes` 
                                    WHERE `code_type` = 2  AND `code_text` LIKE ? LIMIT 1000";
                    $result = sqlStatement($strQuery, array( "%" . $search_term . "%"));
            }
        } else {

            $strQuery = "SELECT code_text,code_text_short,code,code_type 
                                    FROM  `codes` 
                                    WHERE `code_type` = 2 LIMIT 1000";
            $result = sqlStatement($strQuery);
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