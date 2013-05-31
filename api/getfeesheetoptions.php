<?php
/**
 * api/getfeesheetoptions.php Get fee sheet options.
 *
 * API is allowed to get fee sheet options for new and established patient.
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

$xml_string = "";
$xml_string = "<options>";

$token = $_POST['token'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    
    $acl_allow = acl_check('acct', 'bill', $user);
    if ($acl_allow) {
        $newpatient = '1New Patient';
        $strQuery = "SELECT * FROM fee_sheet_options WHERE fs_category = ? ORDER BY fs_option";

        $result = sqlStatement($strQuery,array($newpatient));
    
        $established = '2Established Patient';
        
        $strQuery1 = "SELECT * FROM fee_sheet_options WHERE fs_category = ? ORDER BY fs_option";
        $result1 = sqlStatement($strQuery1,array($established));
        
       
        if ($result->_numOfRows > 0 || $result1->_numOfRows > 0){ 
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Options Processed successfully</reason>";

            $xml_string .= "<newpatient>\n";
            
           while($res = sqlFetchArray($result)){        
                $xml_string .= "<option>\n";
                
                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($fieldName != 'fs_category' && $fieldName == 'fs_option') {
                        $xml_string .= "<$fieldName>" . substr($rowValue, 1) . "</$fieldName>\n";
                    }
                    if ($fieldName != 'fs_category' && $fieldName != 'fs_option' && $fieldName == 'fs_codes') {
                        $xml_string .= "<$fieldName>" . $rowValue . "</$fieldName>\n";
                    }
                }

                $xml_string .= "</option>\n";
               
            }
            $xml_string .= "</newpatient>";

            $xml_string .= "<establishedpatient>\n";

           while($res = sqlFetchArray($result1)){        
                $xml_string .= "<option>\n";
                
                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue1 = xmlsafestring($fieldValue);

                    if ($fieldName != 'fs_category' && $fieldName == 'fs_option') {
                        $xml_string .= "<$fieldName>" . substr($rowValue1, 1) . "</$fieldName>\n";
                    }
                    if ($fieldName != 'fs_category' && $fieldName != 'fs_option' && $fieldName == 'fs_codes') {
                        $xml_string .= "<$fieldName>" . $rowValue1 . "</$fieldName>\n";
                    }
                }

                $xml_string .= "</option>\n";
            }
            $xml_string .= "</establishedpatient>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could not find results</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</options>";
echo $xml_string;
?>