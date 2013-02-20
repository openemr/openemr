<?php
/**
 * api/addpatientdocument.php add new patient's document.
 *
 * Api add's patient document againt a particular category as in openemr
 * 
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
