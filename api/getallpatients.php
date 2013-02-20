<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$token = $_POST['token'];

$xml_string = "";
$xml_string .= "<PatientList>\n";

if ($userId = validateToken($token)) {

    $user_data = getUserData($userId);

    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('patients', 'demo', $username);

    if ($acl_allow) {
        switch ($emr) {
            case 'openemr':
                $strQuery = "SELECT id, pid, fname as firstname, lname as lastname,mname as middlename, phone_contact as phone, dob, sex as gender FROM patient_data";
                $dbresult = $db->query($strQuery);

                if ($dbresult) {
                    $xml_string .= "<status>0</status>\n";
                    $xml_string .= "<reason>The Patient Records has been fetched</reason>\n";
                    $counter = 0;

                    while ($row = $db->get_row($query = $strQuery, $output = ARRAY_A, $y = $counter)) {
                        $xml_string .= "<Patient>\n";

                        $p_id = 0;
                        foreach ($row as $fieldname => $fieldvalue) {
                            $rowvalue = xmlsafestring($fieldvalue);
                            if ($fieldname == "pid")
                                $p_id = $fieldvalue;
                            $xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
                        }

                        $strQuery1 = "SELECT d.date,d.size,d.url,d.docdate,d.mimetype,c2d.category_id
                                FROM `documents` AS d
                                INNER JOIN `categories_to_documents` AS c2d ON d.id = c2d.document_id
                                WHERE foreign_id = {$p_id}
                                AND category_id = 13
                                ORDER BY category_id, d.date DESC 
                                LIMIT 1";

                        $result1 = $db->get_row($strQuery1);

                        if ($result1) {
                            $xml_string .= "<profileimage>" . getUrl($result1->url) . "</profileimage>\n";
                        } else {
                            $xml_string .= "<profileimage></profileimage>\n";
                        }

                        $xml_string .= "</Patient>\n";
                        $counter++;
                    }
                } else {
                    $xml_string .= "<status>-1</status>\n";
                    $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
                }
                break;
            case 'greenway':
                include 'greenway/patientsearch.php';
                break;
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}


$xml_string .= "</PatientList>\n";
echo $xml_string;
?>