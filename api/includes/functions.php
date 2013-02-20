<?php

/**
 * API functions.
 *
 * Api functions.
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
function xmlsafestring($myString) {
    $myString = str_replace("&", "&amp;", $myString);
    $myString = str_replace("<", "&lt;", $myString);
    $myString = str_replace(">", "&gt;", $myString);
    $myString = str_replace("\"", "&quot;", $myString);
    return $myString;
}

function createToken($userId, $create = true, $device_token = '') {
    $token = md5(uniqid(rand(), 1));

    $query = "SELECT * FROM api_tokens WHERE user_id = ?";
    $token_result = sqlQuery($query, array($userId));

    if ($create || !$token_result) {
        $strQuery = "INSERT INTO api_tokens VALUES('', " . add_escape_custom($userId) . " ,'" . add_escape_custom($token) . "','" . add_escape_custom($device_token) . "' ,'" . date('Y-m-d h:i:s') . "', '',0,0,0,0)";
        $result = sqlInsert($strQuery);
    } else {

        $strQuery = "UPDATE `api_tokens` SET `token` = '" . add_escape_custom($token) . "' ";
        if ($device_token) {
            $strQuery .= ",`device_token` = '" . add_escape_custom($device_token) . "' ";
        }
        $strQuery .= "WHERE `user_id` = ?";
        $result = sqlStatement($strQuery, array($userId));
    }
    if ($result) {
        return $token;
    } else {
        return false;
    }
}

function validateToken($token) {
    $query = "SELECT * FROM api_tokens WHERE token LIKE '{$token}' AND (expire_datetime = 0 OR expire_datetime >= NOW())";
    $result = sqlQuery($query);
    if ($result) {
        return $result['user_id'];
    } else {
        return false;
    }
}

function getUsername($userId) {
    $strQuery = "SELECT username FROM users WHERE id = ?";
    $result = sqlQuery($strQuery, array($userId));

    if ($result) {
        return $result['username'];
    } else {
        return false;
    }
}

//function getPass($userId) {
//    global $db;
//    $strQuery = "SELECT `password_with_token` FROM medmasterusers WHERE id=" . $userId;
//    $result = $db->get_row($strQuery);
//
//    if ($result) {
//        return $result->password_with_token;
//    } else {
//        return false;
//    }
//}

function getPatientsProvider($patient_id) {
    $strQuery = "SELECT `providerID` FROM patient_data WHERE pid=?";
    $result = sqlQuery($strQuery, array($patient_id));

    if ($result) {
        return $result['providerID'];
    } else {
        return false;
    }
}

//function getUserProviderId($userId) {
//    global $db;
//    $strQuery = "SELECT uid FROM medmasterusers WHERE id=" . $userId;
//    $result = $db->get_row($strQuery);
//
//    if ($result) {
//        return $result->uid;
//    } else {
//        return false;
//    }
//}

function getDeviceToken($username) {
    $strQuery = "SELECT t.device_token
                        FROM `medmasterusers` AS mu
                        INNER JOIN `api_tokens` AS t ON mu.id = t.user_id
                        WHERE `username` = ?";
    $result = sqlQuery($strQuery, array($username));

    if ($result) {
        return $result['device_token'];
    } else {
        return false;
    }
}

function getAllBadges($token) {
    $strQuery = "SELECT t.`device_token` , t.`message_badge` , t.`appointment_badge` , t.`labreports_badge` , t.`prescription_badge`
                    FROM `api_tokens` AS t
                    WHERE `token` = ?";
    $result = sqlQuery($strQuery, array($token));

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

function getDeviceTokenBadge($username, $update_badge_type = '') {
    $strQuery = "SELECT t.device_token,t.token,t.`message_badge`,t.`appointment_badge`,t.`labreports_badge`,t.`prescription_badge`
                        FROM `users` AS mu
                        INNER JOIN `api_tokens` AS t ON mu.id = t.id
                        WHERE `username` = ?";
    $result = sqlQuery($strQuery, array($username));

    if ($result) {
        $badge = $result['message_badge'] + $result['appointment_badge'] + $result['labreports_badge'] + $result['prescription_badge'] + 1;
        updateBadge($result['token'], $update_badge_type);
        return array('device_token' => $result['device_token'], 'badge' => $badge);
    } else {
        return false;
    }
}

function updateBadge($token, $update_badge_type) {
    switch ($update_badge_type) {
        case 'message':
            $update = "message_badge = message_badge+1";
            break;
        case 'appointment':
            $update = "appointment_badge = appointment_badge+1";
            break;
        case 'labreport':
            $update = "labreports_badge = labreports_badge+1";
            break;
        case 'prescription':
            $update = "prescription_badge = prescription_badge+1";
            break;
        default :
            return false;
            break;
    }
    $query = "UPDATE `api_tokens` SET {$update} WHERE token = ?";
    $result = sqlStatement($query, array($token));
    return $result;
}

function getProviderUsername($userId) {
    $strQuery = "SELECT username FROM users WHERE id = ?";
    $result = sqlQuery($strQuery, array($userId));

    if ($result) {
        return $result['username'];
    } else {
        return false;
    }
}

function getToken($userId, $emr = "openemr", $password = '', $device_token = '') {
//    $strQuery = "SELECT token FROM tokens WHERE user_id = " . $userId;
//    $result = $db->get_row($strQuery);
//    $query = "UPDATE `medmasterusers` SET `emr`='$emr' WHERE id = $userId";
//    $result = $db->query($query);
//    if (!empty($password)) {
//        $query2 = "UPDATE `medmasterusers` SET `password_with_token`='{$password}' WHERE `id`= {$userId}";
//        $db->query($query2);
//    }
    $token = createToken($userId, false, $device_token);
    if ($token) {
        return $token;
    } else {
        return false;
    }
}

//function getEmr($userId) {
//    global $db;
//    $strQuery = "SELECT emr FROM `medmasterusers` WHERE id=" . $userId;
//    $result = $db->get_row($strQuery);
//
//    if ($result) {
//        return $result->emr;
//    } else {
//        return false;
//    }
//}

function isJson($string) {
// json_decode($string);
    return is_object(json_decode($string));
}

function getSecretkey($userId) {

    $strQuery = "SELECT secret_key FROM `users` WHERE id = ?";
    $result = sqlQuery($strQuery, array($userId));
    if ($result) {
        return $result['secret_key'];
    } else {
        return false;
    }
}

function encrypt($data, $secretKey, $nBits = 256) {
    return AesCtr::encrypt($data, $secretKey, $nBits);
}

function decrypt($data, $secretKey, $nBits = 256) {
    return AesCtr::decrypt($data, $secretKey, $nBits);
}

function getUniqueSecretkey() {
    $character_set_array = array();
    $character_set_array[] = array('count' => 10, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
    $character_set_array[] = array('count' => 10, 'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $character_set_array[] = array('count' => 10, 'characters' => '0123456789');
    $character_set_array[] = array('count' => 2, 'characters' => '!@#$+-*&?:');
    $temp_array = array();
    foreach ($character_set_array as $character_set) {
        for ($i = 0; $i < $character_set['count']; $i++) {
            $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
        }
    }
    shuffle($temp_array);
    return implode('', $temp_array);
}

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = '';
    $size = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, $size - 1)];
    }

    return $str;
}

function getUrl($path) {
    global $openemrUrl;
    global $openemrDirName;
    $temp = explode("/" . $openemrDirName, $path);
    return substr_replace($openemrUrl, "", -1) . $temp[1];
}

function getFormatedDate($string, $time = false) {
    if (preg_match('/[0-9]{10,}/', $string, $matches))
        if ($time)
            return date("Y-m-d H:i:s", ($matches[0] / 1000));
        else
            return date("Y-m-d", ($matches[0] / 1000));
    else
        return false;
}

function getUserData($userId) {
    $return_array = array();
    $return_array['user'] = getUsername($userId);
//    $return_array['emr'] = getEmr($userId);
    $return_array['emr'] = '';
    $return_array['username'] = getUsername($userId);
//    $return_array['password'] = getPass($userId);
    $return_array['password'] = '';
    return $return_array;
}

function createThumbnail($pathToImage, $thumb_name, $thumbWidth = 180, $pathToDest = '/var/www/openemr/sites/default/documents/userdata/images/thumb/') {
    $result = 'Failed';
//    var_dump(file_exists($pathToImage));
//    echo $pathToImage;
    if (file_exists($pathToImage)) {
        $info = pathinfo($pathToImage);

        $extension = strtolower($info['extension']);
        if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {

            switch ($extension) {
                case 'jpg':
                    $img = imagecreatefromjpeg("{$pathToImage}");
                    break;
                case 'jpeg':
                    $img = imagecreatefromjpeg("{$pathToImage}");
                    break;
                case 'png':
                    $img = imagecreatefrompng("{$pathToImage}");
                    break;
                case 'gif':
                    $img = imagecreatefromgif("{$pathToImage}");
                    break;
                default:
                    $img = imagecreatefromjpeg("{$pathToImage}");
            }
// load image and get image size

            $width = imagesx($img);
            $height = imagesy($img);

// calculate thumbnail size
            $new_width = $thumbWidth;
            $new_height = floor($height * ( $thumbWidth / $width ));

// create a new temporary image
            $tmp_img = imagecreatetruecolor($new_width, $new_height);

// copy and resize old image into new image
//            imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            $pathToDestImage = $pathToDest . $thumb_name . '.' . $extension;

//            $pathToDestImage = $thumb_name . '.' . $extension;
//          save thumbnail into a file
//          imagejpeg($tmp_img, "{$pathToDestImage}", 100);
            imagepng($tmp_img, "{$pathToDestImage}", 9);
            $result = $pathToImage;
        } else {
            $result = 'Failed|Not an accepted image type (JPG, PNG, GIF).';
        }
    } else {
        $result = 'Failed|Image file does not exist.';
    }
    return $result;
}

function getDrugTitle($code, $db) {
    $strQuery = "SELECT * 
                        FROM  `codes` 
                        WHERE  `code` LIKE  ?";
    $result = sqlQuery($strQuery, array($code));
    if ($result) {
        return $result['code_text'];
    } else {
        return false;
    }
}

function createHtml($data, $heading = "", $flag = true) {
    $indexes = array_keys($data[0]);
    $html = "<html>
            <head>
                
            </head>
            <body>
            <div id='report_results' style=\" margin-top:10px;\">";
    if ($flag) {
        $html .= "<h3>{$heading}</h3>
            <table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    background-color: #ddd;
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>";
        foreach ($indexes as $index) {
            $html .= "<th style=\"border-bottom: 1px solid black; padding: 5px;\">{$index}</th>";
        }
        $html .= "<tr>
                </thead>
                ";
        $html .= "<tbody>";
        foreach ($data as $items) {
            $html .= "<tr>";
            foreach ($items as $item) {
                $html .= "<td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$item}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>
            <table>";
    } else {
        $html .= $data;
    }

    $html .= "
                    </div>
                </body>
            </html>";
    return $html;
}

function visitSummeryHtml($total_vitals, $vital_data, $total_soap, $soap_data, $total_ros, $ros_data, $total_ros_checks, $ros_data_checks, $medical_problem_count, $medication_count, $allergy_count, $dental_count, $surgery_count, $visit_id) {
    $html = " 
        <h3>Visit# {$visit_id} Summery</h3>
        <table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"><strong>Latest Vitals</strong></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\">Vitals : </th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\">{$total_vitals}</th>
                </tr>
             </thead>
             <tbody>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Temrature:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->temperature}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Blood Pressure:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->bpd}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Pulse:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->pulse}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Oxigen Sat:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->oxygen_saturation}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Height:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->height}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Weight:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->weight}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">BMI:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->BMI}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">BMI status:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->BMI_status}</td>
                </tr>
            </tbody>
            
            </table>
            <table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: -1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"><strong>SOAP notes</strong></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\">SOAP notes : </th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\">{$total_soap}</th>
                </tr>
             </thead>
             <tbody>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Subjective:</td>
                    <td colspan=\"3\" style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$soap_data->subjective}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Objective:</td>
                    <td colspan=\"3\" style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->objective}</td>
                    
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Assessment:</td>
                    <td colspan=\"3\" style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->assessment}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">plan:</td>
                    <td colspan=\"3\" style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$vital_data->plan}</td>
                </tr>
            </tbody>
            </table>
            
<table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: -1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"><strong>Review Of Systems</strong></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"><strong>Review Of Systems Checkboxes</strong></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                </tr>
             </thead>
             <tbody>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Date:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . date("d M Y", strtotime($ros_data->date)) . "</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total ROS:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$total_ros}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Date:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . date("d M Y", strtotime($ros_data_checks->date)) . "</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total ROS Checks:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$total_ros_checks}</td>
                </tr>
                
            </tbody>
            
            </table>
            
<table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: -1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"><strong>Present Illness</strong></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;background-color: #ddd;\"></th>
                </tr>
             </thead>
             <tbody>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total Problems: </td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$medical_problem_count}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total Medications:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$medication_count}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total Allergies:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$allergy_count}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total Dental Issues:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$dental_count}</td>
                </tr>
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">Total Surgries:</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$surgery_count}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\"></td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\"></td>
                </tr>
                
            </tbody>
            
            </table>
";

    function createPdf($html, $pdf, $base64enoded = true) {
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("Haroon");
        $pdf->SetTitle("My Report");
        $pdf->SetSubject("My Report");
//        $pdf->SetKeywords("TCPDF, PDF, example, test, guide");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        if ($base64enoded) {
            $pdf_base64 = $pdf->Output("", "E");
            $temp = explode('filename=""', $pdf_base64);
            return $temp[1];
        } else {
            return $pdf_base64 = $pdf->Output("", "S");
        }
    }

    return createHtml($html, '', false);
}

function curlRequest($url, $body) {

    $method = "POST";
    $headerType = "JSON";
//    $method = strtoupper($method);
//    $headerType = strtoupper($headerType);
//    $url = "api-test.greenwaymedical.com/Integration/RESTv1.0/PrimeSuiteAPIService/Patient/PatientHistoriesGet?api_key=aw6gb6nhejrjfdz7mx2276gt";

    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    if ($method == "GET") {
        curl_setopt($session, CURLOPT_HTTPGET, 1);
    } else {
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_POSTFIELDS, $body);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($session, CURLOPT_HEADER, false);
    if ($headerType == "XML") {
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
    } else {
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', "Content-Type: application/json"));
    }
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    if (preg_match("/^(https)/i", $url))
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($session);
//$result = new SimpleXMLElement(utf8_encode($result));
//    $status = curl_getinfo($session);
//echo "Organization=". $result->response->businessEntity->mainName->organisationName;
    curl_close($session);

    return $result;
}

function notification($deviceToken, $badge = 1, $msg_count = 0, $apt_count = 0, $message = 'Notification!', $passphrase = 'medmasterpro') {
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'includes/apns-dev.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
    $fp = stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$fp) {

        return false;
    }
// Create the payload body
    $body['aps'] = array(
        'alert' => $message,
        'badge' => $badge,
        'sound' => 'default',
        'MessageCount' => $msg_count,
        'AppointmentCount' => $apt_count
    );

// Encode the payload as JSON
    $payload = json_encode($body);

// Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    if (!$result) {
        return false;
    } else {
        return true;
    }
// Close the connection to the server
    fclose($fp);
}

function sendAllNotifications($device_token, $user, $provider_id, $message = 'Notification!', $date = '') {
    $date = $date == '' ? date('Y-m-d') : $date;
    $assignee = $user;
    $sql = "SELECT count( 0 ) AS count
                                        FROM `pnotes`
                                        WHERE deleted != '1'
                                        AND date >= '" . add_escape_custom($date) . " 00:00:00'
                                        AND date <= '" . add_escape_custom($date) . " 24:00:00'
                                        AND assigned_to LIKE ?";

    $result_notification = sqlQuery($sql, array($assignee));

    $count_apt = 0;
    $appointments = fetchAppointments($date, $date, $patient_id = null, $provider_id, $facility_id = null);
    if ($appointments) {
        foreach ($appointments as $key => $appointment) {
            $count_apt++;
        }
    }
    if ($result_notification || $appointments) {
        $count_msg = $result_notification['count'];
        $badge = $count_msg + $count_apt;

        $res = notification($device_token, intval($badge), $count_msg, $count_apt, $message);
        return $res ? $badge : false;
    } else {
        return false;
    }
}

?>