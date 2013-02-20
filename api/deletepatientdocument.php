<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<PatientImage>";

$token = $_POST['token'];
$document_id = $_POST['documentId'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {
        $strQuery1 = "SELECT `url`
                    FROM `documents`
                    WHERE `id` = " . $document_id;
        $result1 = $db->get_results($strQuery1);

        $file_path = $result1[0]->url;
        unlink($file_path);

        $strQuery = "DELETE FROM `documents` WHERE id =" . $document_id;
        $result = $db->query($strQuery);

        $strQuery2 = "DELETE FROM `categories_to_documents` WHERE document_id =" . $document_id;
        $result2 = $db->query($strQuery2);

        if ($result) {
            newEvent($event = 'document-record-select', $user, $groupname = 'Default', $success = '1', $comments = $strQuery1);
            newEvent($event = 'document-record-delete', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            newEvent($event = 'document-record-delete', $user, $groupname = 'Default', $success = '1', $comments = $strQuery2);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Pateient document has been deleted</reason>";
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

$xml_string .= "</PatientImage>";
echo $xml_string;
?>