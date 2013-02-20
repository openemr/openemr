<?php

header("Content-Type:text/xml");
$ignoreAuth = true;

require_once 'classes.php';
$xml_array = array();

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$vital_id = $_POST['vital_id'];

$date = date('Y-m-d H:i:s');
$groupname = $_POST['groupname'];
$authorized = $_POST['authorized'];
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
        $strQuery = "UPDATE `form_vitals` SET 
                                        `date`='{$date}',
                                        `pid`='{$patientId}',
                                        `user`='{$user}',
                                        `groupname`='{$groupname}',
                                        `authorized`='{$authorized}',
                                        `activity`='{$activity}',
                                        `bps`='{$bps}',
                                        `bpd`='{$bpd}',
                                        `weight`='{$weight}',
                                        `height`='{$height}',
                                        `temperature`='{$temperature}',
                                        `temp_method`='{$temp_method}',
                                        `pulse`='{$pulse}',
                                        `respiration`='{$respiration}',
                                        `note`='{$note}',
                                        `BMI`='{$BMI}',
                                        `BMI_status`='{$BMI_status}',
                                        `waist_circ`='{$waist_circ}',
                                        `head_circ`='{$head_circ}',
                                        `oxygen_saturation`='{$oxygen_saturation}' 
        WHERE id = {$vital_id}";

        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_array['status'] = 0;
            $xml_array['reason'] = 'Visit vital update successfully';
        } else {
            $xml_array['status'] = -1;
            $xml_array['reason'] = 'Could not update isit vital';
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'visitvitals');
echo $xml;
?>