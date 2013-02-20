<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string .= "<Messages>";

$token = $_POST['token'];

$from_date = !empty($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = !empty($_POST['to_date']) ? $_POST['to_date'] : '';

$date = '';
$sortby = 'date';
$sortorder = 'DESC';
$begin = '0';
$listnumber = '100';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $user);

    if ($acl_allow) {
        $provider_id = getUserProviderId($userId);
        $username = getProviderUsername($provider_id);

        $where = '';
        if ($from_date) {
            $where .= " AND pnotes.date >= '{$from_date}'";
        }
        if ($to_date) {
            $where .= " AND pnotes.date <= '{$to_date}'";
        }

        $sql = "SELECT pnotes.id, pnotes.assigned_to, pnotes.user, pnotes.pid, pnotes.title, pnotes.date,pnotes.body, pnotes.message_status, 
          IF(pnotes.user != pnotes.pid,users.fname,patient_data.fname) as users_fname,
          IF(pnotes.user != pnotes.pid,users.lname,patient_data.lname) as users_lname,
          patient_data.fname as patient_data_fname, patient_data.lname as patient_data_lname
          FROM ((pnotes LEFT JOIN users ON pnotes.user = users.username) 
          JOIN patient_data ON pnotes.pid = patient_data.pid) WHERE 
          pnotes.deleted != '1' {$where} AND pnotes.user LIKE ?" .
                " order by " . add_escape_custom($sortby) . " " . add_escape_custom($sortorder) .
                " limit " . add_escape_custom($begin) . ", " . add_escape_custom($listnumber);
        $result = sqlStatement($sql, array($username));


        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Messages Record has been fetched</reason>";

            while ($myrow = sqlFetchArray($result)) {
                $xml_string .= "<Message>\n";

                foreach ($myrow as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $send_user_query = "SELECT fname, lname, mname
                                    FROM  `users` 
                                    WHERE  `username` LIKE  ?";
                $send_user_result = sqlStatement($send_user_query, array($myrow['assigned_to']));
                $send_user_row = sqlFetchArray($send_user_result);

                $xml_string .= "<recevieuser_fname>{$send_user_row['fname']}</recevieuser_fname>";
                $xml_string .= "<recevieuser_mname>{$send_user_row['mname']}</recevieuser_mname>";
                $xml_string .= "<recevieuser_lname>{$send_user_row['lname']}</recevieuser_lname>";
                $xml_string .= "</Message>\n";
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

$xml_string .= "</Messages>";
echo $xml_string;
?>