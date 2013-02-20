<?php
/**
 * api/deleteresource.php delete user resources.
 *
 * API is allowed to delete resources for user.
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
require_once 'classes.php';

$xml_string = "";
$xml_string = "<Resource>";

$token = $_POST['token'];
$option_id = $_POST['option_id'];
$list_id = 'ExternalResources';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {
        $strQuery1 = "SELECT notes
                    FROM `list_options`
                    WHERE `list_id` LIKE ? AND 
                    `option_id` LIKE ? ";
        
        $result1= sqlQuery($strQuery1, array($list_id, $option_id));
        $file_path = $result1['notes'];

        $temp_path = explode("userdata", $file_path);
        $relative_path = $sitesDir . "{$site}/documents/userdata" . $temp_path[1];


        if (file_exists($relative_path)) {
            unlink($relative_path);
        }

        $thumb_name = end(explode("/", $temp_path[1]));
        $relative_path_thumb = $sitesDir . "{$site}/documents/userdata/images/thumb/" . $thumb_name;

        if (file_exists($relative_path_thumb)) {
            unlink($relative_path_thumb);
        }

        $strQuery = "DELETE FROM list_options WHERE list_id LIKE '{$list_id}' AND 
                    `option_id` LIKE ?";
		$result = sqlStatement($strQuery, array($option_id));



        if ($result){
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Resource has been deleted</reason>";
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

$xml_string .= "</Resource>";
echo $xml_string;
?>