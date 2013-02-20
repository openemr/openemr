<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');

$xml_array = array();

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$image_data = isset($_POST['image_data']) ? $_POST['image_data'] : '';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'docs', $user);
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {

        if ($image_data) {

            $id = 1;
            $type = "file_url";
            $size = '';
            $date = date('Y-m-d H:i:s');
            $url = '';
            $mimetype = 'image/png';
            $hash = '';
            $patient_id = $patientId;
            $ext = 'png';
            $cat_title = 'Patient Profile Image';

            $strQuery2 = "SELECT id from `categories` WHERE name LIKE '{$cat_title}'";
            $result3 = $db->get_row($strQuery2);

            if ($result3) {
                $cat_id = $result3->id;
            } else {
                sqlStatement("lock tables categories read");

                $result4 = sqlQuery("select max(id)+1 as id from categories");

                $cat_id = $result4['id'];

                sqlStatement("unlock tables");

                $cat_insert_query = "INSERT INTO `categories`(`id`, `name`, `value`, `parent`, `lft`, `rght`) 
                VALUES ({$cat_id},'{$cat_title}','',1,0,0)";

                sqlQuery($cat_insert_query);
            }

            $image_path = $sitesDir . "{$site}/documents/{$patient_id}";


            if (!file_exists($image_path)) {
                mkdir($image_path);
            }

            $image_date = date('Y-m-d_H-i-s');

            file_put_contents($image_path . "/" . $image_date . "." . $ext, base64_decode($image_data));

            $hash = sha1_file($image_path . "/" . $image_date . "." . $ext);

            $url = "file://" . $image_path . "/" . $image_date . "." . $ext;

            $size = filesize($url);

            $strQuery4 = "SELECT d.url,d.id
                                FROM `documents` AS d
                                INNER JOIN `categories_to_documents` AS c2d ON d.id = c2d.document_id
                                WHERE d.foreign_id ={$patient_id}
                                AND c2d.category_id ={$cat_id}
                                ORDER BY category_id, d.date DESC";

            $result4 = $db->get_results($strQuery4);

            if ($result4) {

                $file_path = $result4[0]->url;
                $document_id = $result4[0]->id;
                unlink($file_path);

                $strQuery = "UPDATE `documents` SET 
                                        `size`='{$size}',
                                        `url`='{$url}',
                                        `mimetype`='{$mimetype}',
                                        `hash`='{$hash}'
                                        WHERE id = " . $document_id;

                $result = sqlStatement($strQuery);

            } else {


                sqlStatement("lock tables documents read");

                $result = sqlQuery("select max(id)+1 as did from documents");

                sqlStatement("unlock tables");

                if ($result['did'] > 1) {
                    $id = $result['did'];
                }

                $strQuery = "INSERT INTO `documents`( `id`, `type`, `size`, `date`, `url`, `mimetype`, `foreign_id`, `docdate`, `hash`, `list_id`) 
             VALUES ({$id},'{$type}','{$size}','{$date}','{$url}','{$mimetype}',{$patient_id},'{$docdate}','{$hash}','{$list_id}')";

                $result = sqlStatement($strQuery);

                $strQuery1 = "INSERT INTO `categories_to_documents`(`category_id`, `document_id`) VALUES ({$cat_id},{$id})";

                $result1 = sqlStatement($strQuery1);
            }

            if ($result) {
                $xml_array['status'] = 0;
                $xml_array['reason'] = 'The Patient has been updated';
            } else {
                $xml_array['status'] = -2;
                $xml_array['reason'] = 'ERROR: Sorry, there was an error processing your data. Please re-submit the information again.';
            }
        } else {
            $xml_array['status'] = -2;
            $xml_array['reason'] = 'Please select the image';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}

$xml = ArrayToXML::toXml($xml_array, 'Patient');
echo $xml;
?>
