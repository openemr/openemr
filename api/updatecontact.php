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


if ($userId = validateToken($token)) {
    
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'users', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    
    if ($acl_allow) {
        $strQuery = 'UPDATE users SET ';
        $strQuery .= ' password = "' . $password . '",';
        $strQuery .= ' authorized = "' . $authorized . '",';
        $strQuery .= ' info = "' . $info . '",';
        $strQuery .= ' source = "' . $source . '",';
        $strQuery .= ' title = "' . $title . '",';
        $strQuery .= ' fname = "' . $firstname . '",';
        $strQuery .= ' lname = "' . $lastname . '",';
        $strQuery .= ' mname = "' . $middlename . '",';
        $strQuery .= '  upin = "' . $upin . '",';
        $strQuery .= ' see_auth = "' . $see_auth . '",';
        $strQuery .= ' active = "' . $active . '",';
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
        $strQuery .= ' WHERE id = ' . $id;

        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Contact has been updated</reason>";
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

$xml_string .= "</contact>";
echo $xml_string;
?>