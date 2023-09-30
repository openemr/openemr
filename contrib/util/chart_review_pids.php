<?php

/**
 * Create an array of pids for whitelisting the patient filter for a chart review
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

// comment this out when using this script (and then uncomment it again when done using script)
exit;

if (php_sapi_name() !== 'cli') {
    echo "Only php cli can execute command\n";
    echo "example use: php default 2022-01-01 2022-12-31\n";
    die;
}

$_GET['site'] = $argv[1];
$ignoreAuth = true;
require_once __DIR__ . "/../../interface/globals.php";

use OpenEMR\Services\{
    AppointmentService,
    InsuranceService,
    InsuranceCompanyService,
    EncounterService
};

// get date range of encounters from command line args
$startDate = $argv[2];
$endDate = $argv[3];

// get insurance_companies by payer id, example 87726 for uhc
$incos_by_payer_id = (new InsuranceCompanyService())->getAllByPayerID('87726');

// grab pids with that insurance payer id
foreach ($incos_by_payer_id as $key => $insco) {
    $pids_by_payer_id_array[] = (new InsuranceService())->getPidsForPayerByEffectiveDate(
        $insco['id'],
        $type = 'primary',
        $startDate,
        $endDate
    );

    $pids_by_payer_id = array_merge(...$pids_by_payer_id_array);
}

// grab encounters by dos
$encs_by_date_range = (new EncounterService())->getEncountersByDateRange($startDate, $endDate);
$encs_result = array_intersect(array_column($pids_by_payer_id, 'pid'), array_column($encs_by_date_range, 'pid'));
asort($encs_result);

$output = $output ?? '';
foreach ($encs_result as $key => $value) {
    $output .= ($value ?? '') . ", ";
}
echo "pids list \n";
echo $output;
echo "\n" . count($encs_result) . "\n";
