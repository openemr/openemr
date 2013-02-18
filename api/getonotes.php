<?php
/**
 * api/getcontactgeneral.php fetch user's onotes.
 *
 * API to retrivew user's onotes.
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
require_once 'classes.php';
include_once("$srcdir/onotes.inc");

$xml_string = "";
$xml_string .= "<officenotes>";

$token = $_POST['token'];
$body = $_POST['body'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {
        $result = getOnoteByDate("", 1, "date,body,user", "all", 0);

        $xml_string .= "<status>0</status>\n";
        $xml_string .= "<reason>Success processing insurance companies records</reason>\n";

        foreach ($result as $iter) {

            $xml_string .= "<officenote>\n";
            $xml_string .= "<date>$iter[date]</date>\n";
            $xml_string .= "<user>$iter[user]</user>\n";
            $xml_string .= "<body>$iter[body]</body>\n";
            $xml_string .= "</officenote>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</officenotes>";
echo $xml_string;
?>