<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require('classes.php');

$xml_string = "";
$xml_string = "<prescription>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$startDate = $_POST['startDate'];
$drug = $_POST['drug'];
$visit_id = $_POST['visit_id'];
$dosage = $_POST['dosage'];
$quantity = $_POST['quantity'];

$per_refill = $_POST['refill'];
$medication = $_POST['medication'];
$note = $_POST['note'];
$provider_id = $_POST['provider_id'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'med', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {
        $provider_username = getProviderUsername($provider_id);

        $strQuery = "INSERT INTO prescriptions (patient_id, date_added, date_modified, provider_id, start_date, drug, dosage, quantity, refills, medication, note, active, encounter) 
                                            VALUES (" . $patientId . ", '" . date('Y-m-d') . "', '" . date('Y-m-d') . "', {$provider_id}, '" . $startDate . "', '" . $drug . "', '" . $dosage . "', '" . $quantity . "', '" . $per_refill . "', " . $medication . ", '" . $note . "', 1, {$visit_id})";

        if ($medication) {
            $list_query = "insert into lists(date,begdate,type,activity,pid,user,groupname,title) 
                            values (now(),cast(now() as date),'medication',1,{$patientId},'{$user}','','{$drug}')";
            $list_result = sqlStatement($list_query);
        }
        $result = sqlStatement($strQuery);

        $device_token_badge = getDeviceTokenBadge($provider_username, 'prescription');
        $badge = $device_token_badge ['badge'];
        $deviceToken = $device_token_badge ['device_token'];
        if ($deviceToken) {
            $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Prescription Notification!');
        }

        if ($result && $list_result) {


            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Patient prescription added successfully</reason>";
            if ($notification_res) {
                $xml_array['notification'] = 'Add Prescription Notification(' . $notification_res . ')';
            } else {
                $xml_array['notification'] = 'Notificaiotn Failed.';
            }
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Couldn't add record</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</prescription>";
echo $xml_string;
?>