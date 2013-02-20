<?php
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ("classes.php");

$xml_string = "";
$xml_string = "<Contact>";

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

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);

    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('admin', 'users', $username);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;


    if ($acl_allow) {
        $provider_id = getUserProviderId($userId);
        $strQuery = "INSERT INTO users ( username, password, authorized, info, source, title, fname, lname, mname,  upin, see_auth, active, npi, taxonomy, specialty, organization, valedictory, assistant, email, url, street, streetb, city, state, zip, phone, phonew1, phonew2, phonecell, fax, notes ) 
                    VALUES ( '" . $provider_id . "', '', 0, '', NULL, '" . $title . "', '" . $firstname . "', '" . $lastname . "', '" . $middlename . "', '" . $upin . "', 0, 1, '" . $npi . "', '" . $taxonomy . "', '" . $specialty . "', '" . $organization . "', '" . $valedictory . "', '" . $assistant . "', '" . $email . "', '" . $url . "', '" . $street . "', '" . $streetb . "', '" . $city . "', '" . $state . "', '" . $zip . "', '" . $home_phone . "', '" . $work_phone1 . "', '" . $work_phone2 . "', '" . $phonecell . "', '" . $fax . "', '" . $notes . "' )";
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Contact has been added</reason>";
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

$xml_string .= "</Contact>";
echo $xml_string;
?>