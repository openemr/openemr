<?php

header("Content-Type:text/xml");
$ignoreAuth = true;

require_once 'classes.php';
$xml_string = "";
$xml_string = "<badge>";

$token = $_POST['token'];

$message_badge = $_POST['message_badge'];
$appointment_badge = $_POST['appointment_badge'];
$labreports_badge = $_POST['labreports_badge'];
$prescription_badge = $_POST['prescription_badge'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'demo', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    if ($acl_allow) {
        $badges = getAllBadges($token);

        $message_badge = $message_badge >= 0 ? $message_badge : $badges->message_badge;
        $appointment_badge = $appointment_badge >= 0 ? $appointment_badge : $badges->appointment_badge;
        $labreports_badge = $labreports_badge >= 0 ? $labreports_badge : $badges->labreports_badge;
        $prescription_badge = $prescription_badge >= 0 ? $prescription_badge : $badges->prescription_badge;

        $strQuery = "UPDATE `tokens` SET 
        `message_badge`= {$message_badge},`appointment_badge`= {$appointment_badge},
        `labreports_badge`= {$labreports_badge},`prescription_badge`= {$prescription_badge} WHERE token='{$token}'";


        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Badges has been updated</reason>";
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

$xml_string .= "</badge>";
echo $xml_string;
?>