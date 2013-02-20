<?php

header("Content-Type:text/xml");
$ignoreAuth = true;

require_once 'classes.php';
$xml_string = "";
$xml_string = "<soap>";

$token = $_POST['token'];

$soapId = $_POST['id'];
$subjective = mysql_real_escape_string($_POST['subjective']);
$objective = mysql_real_escape_string($_POST['objective']);
$assessment = $_POST ['assessment'];
$plan = mysql_real_escape_string($_POST['plan']);

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    if ($acl_allow) {
        $strQuery = 'UPDATE form_soap SET ';
        $strQuery .= ' subjective = "' . $subjective . '",';
        $strQuery .= ' objective = "' . $objective . '",';
        $strQuery .= ' assessment = "' . $assessment . '",';
        $strQuery .= ' plan = "' . $plan . '"';
        $strQuery .= ' WHERE id = ' . $soapId;

        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Soap has been updated</reason>";
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

$xml_string .= "</soap>";
echo $xml_string;
?>