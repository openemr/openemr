<?php
header("Content-Type:text/xml");
require_once 'includes/class.database.php';
require_once 'includes/functions.php';
require_once 'includes/class.arraytoxml.php';

$xml_array = array();

if ($userId = validateToken($token)) {
    $username = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $username);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {

        $strQuery = "INSERT INTO pnotes (date, body, pid, user, title, assigned_to, deleted, message_status) 
					 VALUES ('" . date('Y-m-d H:i:s') . "', '" . $notes . "', " . $patientId . ", '" . $username . "', '" . $title . "', '" . $username . "', 0, 'New')";
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Patient notes has been added successfully';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'PatientNotes');
echo $xml;
?>