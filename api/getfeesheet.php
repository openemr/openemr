<?php
/**
 * api/getfeecheet.php retrieve feesheet.
 *
 * API fetch patient feesheet.
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

function supervisorName($supervisor_id){
    $strQuery = "SELECT fname, lname FROM users WHERE id =?";
    $result = sqlQuery($strQuery,array($supervisor_id));
    return $result['fname'] . " " . $result['lname'];
}

$xml_string = "";
$xml_string = "<feesheet>";

$token = $_POST['token'];
$visit_id = $_POST['visit_id'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    
    $acl_allow = acl_check('acct', 'bill', $user);
    if ($acl_allow) {
        $strQuery = "SELECT b.id, b.authorized, b.fee, b.code_type, b.code, b.modifier, b.units, b.justify, b.provider_id, 
				fe.supervisor_id, u.fname, u.lname, pd.pricelevel, c.code_text
          		FROM billing AS b
				LEFT JOIN users AS u ON u.id = b.provider_id
          		LEFT JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter
				LEFT JOIN codes AS c ON c.code = b.code
          		LEFT JOIN patient_data AS pd ON pd.pid = b.pid WHERE b.activity = 1 AND b.encounter = ?";

        $result = sqlStatement($strQuery,array($visit_id));
        
        if ($result->_numOfRows > 0) {            
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Feesheet records has been fetched.</reason>";
            $count=0;
            while($res = sqlFetchArray($result)){
                $xml_string .= "<item>\n";

                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($fieldName == 'fname' || $fieldName == 'lname') {
                        
                    } else {
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                }
                $supervisor_id = $res['supervisor_id'];
                $fname = $res['fname'];
                $lname = $res['lname'];
                $xml_string .= "<provider>" . $fname . " " . $lname . "</provider>\n";
                $xml_string .= "<supervisor>\n" . supervisorName($supervisor_id) . "</supervisor>\n";
                $xml_string .= "</item>\n";
                $count++;
            }
        } else {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>No records found.</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</feesheet>";
echo $xml_string;
?>