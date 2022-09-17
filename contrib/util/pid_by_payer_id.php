<?php

$_GET['site'] = 'default';
$ignoreAuth = true;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../../interface/globals.php";

use OpenEMR\Services\{
    AppointmentService,
    InsuranceService,
    InsuranceCompanyService,
    EncounterService
};


// grabs primary type from insurance_companies table
$incos_by_payer_id = (new InsuranceCompanyService())->getAllByPayerID('87726');
//var_dump($incos_by_payer_id);
//echo count($incos_by_payer_id) . "\n";
//exit;

// grab pids with that insurance payer id
//$pids_by_payer_id = [];
foreach ($incos_by_payer_id as $key => $insco) {
    //var_dump($insco);
    //echo $insco['id'];
    $pids_by_payer_id_array[] = (new InsuranceService())->getPidsForPayerByEffectiveDate(
        $insco['id'], 
        $type = 'primary', 
        $startDate = '2020-12-31',
        $endDate = '2022-01-01'
    );

    $pids_by_payer_id = array_merge(...$pids_by_payer_id_array);        

}

//var_dump($pids_by_payer_id);
//echo "\n" . count($pids_by_payer_id) . "\n";
//print_r(array_column($pids_by_payer_id, 'pid'));
// exit;

// grab encounters by dos
$start_date = '2021-01-01';
$end_date = '2021-12-31';

echo $start_date . " " . $end_date;
$encs_by_date_range = (new EncounterService())->getEncountersByDateRange($start_date, $end_date);

//echo count($encs_by_date_range_array) . "/n";
//$pids = array_column($encs_by_date_range_array, 'pid');
//print_r($pids);
//print_r(array_column($encs_by_date_range_array, 'pid'));
//exit;

// var_dump($encs_by_date_range_array);

$encs_result = array_intersect(array_column($pids_by_payer_id, 'pid'), array_column($encs_by_date_range, 'pid'));
//print_r($encs_result);
asort($encs_result);
foreach ($encs_result as $key => $value) {
    $output .= "$value, ";
}
echo $output;
echo "\n" . count($encs_result) . "\n";

//exit;


