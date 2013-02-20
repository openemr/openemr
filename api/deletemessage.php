<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<message>";

$token = $_POST['token'];
$id = $_POST['id'];

if ($userId = validateToken($token)) {

    $result = deletePnote($id);

    if ($result) {
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason> The Message has been deleted</reason>";
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</message>";
echo $xml_string;
?>