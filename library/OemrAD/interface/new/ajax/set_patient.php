<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir']."/pid.inc");

$res = array(
    'status' => false,
    'data' => array()
);

if (isset($_GET['set_pid'])) {
    $pidData = setpid($_GET['set_pid']);
    $patientData = getPatientData($_GET['set_pid'], "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

    if(!empty($patientData)) {
        $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
        $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);
        $patientPubpid = $patientData['pubpid'];
        $res = array(
            'status' => true,
            'data' => array(
                'pname' => $patientName,
                'pubpid' => $patientPubpid,
                'pdob' => $patientDOB
            )
        );
    }
}

echo json_encode($res);