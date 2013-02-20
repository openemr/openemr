<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<contact>";

$token = $_POST['token'];
$id = $_POST['userId'];


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {

        $strQuery = 'UPDATE users SET ';
        $strQuery .= ' active = 0';
        $strQuery .= ' WHERE  username = \'\' AND password = \'\' AND id = ' . $id;

        $result = $db->query($strQuery);

        if ($result) {
            newEvent($event = 'contact-record-deleted', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Contact has been deleted</reason>";
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

$xml_string .= "</contact>";
echo $xml_string;
?>