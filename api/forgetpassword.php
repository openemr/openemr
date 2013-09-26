<?php
/**
 * api/forgetpassword.php to Retrieve user password.
 *
 * API send an email to user which containing his username, password and pin.
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
$xml_string = "<forgetpassword>";

$email = $_POST['email'];


$strQuery = "SELECT id,username, password, fname, lname FROM users WHERE email= ?";
$result = sqlQuery($strQuery,array($email));

if ($result) {
    $xml_string .= "<status>0</status>";
    
    $newPwd = rand_string(10);    
    $pin = substr(uniqid(rand()), 0, 4);
    $pin1 = sha1($pin);
        
    if (getVersion()) {
        require_once("$srcdir/authentication/password_hashing.php");        
        
        $salt = password_salt();
        $password = password_hash($newPwd,$salt);
        $result1 = sqlStatement("UPDATE users_secure SET password='".$password."', salt='".$salt."' WHERE id = {$result["id"]}");
        
        $strQuery1 = "UPDATE `users` SET `app_pin`='" . add_escape_custom($pin1) . "' WHERE email = ?";
        $result1 = sqlStatement($strQuery1,array($email));
    } else {        
        $password1 = sha1($newPwd);
        
        $strQuery1 = "UPDATE `users` SET `password`='" . add_escape_custom($password1) . "', `app_pin`='" . add_escape_custom($pin1) . "' WHERE email = ?";
        $result1 = sqlStatement($strQuery1,array($email));
    }
    
    if ($result1 !== FALSE) {
        
        $mail = new PHPMailer();
        $mail->IsSendmail();
        $body = "<html><body>
						<table>
							<tr>
								<td>Your Password has been changed your new Username and Password are</td>
							</tr>
							<tr>
								<td>Here are the details of your account: </td>
							</tr>
							<tr>
								<td>Username: " . $result['username'] . "</td>
							</tr>
							<tr>
								<td>Password: " . $newPwd . "</td>
							</tr>
							<tr>
								<td>Pin: " . $pin . "</td>
							</tr>
							<tr>
								<td>Thanks, <br />MedMaster Team</td>
							</tr>
						</table>
					</body></html>";
        $body = eregi_replace("[\]", '', $body);
        $mail->AddReplyTo("no-reply@mastermobileproducts.com", "MedMasterPro");
        $mail->SetFrom('no-reply@mastermobileproducts.com', 'MedMasterPro');
        $mail->AddAddress($email, $email);
        $mail->Subject = "Forgot Password Request at MedMaster";
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->MsgHTML($body);

        if (!$mail->Send()) {
            $xml_string .= "<error>" . $mail->ErrorInfo . "</error>";
        } else {
            $xml_string .= "<reason>Email containing your username and password has been sent to your email address!</reason>";
        }
    }
} else {
    $xml_string .= "<status>-1</status>";
    $xml_string .= "<reason>Email Address not found in our records. Please contact support.</reason>";
}


$xml_string .= "</forgetpassword>";
echo $xml_string;
?>