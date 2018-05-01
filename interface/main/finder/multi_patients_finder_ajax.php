<?php
// Copyright (C) 2017 Amiel Elboim <amiele@matrix.co.il>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once('../../globals.php');
include_once("$srcdir/patient.inc");

$type = $_GET['type'];
$search = $_GET['search'];

switch ($type) {
    case 'by-id':
        // load patients ids for select2.js library, expect receive 'text' and 'id'.
        $results=getPatientId("%$search%", 'pubpid as text, pid as id', 'pubpid');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'by-name':
        // load patients names for select2.js library, expect receive 'text' and 'id'.
        $results=getPatientLnames("%$search%", 'pid as id, CONCAT(lname, ", ",fname)  as text', 'lname ASC, fname ASC');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'patient-by-id':
        $results=getPatientData($search, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        //clean data using 'text' function
        $results=array_map('text', $results);
        $results['DOB'] = oeFormatShortDate($results['DOB']);
        break;
}

$output = array('results' => $results);
echo json_encode($output);
die;
