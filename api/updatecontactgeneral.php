<?php
/**
 * api/updatecontactgeneral.php Update contact.
 *
 * API is allowed to update general contact details.
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
$xml_string = "<contact>";

$token = $_POST['token'];
$id = $_POST['id'];
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
$image_title_old = $_POST['imageTitleOld'];
$image_title_new = $_POST['imageTitleNew'];


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;

    if ($acl_allow) {


        if ($firstname == '' || $lastname == '' || $email == '') {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Some fields are empty</reason>";
        } else {

            $strQuery = 'UPDATE users SET ';
            $strQuery .= ' info = "' . add_escape_custom($info) . '",';
            $strQuery .= ' source = "' . add_escape_custom($source) . '",';
            $strQuery .= ' title = "' . add_escape_custom($title) . '",';
            $strQuery .= ' fname = "' . add_escape_custom($firstname) . '",';
            $strQuery .= ' lname = "' . add_escape_custom($lastname) . '",';
            $strQuery .= ' mname = "' . add_escape_custom($middlename) . '",';
            $strQuery .= ' upin = "' . add_escape_custom($upin) . '",';
            $strQuery .= ' see_auth = "' . add_escape_custom($see_auth) . '",';
            $strQuery .= ' npi = "' . add_escape_custom($npi) . '",';
            $strQuery .= ' taxonomy = "' . add_escape_custom($taxonomy) . '",';
            $strQuery .= ' specialty = "' . add_escape_custom($specialty) . '",';
            $strQuery .= ' organization = "' . add_escape_custom($organization) . '",';
            $strQuery .= ' valedictory = "' . add_escape_custom($valedictory) . '",';
            $strQuery .= ' assistant = "' . add_escape_custom($assistant) . '",';
            $strQuery .= ' email = "' . add_escape_custom($email) . '",';
            $strQuery .= ' url = "' . add_escape_custom($url) . '",';
            $strQuery .= ' street = "' . add_escape_custom($street) . '",';
            $strQuery .= ' streetb = "' . add_escape_custom($streetb) . '",';
            $strQuery .= ' city = "' . add_escape_custom($city) . '",';
            $strQuery .= ' state = "' . add_escape_custom($state) . '",';
            $strQuery .= ' zip = "' . add_escape_custom($zip) . '",';
            $strQuery .= ' phone = "' . add_escape_custom($home_phone) . '",';
            $strQuery .= ' phonew1 = "' . add_escape_custom($work_phone1) . '",';
            $strQuery .= ' phonew2 = "' . add_escape_custom($work_phone2) . '",';
            $strQuery .= ' phonecell = "' . add_escape_custom($mobile) . '",';
            $strQuery .= ' fax = "' . add_escape_custom($fax) . '",';
            $strQuery .= ' notes = "' . add_escape_custom($notes) . '"';
            $strQuery .= ' WHERE username = \'\' AND password = \'\' AND id = ?';



            $result = sqlStatement($strQuery, array($id));


            if ($image_data) {

                $imageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
                if ($_SERVER["SERVER_PORT"] != "80") {
                    $imageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
                } else {
                    $imageURL .= $_SERVER["SERVER_NAME"];
                }


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

                $strQuery2 = "SELECT * FROM `list_options` 
                            WHERE `list_id` = 'ExternalResources' AND 
                                   `option_id` = ?";

                $result2 = sqlQuery($strQuery2, array($image_title_old));

                if ($result2) {
                    $old_image_path = $result2['notes'];
                    $old_image_name = basename($old_image_path);

                    if (file_exists($path . "/contactimages/" . $old_image_name)) {
                        unlink($path . "/contactimages/" . $old_image_name);
                    }


                    $strQuery1 = "UPDATE `list_options` SET `notes`='" . add_escape_custom($notes_url) . "',
                                                        `option_id` = '" . add_escape_custom($image_title_new) . "',
                                                        `title` = '" . add_escape_custom($image_title_new) . "'
                                                 WHERE `list_id` = 'ExternalResources' AND 
                                                    `option_id` = '" . add_escape_custom($image_title_old) . "'";
                } else {

                    $strQuery1 = "INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) 
                        VALUES ('ExternalResources','" . add_escape_custom($image_title_new)."','" . add_escape_custom($image_title_new)."','0','0','" . add_escape_custom($id)."','','" . add_escape_custom($notes_url)."')";
                }

                $result1 = sqlStatement($strQuery1);
            }

            if ($result !== FALSE) {
                $xml_string .= "<status>0</status>";
                $xml_string .= "<reason>The Contact has been updated</reason>";
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