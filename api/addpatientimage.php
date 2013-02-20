<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');
$xml_array = array();

$token = $_POST['token'];

$patient_id = $_POST['patientId'];
$docdate = $_POST['docDate'];
$list_id = $_POST['listId'];
$category_id = $_POST['categoryId'];
$image_content = $_POST['imageData'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'docs', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['authUser'] = $patient_id;

    if ($acl_allow) {
        $id = 1;
        $type = "file_url";
        $size = '';
        $date = date('Y-m-d H:i:s');
        $url = '';
        $mimetype = 'image/jpeg';
        $hash = '';

        $image_path = $_SERVER['DOCUMENT_ROOT'] . "/openemr/sites/default/documents/{$patient_id}";

        if (!file_exists($image_path)) {
            mkdir($image_path);
        }

        switch ($category_id) {
            case 1: // Medicall Record
                $cat_id = 10;
                break;
            case 2: // Patient Id Card
                $cat_id = 5;
                break;
            case 3: // Patient Photograph
                $cat_id = 3;
                break;
            case 4: // Lab report
                $cat_id = 2;
                break;
        }

        file_put_contents($image_path . "/" . date('Y-m-d H-i-s') . ".jpg", base64_decode($image_content));

        $url = $image_path . "/" . $date . ".jpg";
        $size = filesize($url);

        exit;

        sqlStatement("lock tables documents read");

        $result = sqlQuery("select max(id)+1 as did from documents");

        sqlStatement("unlock tables");

        if ($result['did'] > 1) {
            $id = $result['did'];
        }

        $strQuery = "INSERT INTO `documents`( `id`, `type`, `size`, `date`, `url`, `mimetype`, `foreign_id`, `docdate`, `hash`, `list_id`) 
             VALUES ({$id},'{$type}','{$size}','{$date}','{$url}','{$mimetype}',{$patient_id},{$docdate},'{$hash}','{$list_id}')";

        $result = sqlStatement($strQuery);

        $strQuery1 = "INSERT INTO `categories_to_documents`(`category_id`, `document_id`) VALUES ({$cat_id},{$id})";

        $result1 = sqlStatement($strQuery1);

        if ($result && $result1) {
            $xml_array['status'] = "0/" . $result;
            $xml_array['reason'] = "The Image has been added";
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
