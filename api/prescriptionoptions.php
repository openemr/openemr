<?php

/**
 * api/getstatsoptions.php Get stats options.
 *
 * API is allowed to get stats option(language, race and ethnicity).
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
$xml_string = "<list>";

$token = $_POST['token'];
//$token = 'cdc612074a78a49f857b915450de4673';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('lists', 'default', $user);
    if ($acl_allow) {

        $strQuery = "SELECT option_id, title FROM list_options WHERE list_id  = 'drug_units'";
        $result = sqlStatement($strQuery);
        $numRows = sqlNumRows($result);

        $strQuery1 = "SELECT option_id, title FROM list_options WHERE list_id  = 'drug_form'";
        $result1 = sqlStatement($strQuery1);
        $numRows1 = sqlNumRows($result1);

        $strQuery2 = "SELECT option_id, title FROM list_options WHERE list_id  = 'drug_route'";
        $result2 = sqlStatement($strQuery2);
        $numRows2 = sqlNumRows($result2);

        $strQuery3 = "SELECT option_id, title FROM list_options WHERE list_id  = 'drug_interval'";
        $result3 = sqlStatement($strQuery3);
        $numRows3 = sqlNumRows($result3);


        if ($numRows > 0 || $numRows1 > 0 || $numRows2 > 0 || $numRows3 > 0) {

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Options has been fetched</reason>";

            $xml_string .= "<drug_units>\n";
            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<item>";
                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);

                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</item>";
            }
            $xml_string .= "</drug_units>";

            $xml_string .= "<drug_form>\n";
            while ($res1 = sqlFetchArray($result1)) {
                $xml_string .= "<item>";
                foreach ($res1 as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);

                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</item>";
            }
            $xml_string .= "</drug_form>";

            $xml_string .= "<drug_route>\n";
            while ($res2 = sqlFetchArray($result2)) {
                $xml_string .= "<item>";
                foreach ($res2 as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);

                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</item>";
            }
            $xml_string .= "</drug_route>";


            $xml_string .= "<drug_interval>\n";
            while ($res3 = sqlFetchArray($result3)) {
                $xml_string .= "<item>";
                foreach ($res3 as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</item>";
            }
            $xml_string .= "</drug_interval>";
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

$xml_string .= "</list>";
echo $xml_string;
?>
