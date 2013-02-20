<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<notifications>";

$token = $_POST['token'];
$primary_business_entity = 0;

if ($userId = validateToken($token)) {
    $strQuery = "SELECT * FROM `tokens` WHERE token = '{$token}'";
    $result = $db->get_row($strQuery);

    if ($result) {
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>Notifications fetching.</reason>";
        $xml_string .= "<message_badge>{$result->message_badge}</message_badge>";
        $xml_string .= "<appointment_badge>{$result->appointment_badge}</appointment_badge>";
        $xml_string .= "<labreports_badge>{$result->labreports_badge}</labreports_badge>";
        $xml_string .= "<prescription_badge>{$result->prescription_badge}</prescription_badge>";
        $xml_string .= "<total_badge>" . ($result->message_badge + $result->appointment_badge + $result->labreports_badge + $result->prescription_badge) . "</total_badge>";
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</notifications>";
echo $xml_string;
?>