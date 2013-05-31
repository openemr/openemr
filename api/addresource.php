<?php
/**
 * api/addresource.php add new user's resources.
 *
 * Api add's users resources such as images, url, videos, pdf.
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
$title = $_POST['title'];
$option_id = $_POST['option_id'];
$type = $_POST['type'];
$data = isset($_POST['data']) ? $_POST['data'] : '';
$ext = $_POST['ext'];

$list_id = 'ExternalResources';
$seq = 0;
$is_default = 0;
$notes = '';
$mapping = '';

if ($userId = validateToken($token)) {
    $username = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $username);

    
    if ($acl_allow) {

        $path = $sitesDir . "{$site}/documents/userdata";

        if (!file_exists($path)) {
            mkdir($path);
            mkdir($path . "/images");
            mkdir($path . "/images/thumb/");
            mkdir($path . "/pdf");
            mkdir($path . "/videos");
        } elseif (!file_exists($path . "/images") || !file_exists($path . "/images/thumb/") || !file_exists($path . "/pdf") || !file_exists($path . "/videos")) {

            mkdir($path . "/images");
            mkdir($path . "/images/thumb/");
            mkdir($path . "/pdf");
            mkdir($path . "/videos");
        }

        switch ($type) {
            case 'link':
                $notes = $data;
                break;
            case 'image':
                $image_date_name = date('Y-m-d_H-i-s');
                $image_name = $image_date_name . "." . $ext;

                $image_path = $path . "/images/" . $image_name;

                file_put_contents($image_path, base64_decode($data));
                $thumb_path = $path . "/images/thumb/";

                createThumbnail($image_path, $image_date_name, 250, $thumb_path);
                $notes = $sitesUrl . "{$site}/documents/userdata/images/" . $image_name;
                break;
            case 'pdf':
                $pdf_name = date('Y-m-d_H-i-s') . "." . $ext;
                file_put_contents($path . "/pdf/" . $pdf_name, base64_decode($data));
                $notes = $sitesUrl . "{$site}/documents/userdata/pdf/" . $pdf_name;
                break;
            case 'video':
                $video_name = date('Y-m-d_H-i-s') . "." . $ext;
                file_put_contents($path . "/videos/" . $video_name, base64_decode($data));
                $notes = $sitesUrl . "{$site}/documents/userdata/videos/" . $video_name;
                break;
        }


        $select_query = "SELECT *  FROM `list_options` 
        WHERE `list_id` LIKE 'lists' AND `option_id` LIKE '" . add_escape_custom($list_id) . "' AND `title` LIKE '" . add_escape_custom($list_id) . "'";

        $result_select = sqlQuery($select_query);
        $result1 = true;
        if (!$result_select) {
            $insert_list = "INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) 
                            VALUES ( 'lists','" . add_escape_custom($list_id) . "','" . add_escape_custom($list_id) . "', '0','1', '0')";
            $result1 = sqlStatement($insert_list);
        }


        $strQuery = "INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) 
                        VALUES (
                        '" . add_escape_custom($list_id) . "',
                        '" . add_escape_custom($option_id) . "',
                        '" . add_escape_custom($title) . "',
                        '" . add_escape_custom($seq) . "',
                        '" . add_escape_custom($is_default) . "',
                        '" . add_escape_custom($userId) . "',
                        '" . add_escape_custom($mapping) . "',
                        '" . add_escape_custom($notes) . "')";


        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_array['status'] = "0";
            $xml_array['reason'] = "The Resource has been added";
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


$xml = ArrayToXML::toXml($xml_array, 'Resource');
echo $xml;
?>