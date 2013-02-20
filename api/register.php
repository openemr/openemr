<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';
ini_set('display_errors', '1');
$xml_array = array();

//$firstname = add_escape_custom($_POST['firstname']);
//$lastname = add_escape_custom($_POST['lastname']);
//$phone = add_escape_custom($_POST['phone']);
//$email = add_escape_custom($_POST['email']);
//$username = add_escape_custom($_POST['username']);
//$password = add_escape_custom($_POST['password']);
//$greetings = isset($_POST['greetings']) ? add_escape_custom($_POST['greetings']) : "";
//$title = !empty($_POST['title']) ? add_escape_custom($_POST['title']) : 'Doctor';
//$device_token = isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '';
//
//$pin = !empty($_POST['pin']) ? add_escape_custom($_POST['pin']) : substr(uniqid(rand()), 0, 4);



$firstname = add_escape_custom('Hassan');
$lastname = add_escape_custom('Baiga');
$phone = add_escape_custom('11224455');
$email = add_escape_custom('hassan@gmail.com');
$username = add_escape_custom('hassanBa');
$password = add_escape_custom('hassanBa123');
//$greetings = isset($_POST['greetings']) ? add_escape_custom($_POST['greetings']) : "";
$title = !empty($_POST['title']) ? add_escape_custom($_POST['title']) : 'Doctor';
$device_token = isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '';

$pin = !empty($_POST['pin']) ? add_escape_custom($_POST['pin']) : substr(uniqid(rand()), 0, 4);



$createDate = date('Y-m-d');


sqlStatement("lock tables gacl_aro read");
$result5 = sqlQuery("select max(id)+1 as id from gacl_aro");
$gacl_aro_id = $result5['id'];
sqlStatement("unlock tables");
$secretKey = getUniqueSecretkey();



//$strQuery = "SELECT * FROM medmasterusers WHERE username LIKE '{$username}'";
//$result = sqlQuery($strQuery);

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

    $strQuery1 = "INSERT INTO `users`(`username`, `password`, `fname`, `lname`,  `phone`, `email`, `authorized`,`calendar`, `upin`, `create_date`, `secret_key`,  `title`, `ip_address`, `country_code`, `country_name`, `state`, `city`, `zip`, `latidute`, `longitude`, `time_zone`)
                            VALUES ('{$username}','{$password1}','{$firstname}','{$lastname}','{$phone}','{$email}',1,1, '" . $pin1 . "', '" . $createDate . "','" . $secretKey . "','{$title}','{$responce_array->ipAddress}','{$responce_array->countryCode}','{$responce_array->countryName}','{$responce_array->regionName}','{$responce_array->cityName}','{$responce_array->zipCode}','{$responce_array->latitude}','{$responce_array->longitude}','{$responce_array->timeZone}')";
    $result1 = sqlInsert($strQuery1);

    echo $result1;

    $last_user_id = $result1;


    $strQuery2 = "INSERT INTO `gacl_aro`(`id`, `section_value`, `value`, `order_value`, `name`) 
                    VALUES ('{$gacl_aro_id}', 'users', '{$username}', '10','" . $firstname . " " . $lastname . "')";


    $result2 = sqlInsert($strQuery2);

    echo "<br />" . $result2;

    $strQuery3 = "INSERT INTO `groups`(`name`, `user`) 
                        VALUES ('Default', '" . $username . "')";
    $result3 = sqlInsert($strQuery3);
    echo "<br />" . $result3;


    $strQuery4 = "INSERT INTO `gacl_groups_aro_map`(`group_id`, `aro_id`) 
                    VALUES('11', '" . $gacl_aro_id . "')";
    $result4 = sqlInsert($strQuery4);

    echo "<br />" . $result4;

    $token = createToken($last_user_id, true, $device_token);

    echo "<br />" . $token;
    
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