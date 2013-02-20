<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$token = $_POST['token'];

$xml_string = "";
$xml_string .= "<Providers>\n";


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {
        $strQuery = "SELECT u.id, u.fname, u.lname, u.mname, u.username
                                                            FROM  `users` AS u
                                                            WHERE authorized =1
                                                            AND active =1";

        $dbresult = $db->query($strQuery);

        if ($dbresult) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>The Appointment Categories Record has been fetched</reason>\n";
            $counter = 0;

            while ($row = $db->get_row($query = $strQuery, $output = ARRAY_A, $y = $counter)) {
                $xml_string .= "<Provider>\n";

                foreach ($row as $fieldname => $fieldvalue) {
                    $rowvalue = xmlsafestring($fieldvalue);
                    $xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
                }

                $xml_string .= "</Provider>\n";
                $counter++;
            }
        } else {
            $xml_string .= "<status>-1</status>\n";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}
$xml_string .= "</Providers>\n";
echo $xml_string;
?>