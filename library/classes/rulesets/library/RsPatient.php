<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( dirname(__FILE__)."/../../../patient.inc" );

class RsPatient
{
    public $id;
    public $dob;

    public function __construct( $id ) {
        $this->id = $id;
        $this->dob = $this->get_DOB( $id );
    }

    /* Function to get patient dob
     * @param $patient_id
     * @return (string) containing date of birth in the format "YYYY mm dd"
     */
    private function get_DOB( $patient_id ) {
        $dob = getPatientData( $patient_id, "DATE_FORMAT(DOB,'%d/%m/%Y') as TS_DOB" );
        $dob = $dob['TS_DOB'];
        $time = strtotime( $dob );
        $date = date( 'Y-m-d H:i:s', $time ); // MYSQL Date Format
        return $date;
    }
    
    public function calculateAgeOnDate( $date )
    {
        // Grab year, month, and day from dob and dateTarget
        $dateDOB = explode( " ", $this->dob );
        $dateTarget = explode( " ", $date );
         
        $dateDOB = explode( "-", $dateDOB[0] );
        $dateTarget = explode( "-", $dateTarget[0] );
    
        // Collect differences 
        $iDiffYear  = $dateTarget[0] - $dateDOB[0]; 
        $iDiffMonth = $dateTarget[1] - $dateDOB[1]; 
        $iDiffDay   = $dateTarget[2] - $dateDOB[2]; 
         
        // If birthday has not happen yet for this year, subtract 1. 
        if ($iDiffMonth < 0 || ($iDiffMonth == 0 && $iDiffDay < 0)) 
        { 
            $iDiffYear--; 
        } 
             
        return $iDiffYear; 
    }
}
