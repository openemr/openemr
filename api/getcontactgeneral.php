<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<contacts>";

$token = $_POST['token'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

    if ($acl_allow) {

        $strQuery = "SELECT id, username,
                                password , authorized, info, source, u.title, fname, lname, mname, upin, see_auth, active, npi, taxonomy, specialty, organization, valedictory, assistant, email, url, street, streetb, city, state, zip, phone, phonew1, phonew2, phonecell, fax, u.notes, lp.title AS image_title, lp.notes AS image_url
                                FROM users AS u
                                LEFT JOIN list_options AS lp ON u.id = lp.option_value
                                WHERE username = ''
                                AND password = ''
                                AND active = 1
                                ";
        $result = $db->get_results($strQuery);

        if ($result) {
            newEvent($event = 'contact-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Contact Record has been fetched</reason>";

            for ($i = 0; $i < count($result); $i++) {
                $xml_string .= "<contact>\n";

                foreach ($result[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }

                $xml_string .= "</contact>\n";
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

$xml_string .= "</contacts>";
echo $xml_string;
?>