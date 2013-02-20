<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';
include_once("$srcdir/onotes.inc");

$xml_string = "";
$xml_string .= "<officenotes>";

$token = $_POST['token'];
$body = $_POST['body'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {
        $result = getOnoteByDate("", 1, "date,body,user", "all", 0);

        $xml_string .= "<status>0</status>\n";
        $xml_string .= "<reason>Success processing insurance companies records</reason>\n";

        foreach ($result as $iter) {

            $xml_string .= "<officenote>\n";
            $xml_string .= "<date>$iter[date]</date>\n";
            $xml_string .= "<user>$iter[user]</user>\n";
            $xml_string .= "<body>$iter[body]</body>\n";
            $xml_string .= "</officenote>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</officenotes>";
echo $xml_string;
?>