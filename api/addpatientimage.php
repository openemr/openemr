<?php
/**
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
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

        $strQuery = "INSERT INTO `documents`( `id`, `type`, `size`, `date`, `url`, `mimetype`, `foreign_id`, `docdate`, `hash`, `list_id`) 
                        VALUES (
                               '" . add_escape_custom($id) . "',
                               '" . add_escape_custom($type) . "',
                               '" . add_escape_custom($size) . "',
                               '" . add_escape_custom($date) . "',
                               '" . add_escape_custom($url) . "',
                               '" . add_escape_custom($mimetype) . "',
                               " . add_escape_custom($patient_id) . ",
                               '" . add_escape_custom($docdate) . "',
                               '" . add_escape_custom($hash) . "',
                               '" . add_escape_custom($list_id) . "')";
        $result = sqlStatement($strQuery);

        $strQuery1 = "INSERT INTO `categories_to_documents`(`category_id`, `document_id`) VALUES (" . add_escape_custom($cat_id)." , " . add_escape_custom($id).")";

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
