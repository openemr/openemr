<?php
/**
 * api/resetpassword.php Reset user password.
 *
 * API is allowed to reset user password and send informations by email.
 *
 * Copyright (coffee) 2012 Karl Englund <karl@mastermobileproducts.com>
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
 * 
 */
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');

$token = $_POST['token'];
$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : '';
$pin = isset($_POST['pin']) && !empty($_POST['pin']) ? $_POST['pin'] : '';


$xml_string = "<reset>";

if ($userId = validateToken($token)) {
    if (empty($password) && empty($pin)) {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>Please provide password/pin values.</reason>";
    } else {
    
        
        $query1 = "UPDATE `users` SET ";

     
        if (!empty($password)) {
            $new_password = sha1($password);
            $query1 .= "`password`='".add_escape_custom($new_password)."' ";

        }
        if (!empty($pin)) {
            $new_pin = sha1($pin);
            if (!empty($password)) {
                $query1 .= ",";
            }
            $query1 .= "`upin`='".add_escape_custom($new_pin)."' ";
        }
        $query1 .= "WHERE id = ".add_escape_custom($userId);

      
        $result1 = sqlStatement($query1);
        
        if ($result1) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Successfully reset Password/Pin</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}
$xml_string .= "</reset>";
echo $xml_string;
?>
