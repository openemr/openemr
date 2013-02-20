<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

function supervisorName($supervisor_id, $db) {

    $strQuery = "SELECT fname, lname FROM users WHERE id =" . $supervisor_id;
    $result = $db->get_results($strQuery);
    return $result[0]->fname . " " . $result[0]->lname;
}

$xml_string = "";
$xml_string = "<feesheet>";

$token = $_POST['token'];
$visit_id = $_POST['visit_id'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('acct', 'bill', $user);

    if ($acl_allow) {
        $strQuery = "SELECT b.id, b.authorized, b.fee, b.code_type, b.code, b.modifier, b.units, b.justify, b.provider_id, 
				fe.supervisor_id, u.fname, u.lname, pd.pricelevel, c.code_text
          		FROM billing AS b
				LEFT JOIN users AS u ON u.id = b.provider_id
          		LEFT JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter
				LEFT JOIN codes AS c ON c.code = b.code
          		LEFT JOIN patient_data AS pd ON pd.pid = b.pid WHERE b.activity = 1 AND b.encounter = " . $visit_id;

        $result = $db->get_results($strQuery);

        if ($result) {
            newEvent($event = 'feesheet-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Feesheet records has been fetched.</reason>";

            for ($i = 0; $i < count($result); $i++) {
                $xml_string .= "<item>\n";

                foreach ($result[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($fieldName == 'fname' || $fieldName == 'lname') {
                        
                    } else {
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
                $supervisor_id = $result[$i]->supervisor_id;
                $fname = $result[$i]->fname;
                $lname = $result[$i]->lname;
                $xml_string .= "<provider>" . $fname . " " . $lname . "</provider>\n";
                $xml_string .= "<supervisor>\n" . supervisorName($supervisor_id, $db) . "</supervisor>\n";
                $xml_string .= "</item>\n";
            }
        } else {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>No records found.</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</feesheet>";
echo $xml_string;
?>