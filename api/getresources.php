<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<resources>";

$token = $_POST['token'];
$check_user = !empty($_POST['check_user']) ? $_POST['check_user'] : '';

$list_id = 'ExternalResources';
if ($userId = validateToken($token)) {

    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {

        $strQuery1 = "SELECT *
                                FROM `list_options`
                                WHERE `list_id` = '{$list_id}' AND 
                                       `notes` NOT LIKE '%/sites/default/documents/userdata/%'";
        if ($check_user) {
            $strQuery1 .= "AND `option_value` = " . $userId;
        }
        $result1 = $db->get_results($strQuery1);

        $strQuery2 = "SELECT *
                                FROM `list_options`
                                WHERE `list_id` = '{$list_id}' AND 
                                       `notes` LIKE '%/sites/default/documents/userdata/images/%'";
        if ($check_user) {
            $strQuery2 .= "AND `option_value` = " . $userId;
        }
        $result2 = $db->get_results($strQuery2);

        $strQuery3 = "SELECT *
                                FROM `list_options`
                                WHERE `list_id` = '{$list_id}' AND 
                                       `notes` LIKE '%/sites/default/documents/userdata/pdf/%'";
        if ($check_user) {
            $strQuery3 .= "AND `option_value` = " . $userId;
        }
        $result3 = $db->get_results($strQuery3);

        $strQuery4 = "SELECT *
                                FROM `list_options`
                                WHERE `list_id` = '{$list_id}' AND 
                                       `notes` LIKE '%/sites/default/documents/userdata/videos/%'";
        if ($check_user) {
            $strQuery4 .= "AND `option_value` = " . $userId;
        }
        $result4 = $db->get_results($strQuery4);

        if ($result1 || $result2 || $result3 || $result4) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Resources Record has been fetched</reason>";

            for ($i = 0; $i < count($result1); $i++) {
                $xml_string .= "<resource>\n";
                $xml_string .= "<type>link</type>\n";
                foreach ($result1[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</resource>\n";
            }

            for ($i = 0; $i < count($result2); $i++) {
                $xml_string .= "<resource>\n";
                $xml_string .= "<type>image</type>\n";
                foreach ($result2[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</resource>\n";
            }

            for ($i = 0; $i < count($result3); $i++) {
                $xml_string .= "<resource>\n";
                $xml_string .= "<type>pdf</type>\n";
                foreach ($result3[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</resource>\n";
            }
            for ($i = 0; $i < count($result4); $i++) {
                $xml_string .= "<resource>\n";
                $xml_string .= "<type>video</type>\n";
                foreach ($result4[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</resource>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</resources>";
echo $xml_string;
?>