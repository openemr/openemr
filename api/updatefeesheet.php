<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<feesheet>";

$token = $_POST['token'];
$id = $_POST['id'];

$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];
$provider_id = $_POST['provider_id'];
$supervisor_id = $_POST['supervisor_id'];
$auth = $_POST['auth'];
$code_type = $_POST['code_type'];
$code = $_POST['code'];
$modifier = $_POST['modifier'];
$units = max(1, intval(trim($_POST['units'])));
$price = $_POST['price'];
$priceLevel = $_POST['priceLevel'];
$justify = $_POST['justify'];

$ndc_info = !empty($_POST['ndc_info']) ? $_POST['ndc_info'] : '';
$noteCodes = !empty($_POST['noteCodes']) ? $_POST['noteCodes'] : '';
$fee = sprintf('%01.2f', (0 + trim($price)) * $units);

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('acct', 'bill', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {

        $strQuery = 'UPDATE billing SET ';
        $strQuery .= ' code_type = "' . $code_type . '",';
        $strQuery .= ' code = "' . $code . '",';
        $strQuery .= ' modifier = "' . $modifier . '",';
        $strQuery .= ' justify = "' . $justify . '",';
        $strQuery .= ' authorized = "' . $auth . '",';
        $strQuery .= ' provider_id = "' . $provider_id . '",';
        $strQuery .= ' units = "' . $units . '",';
        $strQuery .= ' bill_process = 0,';
        $strQuery .= ' notecodes = "' . $notesCodes . '",';
        $strQuery .= ' fee = "' . $fee . '"';
        $strQuery .= ' WHERE id = ' . $id;

        $result = sqlStatement($strQuery);

        $strQuery1 = 'UPDATE `patient_data` SET';
        $strQuery1 .= ' pricelevel  = "' . $priceLevel . '"';
        $strQuery1 .= ' WHERE pid = ' . $patientId;

        $result1 = sqlStatement($strQuery1);

        $strQuery2 = 'UPDATE `form_encounter` SET';
        $strQuery2 .= ' provider_id  = "' . $provider_id . '",';
        $strQuery2 .= ' supervisor_id  = "' . $supervisor_id . '"';
        $strQuery2 .= ' WHERE pid = ' . $patientId . ' AND encounter = ' . $visit_id;

        $result2 = sqlStatement($strQuery2);


        if ($result && $result1 && $result2) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Feesheet has been updated</reason>";
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

$xml_string .= "</feesheet>";
echo $xml_string;
?>