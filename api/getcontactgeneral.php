<?php

/**
 * api/getcontactgeneral.php retrieve user all contacts.
 *
 * API retrieve user all contacts.
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
$xml_string = "<contacts>";

$token = $_POST['token'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

    if ($acl_allow) {

        $strQuery = "SELECT id, username,
                                password , authorized, info, source, u.title, fname, lname, mname, upin, see_auth, active, npi, taxonomy, specialty, organization, valedictory, assistant, email, url, street, streetb, city, state, zip, phone, phonew1, phonew2, phonecell, fax, u.notes, contact_image
                                FROM users AS u
                                WHERE username = ''
                                AND password = ''
                                AND active = 1
                                ";


        $result = sqlStatement($strQuery);
        $numRows = sqlNumRows($result);
        if ($numRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Contact Record has been fetched</reason>";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<contact>\n";

                foreach ($res as $fieldName => $fieldValue) {
                    if ($fieldName == 'contact_image' && !empty($fieldValue)) {
                        $xml_string .="<image_url>{$sitesUrl}{$site}/documents/userdata/contactimages/{$fieldValue}</image_url>";
                        $xml_string .="<image_title>{$image_data['title']}</image_title>";
                    } else {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
                $xml_string .= "</contact>\n";
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

$xml_string .= "</contacts>";
echo $xml_string;
?>