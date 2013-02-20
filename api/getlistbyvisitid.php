<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');
ini_set('display_errors', '1');
require_once("$srcdir/lists.inc");

$xml_string = "";
$xml_string .= "<Listitems>\n";

$token = $_POST['token'];
$pid = $_POST['patientId'];
$visit_id = isset($_POST['visit_id']) && !empty($_POST['visit_id']) ? $_POST['visit_id'] : '';

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);
    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('encounters', 'auth_a', $username);
    if ($acl_allow) {

        $sql = "select l.* from lists as l
                        LEFT JOIN `issue_encounter` as i ON i.list_id = l.id
                        where l.pid='$pid'";
        if ($visit_id) {
            $sql .= " AND i.encounter = {$visit_id}";
        }

        $sql .= " order by l.date DESC";

        $list = $db->get_results($sql);

        if ($list) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>The Patient Record has been fetched</reason>\n";

            for ($i = 0; $i < count($list); $i++) {
                $xml_string .= "<listitem>\n";

                foreach ($list[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }

                $diagnosis_title = getDrugTitle($list[$i]->diagnosis, $db);
                $xml_string .= "<diagnosistitle>{$diagnosis_title}</diagnosistitle>";
                $xml_string .= "</listitem>\n";
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
$xml_string .= "</Listitems>\n";
echo $xml_string;
?>