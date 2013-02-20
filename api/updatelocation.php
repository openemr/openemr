<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<location>";

$token = $_POST['token'];
$name = $_POST['name'];
$locationId = $_POST['id'];

if ($userId = validateToken($token)) {

    $user = getUsername($userId);

    $acl_allow = acl_check('admin', 'super', $user);
    
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    
    if ($acl_allow) {
        
        $strQuery = 'UPDATE facility SET ';
        $strQuery .= ' name = "' . $name . '"';
        $strQuery .= ' WHERE id = ' . $locationId;
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Location has been updated</reason>";
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

$xml_string .= "</location>";
echo $xml_string;
?>