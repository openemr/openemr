<?php
/**
 * api/getinsurancecompany.php retrieve an insurance company.
 *
 * API returns insurance company.
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
            $xml_string .= "<reason>The Insurance Companies Record has been Fetched</reason>\n";

            $xml_string .= "<insurancecompany>";
            foreach ($insurance as $row) {
                $xml_string .="<name>" . $row->name . "</name>";
                $xml_string .="<city>" . $row->address->city . "</city>";
                $xml_string .="<state>" . $row->address->state . "</state>";
            }
            $xml_string .= "<insurancecompany>";
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
?>	