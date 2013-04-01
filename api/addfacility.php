<?php
/**
 * api/addfacility.php add new facility.
 *
 * Api add new facility 
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

    if ($acl_allow) {
        $user = getUsername($userId);
        $strQuery = "INSERT INTO facility (name, phone, fax, street, city, state, postal_code, country_code, federal_ein, service_location, billing_location, accepts_assignment, pos_code, x12_sender_id, attn, domain_identifier, facility_npi, tax_id_type, color, primary_business_entity) 
                                VALUES ('" . add_escape_custom($name) . "',
                                        '" . add_escape_custom($phone) . "',
                                        '" . add_escape_custom($fax) . "',
                                        '" . add_escape_custom($street) . "',
                                        '" . add_escape_custom($city) . "',
                                        '" . add_escape_custom($state) . "',
                                        '" . add_escape_custom($postal_code) . "',
                                        '" . add_escape_custom($country_code) . "',
                                        '" . add_escape_custom($federal_ein) . "',
                                        '" . add_escape_custom($service_location) . "',
                                        '" . add_escape_custom($billing_location) . "', 
                                        '" . add_escape_custom($accepts_assignment) . "', 
                                        '" . add_escape_custom($pos_code) . "', 
                                        '" . add_escape_custom($x12_sender_id) . "', 
                                        '" . add_escape_custom($attn) . "', 
                                        '" . add_escape_custom($domain_identifier) . "',
                                        '" . add_escape_custom($facility_npi) . "',
                                        '" . add_escape_custom($tax_id_type) . "',
                                        '" . add_escape_custom($color) . "',
                                        '" . add_escape_custom($primary_business_entity) . "'
                                        )";
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