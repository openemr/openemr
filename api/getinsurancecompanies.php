<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');
require_once("$srcdir/classes/InsuranceCompany.class.php");

$xml_string = "";
$xml_string = "<insurancecompanies>";

$token = $_POST['token'];
$insuranceCom = new InsuranceCompany();
$insurance = $insuranceCom->insurance_companies_factory();

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);

    if ($acl_allow) {


        if (!empty($insurance)) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>The Insurance Companies Record has been fetched</reason>\n";

            foreach ($insurance as $row) {
                $xml_string .= "<insurancecompany>";
                $xml_string .="<id>" . $row->id . "</id>";
                $xml_string .="<name>" . $row->name . "</name>";
                $xml_string .="<city>" . $row->address->city . "</city>";
                $xml_string .="<state>" . $row->address->state . "</state>";
                $xml_string .= "</insurancecompany>";
            }
        } else {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason></reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</insurancecompanies>";
echo $xml_string;