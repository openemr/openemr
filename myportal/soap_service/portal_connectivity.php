<?php
/**
 * Offsite Portal connection function library.
 *
 * Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Eldho Chacko <eldho@zhservices.com>
 * @author  Vinish K <vinish@zhservices.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
function portal_connection(){
    global $credentials;
    $password 	= $GLOBALS['portal_offsite_password'];
    $randkey	= '';  
    $timminus = date("Y-m-d H:m",(strtotime(date("Y-m-d H:m"))-7200)).":00";  
    sqlStatement("DELETE FROM audit_details WHERE audit_master_id IN(SELECT id FROM audit_master WHERE type=5 AND created_time<=?)",array($timminus));
    sqlStatement("DELETE FROM audit_master WHERE type=5 AND created_time<=?",array($timminus));  
    do{
        $randkey 	= substr(md5(rand().rand()), 0, 8);      
        $res 	= sqlStatement("SELECT * FROM audit_details WHERE field_value = ?",array($randkey));
        $cnt 	= sqlNumRows($res);
    }
    while($cnt>0); 
    $password 	= sha1($password.gmdate("Y-m-d H").$randkey);  
    $grpID 	= sqlInsert("INSERT INTO audit_master SET type=5");
    sqlStatement("INSERT INTO audit_details SET field_value=? , audit_master_id=?",array($randkey,$grpID)); 
    $credentials 	= array($GLOBALS['portal_offsite_username'],$password,$randkey);    
    //CALLING WEBSERVICE ON THE PATIENT-PORTAL 
    $client 	= new SoapClient(null, array(
            'location' => $GLOBALS['portal_offsite_address_patient_link']."/webservice/webserver.php",
            'uri'      => "urn://portal/req"
        )
    );
    return $client;
}
?>
