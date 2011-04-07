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
    if (ereg_replace("[:space:]", "", $firstName) != "")
        $where .= "fname = '".add_escape_custom($firstName)."' " ;

    if (ereg_replace("[:space:]", "", $lastName) != "") {
        if ($where != "") $where .= "AND ";
        $where .= "lname = '".add_escape_custom($lastName)."' " ;
    }

//    if (ereg_replace("[:space:]", "", $middleName) != ""){
//        if ($where != "") $where .= "AND ";
//        $where .= "mname = '".add_escape_custom($middleName)."' " ;
//    }
    
    if (ereg_replace("[:space:]", "", $dob) != ""){
        if ($where != "") $where .= "AND ";
        $where .= "DOB = DATE_FORMAT('".add_escape_custom($dob)."', '%Y-%m-%d') " ;
    }

    if (ereg_replace("[:space:]", "", $gender) != "") {
        if ($gender =="F") $sex = "Female";
        if ($gender =="M") $sex = "Male";
        
        if(isset($sex))
        {
            if ($where != "") $where .= "AND ";
            $where .= "(sex = '".add_escape_custom($sex)."' OR sex = '" . add_escape_custom($gender) ."')" ;
        }
    }

    if (ereg_replace("[:space:]", "", $ssn) != ""){
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

/**
 * identify the lab ordering provider and return the userid. 
 * 
 * parameters are populated from the lab result
 * 
 * @param <type> $id
 * @param <type> $lastName
 * @param <type> $firstName
 * @return <type> user.id
 *
function lab_exchange_match_provider($id, $lastName, $firstName) {
    $sql = "SELECT user_id from laboratory_providers WHERE ";
    $where = "";

    if (ereg_replace("[:space:]", "", $lastName) != "")
        $where .= "provider_lname = '".add_escape_custom($lastName)."' " ;

    if (ereg_replace("[:space:]", "", $firstName) != "") {
        if ($where != "") $where .= "AND ";
        $where .= "provider_fname = '".add_escape_custom($firstName)."' " ;
    }

    if (ereg_replace("[:space:]", "", $id) != "") {
        if ($where != "") $where .= "AND ";
        $where .= "provider_id = '".add_escape_custom($id)."' " ;
    }

    if ($where == "") {
        return false;
    }
    else {
        $res = sqlQuery($sql . $where);
        if ($res['user_id']) {
//            echo "found id: " . $res['user_id'];
            return $res['user_id'];
        }
        else {
//            echo "found no id using " . $lastName .", " . $firstName .", " . $id;
            return false;
        }
    }
}
*/

/**
 * identify the lab ordering provider and return the userid.
 *
 * parameters are populated from the lab result
 *
 * @param <type> $id
 * @param <type> $lastName
 * @param <type> $firstName
 * @return <type> user.id if npi exists in users table; false if npi cannot be found
 */
function lab_exchange_match_provider($npi)
{
    $npi = trim($npi);

    if(!empty($npi))
    {
        $sql = "SELECT id from users WHERE npi = " . $npi;
        $res = sqlQuery($sql);
    }
    return isset($res['id']) ? $res['id'] : false;
}

/**
 * process the lab facility information
 *
 * @param <type> $facilities - potentially multiple facilities for performing lab info
 * @return <type> facilityID
 */
function processFacility($facilities)
{
        // Loop through the facility
        // There can be several facilities.
        // Also there is no good place to store a reference to users.id for facility info lookup,
        // so I'm concatenating the table id onto the lab id prior to the addition of a colon

        $facilityId = null;

        foreach ($facilities as $facility) {
            // Access facility fields
            $users_id = "";

            if(!$users_id = getLabFacility($facility))
            {
                $users_id = addNewLabFacility($facility);
            }
            $facilityId[] = $facility->FacilityID . "_" . $users_id;   //=>procedure_result.facility

        }

        if (count($facilityId) > 0) {
            $str_facilityId = implode(":", $facilityId);
        }
        return $str_facilityId;
}
/**
 *
 * @param <type> $facility
 * @return <type> returns the user id for the lab facility record if it exists in the database, false otherwise.
 */
function getLabFacility($facility)
{
    $query = "select id from users where fname = '" . trim($facility->FacilityDirectorFirstName) . "' AND " .
                        "lname = '" . trim($facility->FacilityDirectorLastName) . "' AND " .
                        "street = '" . trim($facility->FacilityAddress) . "' AND " .
                        "city = '" . trim($facility->FacilityCity) . "' AND " .
                        "state = '" . trim($facility->FacilityState) . "' AND " .
                        "zip = " . trim($facility->FacilityZip) . " AND " .
                        "organization = '" . trim($facility->FacilityName) ."'";

    $res = sqlStatement($query);
    $result = sqlFetchArray($res);

    return isset($result['id']) ? $result['id'] : false;
}
/**
 *
 * @param <type> $facilityID
 * @return <type> the result set, false if the input is malformed
 */
function getFacilityInfo($facilityID)
{
    // facility ID will be in the format XX_YY, where XX is the lab-assigned id, Y is the user.id record representing that lab facility, and the _ is a divider.
    $facility = explode("_", $facilityID);

    if(count($facility) > 1)
    {
        $query = "select
                title,fname,lname,street,city,state,zip,organization,phone
                from users where id = " . $facility[1];

        $res = sqlStatement($query);
        return sqlFetchArray($res);
    }
    return false;
}
/**
 *
 * @param <type> $facility
 * @return <type> returns the id
 */
function addNewLabFacility($facility)
{
    $query = "INSERT INTO users ( " .
    "username, password, authorized, info, source, " .
    "title, fname, lname, mname,  " .
    "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, " .
    "specialty, organization, valedictory, assistant, billname, email, url, " .
    "street, streetb, city, state, zip, " .
    "street2, streetb2, city2, state2, zip2," .
    "phone, phonew1, phonew2, phonecell, fax, notes, abook_type "            .
    ") VALUES ( "                        .
    "'', "                               . // username
    "'', "                               . // password
    "0, "                                . // authorized
    "'', "                               . // info
    "NULL, "                             . // source
    "'" . trim($facility->FacilityDirectorTitle)         . "', " .
    "'" . trim($facility->FacilityDirectorFirstName)         . "', " .
    "'" . trim($facility->FacilityDirectorLastName)         . "', " .
    "'', " .
    "'', " .
    "'', "                               . // federaldrugid
    "'', " .
    "'', "                               . // facility
    "0, "                                . // see_auth
    "1, "                                . // active
    "'', " .
    "'', " .
    "'', " .
    "'" . trim($facility->FacilityName)  . "', " .
    "'', " .
    "'', " .
    "'', "                               . // billname
    "'', " .
    "'', " .
    "'" . trim($facility->FacilityAddress)        . "', " .
    "'', " .
    "'" . trim($facility->FacilityCity)          . "', " .
    "'" . trim($facility->FacilityState)         . "', " .
    "'" . trim($facility->FacilityZip)           . "', " .
    "'', " .
    "'', " .
    "'', " .
    "'', " .
    "'', " .
    "'" . trim($facility->FacilityPhone)         . "', " .
    "'', " .
    "'', " .
    "'', " .
    "'', " .
    "'', " .
    "'ord_lab'"  .
    ")";

    return sqlInsert($query);
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

function formatPhone($phone)
{
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if(strlen($phone) == 7)
                return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
        elseif(strlen($phone) == 10)
                return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
        else
                return $phone;
}

?>
