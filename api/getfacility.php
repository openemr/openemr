<?php

//header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<facilities>";

//$token = $_POST['token'];
$token = '722192de79af528200a232e958b64f49';

$primary_business_entity = 0;

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {

        try {
            $strQuery = "SELECT id, name FROM facilitys";

            $result = sqlStatement($strQuery);

            $numRows = sqlNumRows($result);

            if ($numRows > 0) {
                $xml_string .= "<status>0</status>";
                $xml_string .= "<reason>The Facilities Record has been fetched</reason>";

                while ($res = sqlFetchArray($result)) {
                    $xml_string .= "<facility>\n";

                    foreach ($res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }

                    $xml_string .= "</facility>\n";
                }
            } else {
                $xml_string .= "<status>-1</status>";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            exit;
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</facilities>";
echo $xml_string;
?>