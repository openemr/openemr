<?php
// Copyright (C) 2010 OpenEMR Support LLC   
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");

// Find and match the patient with the incoming lab report.
// return patient pid if matched else return false
function lab_exchange_match_patient($externalId, $firstName, $middleName, $lastName, $dob, $gender, $ssn, $address) {
    $sql = "SELECT pid from patient_data WHERE ";
    $where = "";
    /* 
    // Search for pid and return if pid match with $externalId(from lab API)
    if ($externalId != "") {
        $where .= "pid = '".add_escape_custom($externalId)."' " ;
        $res = sqlQuery($sql . $where);
        if ($res['pid']) {
            return $res['pid'];
        }
        else {
            $where = "";
        }
    }
    */
    
    // If empty $externalId or externalId no matched
    if ($firstName != "")
        $where .= "fname = '".add_escape_custom($firstName)."' " ;
    
    if ($lastName != "") {
        if ($where != "") $where .= "AND ";
        $where .= "lname = '".add_escape_custom($lastName)."' " ;
    }
    
    if ($middleName != ""){
        if ($where != "") $where .= "AND ";
        $where .= "mname = '".add_escape_custom($middleName)."' " ;
    }
    
    if ($dob != ""){
        if ($where != "") $where .= "AND ";
        $where .= "DOB = DATE_FORMAT('".add_escape_custom($dob)."', '%Y-%m-%d') " ;
    }
    
    if ($gender != "") {
        if ($gender =="F") $sex = "Female";
        if ($gender =="M") $sex = "Male";
        if ($where != "") $where .= "AND ";
        $where .= "(sex = '".add_escape_custom($sex)."' OR sex = '" . add_escape_custom($gender) ."')" ;
    }
    
    if ($ssn != ""){
        if ($where != "") $where .= "AND ";
        // Change to xxx-xx-xxxx format.
        $ss = substr($ssn,0,3)."-".substr($ssn,3,2)."-".substr($ssn,5);
        $where .= "(ss = '".add_escape_custom($ssn)."' OR ss = '".add_escape_custom($ss)."' OR ss = '')";
    }
        
    if ($where == "") {
        return false;
    }
    else {
        $res = sqlQuery($sql . $where);
        if ($res['pid']) {
            return $res['pid'];
        }
        else {
            return false;
        }
    }
}

// Find and match the providers for access to the incoming lab report.
// return: - provider/user id or - false
function lab_exchange_match_provider($lastName, $firstName) {
    $sql = "SELECT id from users WHERE ";
    $where = "";
    
    if ($lastName != "")
        $where .= "lname = '".add_escape_custom($lastName)."' " ;
    
    if ($lastName != "") {
        if ($where != "") $where .= "AND ";
        $where .= "fname = '".add_escape_custom($firstName)."' " ;
    }
        
    if ($where == "") {
        return false;
    }
    else {
        $res = sqlQuery($sql . $where);
        if ($res['id']) {
            return $res['id'];
        }
        else {
            return false;
        }
    }
}

function mapReportStatus($stat) {
    $return_status = $stat;
    
    // if($stat == "")
        // $return_status = "unknown";
    if($stat=="F" || $stat=="f")
        $return_status = "final";
    if($stat=="P" || $stat=="p")
        $return_status = "prelim";
    if($stat=="X" || $stat=="x")
        $return_status = "cancel";
    if($stat=="C" || $stat=="c")
        $return_status = "correct";
    
    return $return_status;
}

function mapResultStatus($stat) {
    $return_status = $stat;
    
    // if($stat == "")
         // $return_status = "unknown";
    if($stat=="F" || $stat=="f")
         $return_status = "final";
    if($stat=="P" || $stat=="p")
         $return_status = "prelim";
    if($stat=="X" || $stat=="x")
         $return_status = "cancel";
    if($stat=="C" || $stat=="c")
         $return_status = "correct";
    if($stat=="I" || $stat=="i")
        $return_status = "incomplete";
    
    return $return_status;
}

function mapAbnormalStatus($stat) {
    $return_status = $stat;
    
    // if($stat == "")
        // $return_status = "unknown";
    if($stat=="L" || $stat=="l")
         $return_status = "low";
    if($stat=="H" || $stat=="h")
         $return_status = "high";
    if($stat=="LL" || $stat=="ll")
         $return_status = "low";
    if($stat=="HH" || $stat=="hh")
         $return_status = "high";
    if($stat=="<")
         $return_status = "low";
    if($stat==">")
         $return_status = "high";
    if($stat=="A" || $stat=="a")
        $return_status = "yes";
    
    return $return_status;
}

?>
