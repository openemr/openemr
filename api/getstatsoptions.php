<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<list>";

$token = $_POST['token'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('lists', 'default', $user);
    if ($acl_allow) {

        $strQuery = "SELECT option_id, title FROM list_options WHERE list_id  = 'language'";

        $result = $db->get_results($strQuery);

        $strQuery1 = "SELECT option_id, title FROM list_options WHERE list_id  = 'race'";

        $result1 = $db->get_results($strQuery1);

        $strQuery2 = "SELECT option_id, title FROM list_options WHERE list_id  = 'ethnicity'";

        $result2 = $db->get_results($strQuery2);

        if ($result || $result1 || $result2) {
            newEvent($event = 'feesheet-options-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            newEvent($event = 'feesheet-options-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery1);
            newEvent($event = 'feesheet-options-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery2);

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Options has been fetched</reason>";

            $xml_string .= "<language>\n";
            $xml_string .= "<unassigned>Unassigned</unassigned>\n";
            for ($i = 0; $i < count($result); $i++) {

                foreach ($result[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($fieldName == 'fs_category') {
                        
                    } else {
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
            }
            $xml_string .= "</language>";

            $xml_string .= "<race>\n";
            $xml_string .= "<unassigned>Unassigned</unassigned>\n";
            for ($i = 0; $i < count($result1); $i++) {

                foreach ($result1[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);

                    if ($fieldName == 'fs_category') {
                        
                    } else {
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
            }
            $xml_string .= "</race>";

            $xml_string .= "<ethnicity>\n";
            $xml_string .= "<unassigned>Unassigned</unassigned>\n";
            for ($i = 0; $i < count($result2); $i++) {

                foreach ($result2[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);

                    if ($fieldName == 'fs_category') {
                        
                    } else {
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
            }
            $xml_string .= "</ethnicity>";
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

$xml_string .= "</list>";
echo $xml_string;
?>