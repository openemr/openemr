<?php

/**
 * api/addcontactgeneral.php Add new contact for user.
 *
 * Api add's new contacts for user.
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
require_once ("classes.php");

$xml_string = "";
$xml_string = "<contact>";

$token = $_POST['token'];
$title = $_POST['title'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$middlename = $_POST['middlename'];
$upin = $_POST['upin'];
$npi = $_POST['npi'];
$taxonomy = $_POST['taxonomy'];
$specialty = $_POST['specialty'];
$organization = $_POST['organization'];
$valedictory = $_POST['valedictory'];
$assistant = $_POST['assistant'];
$email = $_POST['email'];
$url = $_POST['url'];
$street = $_POST['street'];
$streetb = $_POST['streetb'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$home_phone = $_POST['home_phone'];
$work_phone1 = $_POST['work_phone1'];
$work_phone2 = $_POST['work_phone2'];
$mobile = $_POST['mobile'];
$fax = $_POST['fax'];
$notes = $_POST['notes'];
$image_data = $_POST['imageData'];
$image_title = $_POST['imageTitle'];

if ($userId = validateToken($token)) {
    
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

    if ($acl_allow) {

        if ($firstname == '' || $lastname == '' || $email == '') {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Some fields are empty</reason>";
        } else {

            $strQuery = "INSERT INTO users (username, password, authorized, info, source, title, fname, lname, mname,  upin, see_auth, active, npi, taxonomy, specialty, organization, valedictory, assistant, email, url, street, streetb, city, state, zip, phone, phonew1, phonew2, phonecell, fax, notes ) 
                            VALUES ('',
                                    '',
                                    0,
                                    '',
                                    NULL,
                                    '" . add_escape_custom($title) . "',
                                    '" . add_escape_custom($firstname) . "',
                                    '" . add_escape_custom($lastname) . "',
                                    '" . add_escape_custom($middlename) . "',
                                    '" . add_escape_custom($upin) . "',
                                    0,
                                    1,
                                    '" . add_escape_custom($npi) . "',
                                    '" . add_escape_custom($taxonomy) . "',
                                    '" . add_escape_custom($specialty) . "',
                                    '" . add_escape_custom($organization) . "',
                                    '" . add_escape_custom($valedictory) . "',
                                    '" . add_escape_custom($assistant) . "',
                                    '" . add_escape_custom($email) . "',
                                    '" . add_escape_custom($url) . "',
                                    '" . add_escape_custom($street) . "',
                                    '" . add_escape_custom($streetb) . "',
                                    '" . add_escape_custom($city) . "',
                                    '" . add_escape_custom($state) . "',
                                    '" . add_escape_custom($zip) . "',
                                    '" . add_escape_custom($home_phone) . "',
                                    '" . add_escape_custom($work_phone1) . "',
                                    '" . add_escape_custom($work_phone2) . "',
                                    '" . add_escape_custom($mobile) . "',
                                    '" . add_escape_custom($fax) . "',
                                    '" . add_escape_custom($notes) . "'
                                    )";
            $result = sqlInsert($strQuery);

            $last_inserted_id = $result;

            if ($image_data) {

               
                $path = $sitesDir . "{$site}/documents/userdata";

                if (!file_exists($path)) {
                    mkdir($path);
                    mkdir($path . "/contactimages");
                } elseif (!file_exists($path . "/contactimages")) {
                    mkdir($path . "/contactimages");
                }

                $image_name = date('Y-m-d_H-i-s') . ".png";
                file_put_contents($path . "/contactimages/" . $image_name, base64_decode($image_data));

                $notes_url = $sitesUrl . "{$site}/documents/userdata/contactimages/" . $image_name;

                $strQuery1 = "INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) 
                                VALUES ('ExternalResources',
                                        '" . add_escape_custom($image_title) . "',
                                        '" . add_escape_custom($image_title) . "',
                                        '0',
                                        '0',
                                        '" . ($last_inserted_id) . "',
                                        '',
                                        '" . add_escape_custom($notes_url) . "')";


                $result1 = sqlStatement($strQuery1);
            }


            if ($result) {

                $xml_string .= "<status>0</status>";
                $xml_string .= "<reason>The Contact has been added</reason>";
            } else {
                $xml_string .= "<status>-1</status>";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
            }
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</contact>";
echo $xml_string;
?>