<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');
$xml_array = array();

$token = $_POST['token'];

$patient_id = $_POST['patientId'];
$docdate = $_POST['docDate'];
$list_id = isset($_POST['listId']) ? $_POST['listId'] : 0;
$cat_id = $_POST['categoryId'];
$image_content = $_POST['data'];
$ext = $_POST['docType'];
$mimetype = $_POST['mimeType'];

if ($userId = validateToken($token)) {
    $provider_id = getPatientsProvider($patient_id);
    $provider_username = getProviderUsername($provider_id);

    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'docs', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patient_id;

    if ($acl_allow) {

        $id = 1;
        $type = "file_url";
        $size = '';
        $date = date('Y-m-d H:i:s');
        $url = '';
        $hash = '';

        $image_path = $sitesDir . "{$site}/documents/{$patient_id}";

        if (!file_exists($image_path)) {
            mkdir($image_path);
        }

        $image_date = date('Y-m-d_H-i-s');

        file_put_contents($image_path . "/" . $image_date . "." . $ext, base64_decode($image_content));


        sqlStatement("lock tables documents read");

        $result = sqlQuery("select max(id)+1 as did from documents");

        sqlStatement("unlock tables");

        if ($result['did'] > 1) {
            $id = $result['did'];
        }

        $hash = sha1_file($image_path . "/" . $image_date . "." . $ext);

        $url = "file://" . $image_path . "/" . $image_date . "." . $ext;

        $size = filesize($url);

        $strQuery = "INSERT INTO `documents`( `id`, `type`, `size`, `date`, `url`, `mimetype`, `foreign_id`, `docdate`, `hash`, `list_id`) 
             VALUES ({$id},'{$type}','{$size}','{$date}','{$url}','{$mimetype}',{$patient_id},'{$docdate}','{$hash}','{$list_id}')";

        $result = sqlStatement($strQuery);

        $strQuery1 = "INSERT INTO `categories_to_documents`(`category_id`, `document_id`) VALUES ({$cat_id},{$id})";

        $result1 = sqlStatement($strQuery1);

        if ($cat_id == 2) {
            $device_token_badge = getDeviceTokenBadge($provider_username, 'labreport');
            $badge = $device_token_badge ['badge'];
            $deviceToken = $device_token_badge ['device_token'];
            if ($deviceToken) {
                $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Labreport Notification!');
            }
        }

        if ($result && $result1) {
//            newEvent($event = 'patient-record-add', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_array['status'] = "0";
            $xml_array['reason'] = "The Image has been added";
            if ($notification_res) {
                $xml_array['notification'] = 'Add Patient document Notification(' . $notification_res . ')';
            } else {
                $xml_array['notification'] = 'Notificaiotn Failed.';
            }
        } else {
            $xml_array['status'] = "-1";
            $xml_array['reason'] = "ERROR: Sorry, there was an error processing your data. Please re-submit the information again.";
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = "-2";
    $xml_array['reason'] = 'Invalid Token';
}


$xml = ArrayToXML::toXml($xml_array, 'PatientImage');
echo $xml;
?>
