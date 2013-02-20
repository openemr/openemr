<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');

//ini_set('display_errors', '1');

$xml_string = "";
$xml_string .= "<PatientVitals>\n";

//$token = $_POST['token'];
//$visit_id = add_escape_custom($_POST['visit_id']);

$token = 'e85e54d56c48027eddd7150b8ea2eab3';
$visit_id = add_escape_custom(8);

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {
        $strQuery = "SELECT fv.* 
                                FROM  `forms` AS f
                                INNER JOIN  `form_vitals` AS fv ON f.form_id = fv.id
                                WHERE  `encounter` = ?
                                AND  `form_name` =  'Vitals'
                                ORDER BY f.id DESC";
        $result = sqlStatement($strQuery,array($visit_id));

        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>Success processing patient vitals records</reason>\n";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<Vital>\n";

                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                
                
                $user_query = "SELECT  `firstname` ,  `lastname` 
                                                FROM  `medmasterusers` 
                                                WHERE username LIKE  ?";
                
                $user_result = sqlQuery($user_query,array($res['user']));
                
                $xml_string .= "<firstname>".$user_result['firstname']."</firstname>\n";
                $xml_string .= "<lastname>".$user_result['lastname']."</lastname>\n";
                $xml_string .= "</Vital>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>\n";
            $xml_string .= "<reason>Cound not find results</reason>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}
$xml_string .= "</PatientVitals>\n";
echo $xml_string;
?>