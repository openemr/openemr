<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<list>";


$token = $_POST['token'];
$patientId = $_POST['patientId'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {
        $strQuery = "SELECT f.encounter as visit_id, fsoap. id,fsoap. date, subjective, objective, assessment, plan, fsoap.user
				FROM forms AS f
				INNER JOIN `form_soap` AS fsoap ON f.form_id = fsoap.id
				WHERE fsoap.pid = {$patientId}
				AND `form_name` = 'SOAP'
                                ORDER BY fsoap. date DESC";

        $result = $db->get_results($strQuery);

        if ($result) {
            newEvent($event = 'soap-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Soap Record has been fetched</reason>";

            for ($i = 0; $i < count($result); $i++) {
                $xml_string .= "<soap>\n";

                foreach ($result[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }

                $user_query = "SELECT  `firstname` ,  `lastname` 
                                                    FROM  `medmasterusers` 
                                                    WHERE username LIKE  '{$result[$i]->user}'";
                $user_result = $db->get_row($user_query);
                $xml_string .= "<firstname>{$user_result->firstname}</firstname>\n";
                $xml_string .= "<lastname>{$user_result->lastname}</lastname>\n";

                $xml_string .= "</soap>\n";
            }
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