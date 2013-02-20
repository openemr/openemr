<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require 'classes.php';

$xml_string = "";
$xml_string = "<options>";

$token = $_POST['token'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('acct', 'bill', $user);

    if ($acl_allow) {

        $strQuery = "SELECT * FROM fee_sheet_options WHERE fs_category = '1New Patient' ORDER BY fs_option";

        $result = $db->get_results($strQuery);

        $strQuery1 = "SELECT * FROM fee_sheet_options WHERE fs_category = '2Established Patient' ORDER BY fs_option";
        $result1 = $db->get_results($strQuery1);

        if ($result || $result1) {
            newEvent($event = 'feesheet-options-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            newEvent($event = 'feesheet-options-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery1);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Options Processed successfully</reason>";

            $xml_string .= "<newpatient>\n";
            for ($i = 0; $i < count($result); $i++) {

                $xml_string .= "<option>\n";

                foreach ($result[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($fieldName != 'fs_category' && $fieldName == 'fs_option') {
                        $xml_string .= "<$fieldName>" . substr($rowValue, 1) . "</$fieldName>\n";
                    }
                    if ($fieldName != 'fs_category' && $fieldName != 'fs_option' && $fieldName == 'fs_codes') {
                        $xml_string .= "<$fieldName>" . $rowValue . "</$fieldName>\n";
                    }
                }

                $xml_string .= "</option>\n";
            }
            $xml_string .= "</newpatient>";

            $xml_string .= "<establishedpatient>\n";

            for ($i = 0; $i < count($result1); $i++) {

                $xml_string .= "<option>\n";

                foreach ($result1[$i] as $fieldName => $fieldValue) {
                    $rowValue1 = xmlsafestring($fieldValue);

                    if ($fieldName != 'fs_category' && $fieldName == 'fs_option') {
                        $xml_string .= "<$fieldName>" . substr($rowValue1, 1) . "</$fieldName>\n";
                    }
                    if ($fieldName != 'fs_category' && $fieldName != 'fs_option' && $fieldName == 'fs_codes') {
                        $xml_string .= "<$fieldName>" . $rowValue1 . "</$fieldName>\n";
                    }
                }

                $xml_string .= "</option>\n";
            }
            $xml_string .= "</establishedpatient>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could not find results</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</options>";
echo $xml_string;
?>