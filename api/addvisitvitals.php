<?php

header("Content-Type:text/xml");
$ignoreAuth = true;

require_once'classes.php';
$xml_array = array();

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];

$date = date('Y-m-d H:i:s');
$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : 'default';
$authorized = isset($_POST['authorized']) ? $_POST['authorized'] : 1;
$activity = $_POST['activity'];
$bps = $_POST['bps'];
$bpd = $_POST['bpd'];
$weight = $_POST['weight'];
$height = $_POST['height'];
$temperature = $_POST['temperature'];
$temp_method = $_POST['temp_method'];
$pulse = $_POST['pulse'];
$respiration = $_POST['respiration'];
$note = $_POST['note'];
$BMI = $_POST['BMI'];
$BMI_status = $_POST['BMI_status'];
$waist_circ = $_POST['waist_circ'];
$head_circ = $_POST['head_circ'];
$oxygen_saturation = $_POST['oxygen_saturation'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {
        $strQuery = "INSERT INTO `form_vitals`(`date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `bps`, `bpd`, `weight`, `height`, `temperature`, `temp_method`, `pulse`, `respiration`, `note`, `BMI`, `BMI_status`, `waist_circ`, `head_circ`, `oxygen_saturation`) 
                    VALUES ('{$date}','{$patientId}','{$user}','{$groupname}','{$authorized}','{$activity}','{$bps}','{$bpd}','{$weight}','{$height}','{$temperature}','{$temp_method}','{$pulse}','{$respiration}','{$note}','{$BMI}','{$BMI_status}','{$waist_circ}','{$head_circ}','{$oxygen_saturation}')";

        $result = sqlInsert($strQuery);
        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'Vitals', $last_inserted_id, $formdir = 'vitals', $patientId, $authorized = "1", $date = "NOW()", $user, $group = "Default");
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'The Visit vital has been added';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'visitvitals');
echo $xml;
?>