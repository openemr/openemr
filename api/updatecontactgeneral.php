<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<contact>";

$token = $_POST['token'];
$id = $_POST['id'];
$title = $_POST['title'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$middlename = $_POST['middlename'];
$upin = $_POST['upin'];
$npi = $_POST['npi'];
$taxonomy = $_POST['taxonomy'];
$specialty = $_POST['specialty'];
$organization = $_POST['organization'];
$valedictory = $_POST['valedictory'];
$assistant = $_POST['assistant'];
$email = $_POST['email'];
$url = $_POST['url'];
$street = $_POST['street'];
$streetb = $_POST['streetb'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$home_phone = $_POST['home_phone'];
$work_phone1 = $_POST['work_phone1'];
$work_phone2 = $_POST['work_phone2'];
$mobile = $_POST['mobile'];
$fax = $_POST['fax'];
$notes = $_POST['notes'];
$image_data = $_POST['imageData'];
$image_title_old = $_POST['imageTitleOld'];
$image_title_new = $_POST['imageTitleNew'];


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

     $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    
    if ($acl_allow) {


        if ($firstname == '' || $lastname == '' || $email == '') {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Some fields are empty</reason>";
        } else {

            $strQuery = 'UPDATE users SET ';
            $strQuery .= ' info = "' . $info . '",';
            $strQuery .= ' source = "' . $source . '",';
            $strQuery .= ' title = "' . $title . '",';
            $strQuery .= ' fname = "' . $firstname . '",';
            $strQuery .= ' lname = "' . $lastname . '",';
            $strQuery .= ' mname = "' . $middlename . '",';
            $strQuery .= ' upin = "' . $upin . '",';
            $strQuery .= ' see_auth = "' . $see_auth . '",';
            $strQuery .= ' npi = "' . $npi . '",';
            $strQuery .= ' taxonomy = "' . $taxonomy . '",';
            $strQuery .= ' specialty = "' . $specialty . '",';
            $strQuery .= ' organization = "' . $organization . '",';
            $strQuery .= ' valedictory = "' . $valedictory . '",';
            $strQuery .= ' assistant = "' . $assistant . '",';
            $strQuery .= ' email = "' . $email . '",';
            $strQuery .= ' url = "' . $url . '",';
            $strQuery .= ' street = "' . $street . '",';
            $strQuery .= ' streetb = "' . $streetb . '",';
            $strQuery .= ' city = "' . $city . '",';
            $strQuery .= ' state = "' . $state . '",';
            $strQuery .= ' zip = "' . $zip . '",';
            $strQuery .= ' phone = "' . $home_phone . '",';
            $strQuery .= ' phonew1 = "' . $work_phone1 . '",';
            $strQuery .= ' phonew2 = "' . $work_phone2 . '",';
            $strQuery .= ' phonecell = "' . $mobile . '",';
            $strQuery .= ' fax = "' . $fax . '",';
            $strQuery .= ' notes = "' . $notes . '"';
            $strQuery .= ' WHERE username = \'\' AND password = \'\' AND id = ' . $id;



            $result = sqlStatement($strQuery);


            if ($image_data) {

                $imageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
                if ($_SERVER["SERVER_PORT"] != "80") {
                    $imageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
                } else {
                    $imageURL .= $_SERVER["SERVER_NAME"];
                }


                $path = $sitesDir . "{$site}/documents/userdata";

                if (!file_exists($path)) {
                    mkdir($path);
                    mkdir($path . "/contactimages");
                } elseif (!file_exists($path . "/contactimages")) {
                    mkdir($path . "/contactimages");
                }

                $image_name = date('Y-m-d_H-i-s') . ".png";
                file_put_contents($path . "/contactimages/" . $image_name, base64_decode($image_data));

                $notes_url = $sitesUrl . "{$site}/documents/userdata/contactimages/" . $image_name;

                $strQuery2 = "SELECT * FROM `list_options` 
                            WHERE `list_id` = 'ExternalResources' AND 
                                   `option_id` = '{$image_title_old}'";
                $result2 = $db->get_results($strQuery2);


                if ($result2) {
                    $old_image_path = $result2[0]->notes;
                    $old_image_name = basename($old_image_path);

                    if (file_exists($path . "/contactimages/" . $old_image_name)) {
                        unlink($path . "/contactimages/" . $old_image_name);
                    }


                    $strQuery1 = "UPDATE `list_options` SET `notes`='{$notes_url}',
                                                        `option_id` = '{$image_title_new}',
                                                        `title` = '{$image_title_new}'
                                                 WHERE `list_id` = 'ExternalResources' AND 
                                                    `option_id` = '{$image_title_old}'";

                } else {

                    $strQuery1 = "INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) 
                        VALUES ('ExternalResources','{$image_title_new}','{$image_title_new}','0','0','{$id}','','{$notes_url}')";
                }

                $result1 = sqlStatement($strQuery1);
            }

            if ($result) {
                $xml_string .= "<status>0</status>";
                $xml_string .= "<reason>The Contact has been updated</reason>";
            } else {
                $xml_string .= "<status>-1</status>";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
            }
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</contact>";
echo $xml_string;
?>