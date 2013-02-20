<?php
/**
 * api/addinsurancecompany.php add new insurance company.
 *
 * Api add new Insurance Company
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
require 'classes.php';
require_once("$srcdir/classes/InsuranceCompany.class.php");

$xml_string = "";
$xml_string .= "<insurancecompany>";

$token = $_POST['token'];
$name = $_POST['name'];
$attn = $_POST['attn'];
$address_line1 = $_POST['address_line1'];
$address_line2 = $_POST['address_line1'];
$phone = $_POST['phone'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$cms_id = $_POST['cms_id'];
$freeb_type = $_POST['freeb_type'];
$x12_receiver_id = $_POST['x12_receiver_id'];

if ($userId = validateToken($token)) {

    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    
    
    if ($acl_allow) {

        $insuranceCom = new InsuranceCompany();

        $insuranceCom->set_name($name);
        $insuranceCom->set_attn($attn);
        $insuranceCom->set_address_line1($address_line1);
        $insuranceCom->set_address_line2($address_line1);
        $insuranceCom->set_phone($phone);
        $insuranceCom->set_city($city);
        $insuranceCom->set_state($state);
        $insuranceCom->set_zip($zip);
        $insuranceCom->set_cms_id($cms_id);
        $insuranceCom->set_freeb_type($freeb_type);
        $insuranceCom->set_x12_receiver_id($x12_receiver_id);

        $insuranceCom->persist();

        $xml_string .= "<status>0</status>\n";
        $xml_string .= "<reason>The Insurance has been added</reason>\n";
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</insurancecompany>";
echo $xml_string;
?>