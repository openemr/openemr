<?php
$sanitize_all_escapes = true;
$fake_register_globals = false;
require_once (dirname(__FILE__) . "/../../interface/globals.php");

$term = isset($_GET["term"]) ? filter_input(INPUT_GET, 'term') : '';

function get_patients_list($term)
{
    $clear = "- " . xl("Reset no patient") . " -";
    $response = sqlStatement("SELECT Concat_Ws(' ', patient_data.fname, patient_data.lname) as label, patient_data.pid as value FROM patient_data HAVING label LIKE '%$term%' ORDER BY patient_data.lname ASC");
    $resultpd[] = array('label'=>$clear,'value'=>'00');
    while ($row = sqlFetchArray($response)) {
        $resultpd[] = $row;
    }
    echo json_encode($resultpd);
}

get_patients_list($term);
