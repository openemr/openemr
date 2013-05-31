<?php
/**
 * api/register.php Register user.
 *
 * API is allowed to register new user.
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
$xml_array = array();

$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$greetings = isset($_POST['greetings']) ? $_POST['greetings'] : "";
$title = !empty($_POST['title']) ? $_POST['title'] : 'Doctor';
$device_token = isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '';

$pin = !empty($_POST['pin']) ? $_POST['pin'] : substr(uniqid(rand()), 0, 4);


$createDate = date('Y-m-d');


sqlStatement("lock tables gacl_aro read");
$result5 = sqlQuery("select max(id)+1 as id from gacl_aro");
$gacl_aro_id = $result5['id'];
sqlStatement("unlock tables");
$secretKey = getUniqueSecretkey();



$strQueryUsers = "SELECT * FROM users WHERE username LIKE '{$username}'";
$resultUsers = sqlQuery($strQueryUsers);

if ($result || $resultUsers) {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Username is not available';
} else {

    $ip = $_SERVER['REMOTE_ADDR'];

    $url = "http://api.ipinfodb.com/v3/ip-city/?key=53e1dbadb9c701a660a8914aeacca2bd640b56758659f3b1940de385fa97ca94&ip={$ip}&format=json";
    $responce = file_get_contents($url);
    $responce_array = json_decode($responce);


    $password1 = sha1($password);
    $pin1 = sha1($pin);

    $strQuery1 = "INSERT INTO `users`(`username`, `password`, `fname`, `lname`,  `phone`, `email`, `authorized`,`calendar`, `app_pin`, `create_date`, `secret_key`,  `title`, `ip_address`, `country_code`, `country_name`, `state`, `city`, `zip`, `latidute`, `longitude`, `time_zone`)
                            VALUES ('".add_escape_custom($username)."','".add_escape_custom($password1)."','".add_escape_custom($firstname)."','".add_escape_custom($lastname)."','".add_escape_custom($phone)."','".add_escape_custom($email)."',1,1, '" . add_escape_custom($pin1) . "', '" . $createDate . "','" . $secretKey . "','".add_escape_custom($title)."','".add_escape_custom($responce_array->ipAddress)."','".add_escape_custom($responce_array->countryCode)."','".add_escape_custom($responce_array->countryName)."','".add_escape_custom($responce_array->regionName)."','".add_escape_custom($responce_array->cityName)."','".add_escape_custom($responce_array->zipCode)."','".add_escape_custom($responce_array->latitude)."','".add_escape_custom($responce_array->longitude)."','".add_escape_custom($responce_array->timeZone)."')";
   
    $result1 = sqlInsert($strQuery1);


    $last_user_id = $result1;


    $strQuery2 = "INSERT INTO `gacl_aro`(`id`, `section_value`, `value`, `order_value`, `name`) 
                    VALUES ('{$gacl_aro_id}', 'users', '".add_escape_custom($username)."', '10','" . add_escape_custom($firstname . " " . $lastname) . "')";


    $result2 = sqlInsert($strQuery2);


    $strQuery3 = "INSERT INTO `groups`(`name`, `user`) 
                        VALUES ('Default', '" . add_escape_custom($username) . "')";
    $result3 = sqlInsert($strQuery3);


    $strQuery4 = "INSERT INTO `gacl_groups_aro_map`(`group_id`, `aro_id`) 
                    VALUES('11', '" . add_escape_custom($gacl_aro_id) . "')";
    $result4 = sqlInsert($strQuery4);


    $token = createToken($last_user_id, true, $device_token);

    
    if ($result1 && $result2 && $result3 && $result4 && $token) {
        $mail = new PHPMailer();
        $mail->IsSendmail();
        $body = "<html><body>
                            <table>
                                    <tr>
                                            <td>You have signed up for a MedMaster account</td>
                                    </tr>
                                    <tr>
                                            <td>Here are the details of your account: </td>
                                    </tr>
                                    <tr>
                                            <td>Username: " . $username . "</td>
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
        $mail->Subject = "MedMaster Account Signup";
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->MsgHTML($body);

        if (!$mail->Send()) {
            $xml_array['email'] = $mail->ErrorInfo;
        } else {
            $xml_array['email'] = "Email send successfully";
        }

        $xml_array['status'] = 0;
        $xml_array['token'] = $token;
        $xml_array['provider_id'] = $last_user_id;
        $xml_array['firstname'] = $firstname;
        $xml_array['lastname'] = $lastname;
        $xml_array['reason'] = 'User registered successfully';
    } else {
        $xml_array['status'] = -1;
        $xml_array['reason'] = 'Could not register user';
    }
}

$xml = ArrayToXML::toXml($xml_array, 'MedMasterUser');
echo $xml;
?>