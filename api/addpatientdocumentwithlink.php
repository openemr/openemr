<?php
/**
 * api/addpatientdocumentwithlink.php add new patient's document.
 *
 * Api add's patient document againt a particular category with file url.
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
require('classes.php');
require_once("$srcdir/documents.php");
$xml_array = array();

$token = $_POST['token'];

$patient_id = $_POST['patientId'];
$docdate = $_POST['docDate'];
$list_id = !empty($_POST['listId']) ? $_POST['listId'] : 0;
$cat_id = $_POST['categoryId'];
$link = $_POST['link'];
$ext = $_POST['docType'];
$mimetype = $_POST['mimeType'];

$image_content = file_get_contents($link);

if ($userId = validateToken($token)) {
    $provider_id = getPatientsProvider($patient_id);
    $provider_username = getProviderUsername($provider_id);
            
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'docs', $user);

   

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

            $image_date = date('YmdHis');
            $image_root_path = $image_path . "/" . $image_date . "." . $ext;
            file_put_contents($image_root_path , $image_content);
            
            $res = addNewDocument($image_date. "." . $ext,'image/png',$image_root_path,0,filesize($image_root_path),$userId,$patient_id,$cat_id,$higher_level_path='',$path_depth='1');

            

                $lab_report_catid = document_category_to_id("Lab Report");
                
                if ($cat_id == $lab_report_catid) {
                    $device_token_badge = getDeviceTokenBadge($provider_username, 'labreport');
                    $badge = $device_token_badge ['badge'];
                    $deviceToken = $device_token_badge ['device_token'];
                    if ($deviceToken) {
                        $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Labreport Notification!');
                    }
                }


                if ($res) {
                    $xml_array['status'] = "0";
                    $xml_array['reason'] = "Document added successfully";
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
