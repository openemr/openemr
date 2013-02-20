<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<soap>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];

$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : NULL;
$subjective = $_POST['subjective'];
$objective = $_POST['objective'];
$assessment = $_POST ['assessment'];
$plan = $_POST['plan'];
$authorized = isset($_POST['authorized']) ? $_POST['authorized'] : 0;
$activity = isset($_POST['activity']) ? $_POST['activity'] : 1;

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
      $_SESSION['authUser'] = $user;
      $_SESSION['authGroup'] = $site;
      $_SESSION['pid'] = $patientId;
   
      if ($acl_allow) {
        $strQuery = "INSERT INTO form_soap 
            (pid, user, date, groupname, authorized, activity, subjective, objective, assessment,  plan) 
            VALUES (" . $patientId . ", '" . $user . "', '" . date('Y-m-d H:i:s') . "','" . $groupname . "', '" . $authorized . "','" . $activity . "',  '" . $subjective . "' , '" . $objective . "' , '" . $assessment . "', '" . $plan . "')";

        $result = sqlInsert($strQuery);
        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'SOAP', $last_inserted_id, $formdir = 'soap', $patientId, $authorized = "1", $date = "NOW()", $user, $group = "Default");

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Soap has been added</reason>";
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