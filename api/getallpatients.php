<?php

/**
 * api/getallpatients.php retrieve all patients.
 *
 * API returns all patients with the profile image url.
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

$token = $_POST['token'];

$xml_string = "";
$xml_string .= "<PatientList>\n";

if ($userId = validateToken($token)) {

    $username = getUsername($userId);

    $acl_allow = acl_check('patients', 'demo', $username);
    if ($acl_allow) {

        $strQuery = "SELECT id, pid, fname as firstname, lname as lastname,mname as middlename, phone_contact as phone, dob, sex as gender FROM patient_data";
        $result = sqlStatement($strQuery);
        $numRows = sqlNumRows($result);
        if ($numRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Patient list Record has been fetched</reason>";
            $counter = 0;

            $cat_title = 'Patient Photograph';
            $strQuery2 = "SELECT id from `categories` WHERE name LIKE '" . add_escape_custom($cat_title) . "'";
            $result3 = sqlQuery($strQuery2);
            $cat_id = $result3['id'];

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<Patient>\n";

                $p_id = 0;
                foreach ($res as $fieldname => $fieldvalue) {
                    $rowvalue = xmlsafestring($fieldvalue);
                    if ($fieldname == "pid")
                        $p_id = $fieldvalue;
                    $xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
                }


                $strQuery1 = "SELECT d.date,d.size,d.url,d.docdate,d.mimetype,c2d.category_id
                                FROM `documents` AS d
                                INNER JOIN `categories_to_documents` AS c2d ON d.id = c2d.document_id
                                WHERE foreign_id = ?
                                AND category_id = ?
                                ORDER BY category_id, d.date DESC 
                                LIMIT 1";

                $result1 = sqlQuery($strQuery1, array($p_id, $cat_id));

                if ($result1) {
                    $xml_string .= "<profileimage>" . getUrl($result1['url']) . "</profileimage>\n";
                } else {
                    $xml_string .= "<profileimage></profileimage>\n";
                }

                $xml_string .= "</Patient>\n";
                $counter++;
            }
        } else {
            $xml_string .= "<status>-1</status>\n";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}


$xml_string .= "</PatientList>\n";
echo $xml_string;
?>