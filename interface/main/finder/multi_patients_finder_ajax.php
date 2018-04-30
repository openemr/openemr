<?php

include_once('../../globals.php');
include_once("$srcdir/patient.inc");

$type = $_GET['type'];
$search = $_GET['search'];

switch ($type){
    case 'by-id':
        $results=getPatientId("%$search%",'pubpid as text, pid as id','pubpid');
        foreach ($results as $key => $result) {
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'by-name':
        $results=getPatientLnames("%$search%",'pid as id, CONCAT(lname, ", ",fname)  as text','lname ASC, fname ASC');
        foreach ($results as $key => $result) {
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'patient-by-id':
        $results=getPatientData($search, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        $results=array_map('attr', $results);
        $results['DOB'] = oeFormatShortDate($results['DOB']);
        break;
}

$output = array('results' => $results);
echo json_encode($output);die;