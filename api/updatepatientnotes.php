<?php

header("Content-Type:text/xml");
require_once 'classes.php';

$xml_array = array();

$token = $_POST['token'];
$noteId = $_POST['noteId'];
$patientId = $_POST['patientId'];
$notes = $_POST['notes'];
$title = $_POST['title'];

if ($userId = validateToken($token)) {
    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    $_SESSION['authUser'] = $username;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {
        $strQuery = "UPDATE pnotes SET date = '" . date('Y-m-d H:i:s') . "', body = '" . $notes . "', user = '" . $username . "', title = '" . $title . "', assigned_to = '" . $username . "' WHERE id = " . $noteId;
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Patient notes has been updated';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}
$xml = ArrayToXML::toXml($xml_array, 'PatientNotes');
echo $xml;
?>