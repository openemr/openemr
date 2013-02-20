<?php
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ("classes.php");

$xml_string = "";
$xml_string = "<contact>";

$token = $_POST['token'];
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
$image_title = $_POST['imageTitle'];

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

            $strQuery = "INSERT INTO users (username, password, authorized, info, source, title, fname, lname, mname,  upin, see_auth, active, npi, taxonomy, specialty, organization, valedictory, assistant, email, url, street, streetb, city, state, zip, phone, phonew1, phonew2, phonecell, fax, notes ) 
            VALUES ('', '', 0, '', NULL, '" . $title . "', '" . $firstname . "', '" . $lastname . "', '" . $middlename . "', '" . $upin . "', 0, 1, '" . $npi . "', '" . $taxonomy . "', '" . $specialty . "', '" . $organization . "', '" . $valedictory . "', '" . $assistant . "', '" . $email . "', '" . $url . "', '" . $street . "', '" . $streetb . "', '" . $city . "', '" . $state . "', '" . $zip . "', '" . $home_phone . "', '" . $work_phone1 . "', '" . $work_phone2 . "', '" . $phonecell . "', '" . $fax . "', '" . $notes . "' )";
            $result = sqlInsert($strQuery);

            $last_inserted_id = $result;

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

                $strQuery1 = "INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) 
                        VALUES ('ExternalResources','{$image_title}','{$image_title}','0','0','{$last_inserted_id}','','{$notes_url}')";


                $result1 = sqlStatement($strQuery1);
            }


            if ($result) {

                $xml_string .= "<status>0</status>";
                $xml_string .= "<reason>The Contact has been added</reason>";
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