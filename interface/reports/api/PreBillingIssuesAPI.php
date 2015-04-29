<?php

// Copyright (C) 2015 Tony McCormick <tony@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__)."/../../globals.php");
require_once("$srcdir/sql.inc");

class PreBillingIssuesAPI {
    
    const ENCOUNTERS_NO_RENDERING_PROVIDER =
    "SELECT pdat.lname as 'LName'
	, pdat.fname as 'FName'
	, pdat.pid as 'Pt ID'
        , pdat.pubpid as 'Pub Pt ID'
        , pdat.DOB as 'Pt DOB'
	, fenc.date AS 'Encounter Date'
	, fenc.encounter AS 'Enc ID'
	, concat('No Enc Rendering Provider') AS 'Rendering Provider'
    FROM form_encounter fenc
    inner join patient_data pdat on fenc.pid = pdat.pid 
    WHERE provider_id = 0";

    const SUBSCRIBER_MISSING_DATA_FIELDS = 
    "SELECT pdat.lname as 'LName'
            , pdat.fname as 'FName'
            , insdat.pid as 'Pt ID'
            , pdat.pubpid as 'Pub Pt ID'
            , pdat.DOB as 'Pt DOB'
            , subscriber_relationship as 'Subscriber Relationship'
            , subscriber_lname as 'Subscriber Last Name'
            , concat('Ins type ',insdat.type) AS 'Insurance Type'
            , insdat.subscriber_street AS 'Pt Address Street'
            , insdat.subscriber_postal_code AS 'Pt Address Code'
            , insdat.subscriber_city AS 'Pt Address City'
            , insdat.subscriber_state AS 'Pt Address State'
    FROM insurance_data insdat
    inner join patient_data pdat on insdat.pid = pdat.pid 
    WHERE subscriber_relationship <> ''
    AND (
            insdat.subscriber_lname = '' OR
            insdat.subscriber_DOB = '' OR
            insdat.subscriber_street = '' OR
            insdat.subscriber_postal_code = '' OR
            insdat.subscriber_city = '' OR
            insdat.subscriber_state = ''
            )";
	
    const INSURANCE_NO_SUBSCRIBER_RELATIONSHIP = 
    "SELECT pdat.lname as 'LName'
            , pdat.fname as 'FName'
            , insdat.pid as 'Pt ID'
            , pdat.pubpid as 'Pub Pt ID'
            , pdat.DOB as 'Pt DOB'
            , concat('Ins type ',insdat.type) AS 'Insurance Type'
    FROM insurance_data insdat
    inner join patient_data pdat on insdat.pid = pdat.pid 
    WHERE subscriber_relationship = ''
    AND (
            insdat.provider <> '' OR
            insdat.provider > 0
            )";
    
    const INSURANCE_MISSING_FIELDS = 
    "SELECT pdat.lname as 'LName'
            , pdat.fname as 'FName'
            , insdat.pid as 'Pt ID'
            , pdat.pubpid as 'Pub Pt ID'
            , pdat.DOB as 'Pt DOB'
            , concat('Ins type ',insdat.type) AS 'Insurance Type'
            , insdat.plan_name AS 'Plan Name'
            , insdat.date AS 'Effective Date'
            , insdat.policy_number AS 'Policy Number'
            , insdat.group_number AS 'Group Number'
    FROM insurance_data insdat
    inner join patient_data pdat on insdat.pid = pdat.pid 
    WHERE insdat.provider > '' AND
            (
                insdat.plan_name = '' OR
                insdat.date = '000-00-00' OR
                insdat.policy_number = '' OR
                insdat.group_number = ''
            )";

    function __construct() {
    }
    
    function doQuery($query) {
        $result = array();
        $stmt = sqlStatement($query);
        while ($row = sqlFetchArray($stmt)) {
            array_push($result, $row);
        }
        
        if ( !$result || sizeof($result) < 1 ) {
            return null;
        }
        return $result;   
    }

    function findEncountersMissingProvider() {
        return $this->doQuery( self::ENCOUNTERS_NO_RENDERING_PROVIDER );
    }
    
    function findPatientInsuranceMissingSubscriberFields() {
        $results = $this->doQuery( self::SUBSCRIBER_MISSING_DATA_FIELDS );
        
        $data = array();
        
        for ($i = 0; $i < count($results); $i++) {
            $dataRow = array();
            $result = $results[$i];
            $decodedErrors = array();
            foreach($result as $key => $value) {
                if ( $key == 'Subscriber Last Name' && $value == "" ) {
                    array_push($decodedErrors, 'Missing subscriber last name');
                }
                if ( $key == 'Pt Address Street' && $value == "" ) {
                    array_push($decodedErrors, 'Missing address street');
                }
                if ( $key == 'Pt Address Code' && $value == "" ) {
                    array_push($decodedErrors, 'Missing address zip code');
                }
                if ( $key == 'Pt Address City' && $value == "" ) {
                    array_push($decodedErrors, 'Missing address city');
                }
                if ( $key == 'Pt Address State' && $value == "" ) {
                    array_push($decodedErrors, 'Missing address state');
                }
                $dataRow[$key] = $value;
            }
            $dataRow['decodedErrors'] = $decodedErrors;
            $data[] = $dataRow;
        }
        
        return $data;
    }
    
    function findPatientInsuranceMissingSubscriberRelationship() {
        return $this->doQuery( self::INSURANCE_NO_SUBSCRIBER_RELATIONSHIP );
    }
    
    function findPatientInsuranceMissingInsuranceFields() {
        $results = $this->doQuery( self::INSURANCE_MISSING_FIELDS );
        
        $data = array();
        
        for ($i = 0; $i < count($results); $i++) {
            $dataRow = array();
            $result = $results[$i];
            $decodedErrors = array();
            foreach($result as $key => $value) {
                if ( $key == 'Plan Name' && $value == "" ) {
                    array_push($decodedErrors, 'Missing plan name');
                }
                if ( $key == 'Effective Date' && $value == "000-00-00" ) {
                    array_push($decodedErrors, 'Missing effective date');
                }
                if ( $key == 'Policy Number' && $value == "" ) {
                    array_push($decodedErrors, 'Missing policy number');
                }
                if ( $key == 'Group Number' && $value == "" ) {
                    array_push($decodedErrors, 'Missing group number');
                }
                $dataRow[$key] = $value;
            }
            $dataRow['decodedErrors'] = $decodedErrors;
            $data[] = $dataRow;
        }
        
        return $data;
    }
}

?>