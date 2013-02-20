<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ("classes.php");

$xml_string = "";
$xml_string = "<facility>";

$token = $_POST['token'];

$name = $_POST['name'];
$phone = $_POST['phone'];
$fax = $_POST['fax'];
$street = $_POST['street'];
$city = $_POST['city'];
$state = $_POST['state'];
$postal_code = $_POST['postal_code'];
$country_code = $_POST['country_code'];
$federal_ein = $_POST['federal_ein'];
$service_location = $_POST['service_location'];
$billing_location = $_POST['billing_location'];
$accepts_assignment = $_POST['accepts_assignment'];
$pos_code = $_POST['pos_code'];
$x12_sender_id = $_POST['x12_sender_id'];
$attn = $_POST['attn'];
$domain_identifier = $_POST['domain_identifier'];
$facility_npi = $_POST['facility_npi'];
$tax_id_type = $_POST['tax_id_type'];
$color = $_POST['color'];
$primary_business_entity = 0;

if ($userId = validateToken($token)) {

    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;

    if ($acl_allow) {

        $user = getUsername($userId);
        $strQuery = "INSERT INTO facility (name, phone, fax, street, city, state, postal_code, country_code, federal_ein, service_location, billing_location, accepts_assignment, pos_code, x12_sender_id, attn, domain_identifier, facility_npi, tax_id_type, color, primary_business_entity) 
        VALUES ('" . $name . "', '" . $phone . "', '" . $fax . "', '" . $street . "', '" . $city . "', '" . $state . "', '" . $postal_code . "', '" . $country_code . "', '" . $federal_ein . "', '" . $service_location . "', '" . $billing_location . "', '" . $accepts_assignment . "', '" . $pos_code . "', '" . $x12_sender_id . "', '" . $attn . "', '" . $domain_identifier . "', '" . $facility_npi . "', '" . $tax_id_type . "', '" . $color . "', '" . $primary_business_entity . "')";
        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Facility has been added</reason>";
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

$xml_string .= "</facility>";
echo $xml_string;
?>