<?php

/**
 * Create an array of pids for whitelisting the patient filter for a chart review
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023-2025 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

// comment this out when using this script (and then uncomment it again when done using script)
exit;

if (php_sapi_name() !== 'cli') {
    echo "Only php cli can execute command\n";
    echo "example use: php default 2022-01-01 2022-12-31 primary MCDVT\n";
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
// TBD add all types to getPolicies
$type = $argv[4] ?? 'primary';
$payerId = $argv[5] ?? '87726';

// get insurance_companies by payer id, example 87726 for uhc
$inscos_by_payer_id = (new InsuranceCompanyService())->getAllByPayerID($payerId);

// grab pids with that insurance payer id
foreach ($inscos_by_payer_id as $key => $insco) {
    $policies_by_payer_id_array[] = (new InsuranceService())->getPoliciesByPayerByEffectiveDate(
        $insco['id'],
        $type = $type,
        $startDate,
        $endDate
    );
    $policies_by_payer_id = array_merge(...$policies_by_payer_id_array);
}

// grab encounters by dos
$encs_by_date_range = (new EncounterService())->getEncountersByDateRange($startDate, $endDate);
$encs_result = array_intersect(array_column($policies_by_payer_id, 'pid'), array_column($encs_by_date_range, 'pid'));

// sort and remove duplicate pids from encounters
asort($encs_result);
$result = array();
foreach ($encs_result as $key => $value) {
    if (!in_array($value, $result)) {
        $result[$key] = $value;
    }
}
$output = $output ?? '';
foreach ($result as $key => $value) {
    $output .= ($value ?? '') . ", ";
}
echo "pid list \n";
echo $output . "\n";
