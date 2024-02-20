<?php

require_once("../globals.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/report_database.inc.php";
// TODO: @adunsulag we need to move ALL this AMC stuff into a namespace.  Any AMC classes should use autoloader not requires.
require_once("../../library/classes/rulesets/library/RsReportIF.php");
require_once("../../library/classes/rulesets/library/RsUnimplementedIF.php");
require_once("../../library/classes/rulesets/library/RsPatient.php");
require_once("../../library/classes/rulesets/library/RsPopulation.php");
require_once("../../library/classes/rulesets/library/RsResultIF.php");
require_once("../../library/classes/rulesets/ReportTypes.php");
require_once("../../library/classes/rulesets/library/RsReportFactoryAbstract.php");
require_once("../../library/classes/rulesets/Amc/AmcReportFactory.php");

use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\SystemLogger;

function formatPatientReportData($report_id, &$data, $type_report, $amc_report_types = array())
{
    $dataSheet = json_decode($data, true) ?? [];
    $formatted = [];
    $main_pass_filter = 0;
    foreach ($dataSheet as $row) {
        $row['type'] = $type_report;
        $row['total_patients'] = $row['total_patients'] ?? 0;
        $failed_items = null;
        $displayFieldSubHeader = "";

        $row['type'] = 'amc';
        if (!empty($amc_report_types[$type_report]['code_col'])) {
            $code_col = $amc_report_types[$type_report]['code_col'];
            $displayFieldSubHeader .= " " . text($amc_report_types[$type_report]['abbr']) . ":"
                . text($row[$code_col]) . " ";
        }

        if (isset($row['is_main'])) {
            // note that the is_main record must always come before is_sub in the report or the data will not work.
            $main_pass_filter = $row['pass_filter'] ?? 0;
            $row['display_field'] = generate_display_field(array('data_type' => '1', 'list_id' => 'clinical_rules'), $row['id']);
            if ($type_report == "standard") {
                // Excluded is not part of denominator in standard rules so do not use in calculation
                $failed_items = $row['pass_filter'] - $row['pass_target'];
            } else {
                $failed_items = $row['pass_filter'] - $row['pass_target'] - $row['excluded'];
            }
            $row['display_field_sub'] = ($displayFieldSubHeader != "") ? "($displayFieldSubHeader)" : null;
        } else if (isset($row['is_sub'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1', 'list_id' => 'rule_action_category'), $row['action_category'])
                . ': ' . generate_display_field(array('data_type' => '1', 'list_id' => 'rule_action'), $row['action_item']);
            // Excluded is not part of denominator in standard rules so do not use in calculation
            $failed_items = $main_pass_filter - $row['pass_target'];
        } else if (isset($row['is_plan'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1', 'list_id' => 'clinical_plans'), $row['id']);
        }

        // this is very inefficient, but we are doing it just to get the report written for now.
        if (isset($row['itemized_test_id'])) {
            $row['itemized_patients'] = collectItemizedPatientData($report_id, $row['itemized_test_id']);
        }

        $row['failed_items'] = $failed_items;
        $formatted[] = $row;
    }


    return $formatted;
}

function collectItemizedPatientData($report_id, $itemized_test_id)
{
    $has_results = true;
    $batchSize = 100; // we will do 100 results at a time so we don't overload the MySQL server
    $reportDataByPid = [];
    $numHeaders = [];
    $denomHeaders = [];
    $index = 0;
    $infiniteLoopMax = 100000; // 100*10^5 = 10 million records is the max we'll support without failing
    while ($has_results && $index < $infiniteLoopMax) {
        $sql = "SELECT * FROM `report_itemized` WHERE report_id = ? AND itemized_test_id = ? LIMIT " . $index . "," . $batchSize;
        $sqlParameters = array($report_id, $itemized_test_id);
        $rez = sqlStatementCdrEngine($sql, $sqlParameters);
        if ($rez == false || $rez->EOF) {
            $has_results = false;
            continue;
        }

        for ($iter = $index; $row = sqlFetchArray($rez); $iter++) {
            $pid = $row['pid'];
            if (!isset($reportDataByPid[$pid])) {
                $reportDataByPid[$pid] = [];
            }
            $reportDataByPid[$pid][] = $iter;// need to keep the indexes so we can populate patients later
            $hydratedRecord = ['patient' => $pid, 'numerator' => [], 'denominator' => []];
            $ruleId = $row['rule_id'] ?? null;
            if (!empty($ruleId) && !empty($row['item_details'])) {
                if (empty($ruleObjectHash[$ruleId])) {
                    $ruleObjectHash[$ruleId] = getRuleObjectForId($ruleId) ?? new AMC_Unimplemented();
                }
                $data = json_decode($row['item_details'], true) ?? null;
                if (!empty($data)) {
                    $data = $ruleObjectHash[$ruleId]->hydrateItemizedDataFromRecord($data)->getActionData();

                    $hydratedRecord['numerator'] = $data['numerator'] ?? [];
                    $hydratedRecord['denominator'] = $data['denominator'] ?? [];

                    // grab our headers
                    foreach ($hydratedRecord['numerator'] as $key => $item) {
                        if (empty($numHeaders[$key])) {
                            $numHeaders[$key] = $item['label'];
                        }
                        // save memory usage by clearing out the label, we match with the header key
                        unset($hydratedRecord['numerator'][$key]['label']);
                    }
                    foreach ($hydratedRecord['denominator'] as $key => $item) {
                        if (empty($numHeaders[$key])) {
                            $denomHeaders[$key] = $item['label'];
                        }
                        // save memory usage by clearing out the label, we match with the header key
                        unset($hydratedRecord['denominator'][$key]['label']);
                    }
                }
            }
            $reportData[$iter] = $hydratedRecord;
        }
        $index += $batchSize;
    }

    // now grab all the patients and let's populate here
    $sanitizedPids = array_map('intval', array_keys($reportDataByPid));
    $totalPids = count($sanitizedPids);
    $aggregatedPatientRecords = [];
    if (!empty($sanitizedPids)) {
        for ($i = 0; $i < $totalPids; $i += $batchSize) {
            $slicedPids = array_slice($sanitizedPids, $i, $batchSize, true);

            $sql = "SELECT `patient_data`.*, DATE_FORMAT(`patient_data`.`DOB`,'%m/%d/%Y') as DOB_TS FROM patient_data WHERE pid "
                . " IN (" . implode(",", $slicedPids) . ")";

            $rez = sqlStatementCdrEngine($sql, []);
            for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
                $record = ['patient' => $row, 'records' => []];
                $pid = $row['pid'];
                // if its empty it means we processed it on a previous batch and everything's already been set so move on
                // we shouldn't ever have duplicate pids... since we've handled it at the application layer, but we will
                // be safe here.
                if (empty($reportDataByPid[$pid])) {
                    continue;
                }
                // do we want to make this an object to preserve memory?  Lot of duplicate counting
                foreach ($reportDataByPid[$pid] as $index) {
                    unset($reportData[$index]['patient']);
                    $record['records'][] = $reportData[$index];
                    // now remove the record so we conserve memory
                    $reportData[$index] = null; // we set it to empty to clear it out and not reorder any internal array
                }

                unset($reportDataByPid[$pid]);
                $aggregatedPatientRecords[] = $record;
            }
        }
    }

    return ['patients' => $aggregatedPatientRecords, 'numeratorHeaders' => $numHeaders, 'denominatorHeaders' => $denomHeaders];
}

function getRuleObjectForId($ruleId)
{

    try {
        $reportManager = new AmcReportFactory();
        $rule = ReportTypes::getClassName($ruleId);
        $report = $reportManager->createReport($rule, ['id' => $ruleId], [], [], []);
        return $report;
    } catch (\Exception $error) {
        (new SystemLogger())->errorLogCaller("Failed to instantiate rule class for rule", ['rule_id' => $ruleId
            , 'message' => $error->getTraceAsString()]);
    }
    return null;
}

$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";

// Collect the back variable, if pertinent
$back_link = (isset($_GET['back'])) ? trim($_GET['back']) : "";
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
$report_view = collectReportDatabase($report_id);
if (!empty($report_view)) {
    $amc_report_types = CertificationReportTypes::getReportTypeRecords();
    $type_report = $report_view['type'];
    $amc_report_data = $amc_report_types[$type_report] ?? array();


// See if showing an old report or creating a new report
    // now we are going to create our report
    $dataSheet = formatPatientReportData($report_id, $report_view['data'], true, false, $type_report, $amc_report_data);
    $form_provider = $_POST['form_provider'] ?? '';

    $subTitle = '';
    if ($report_view['provider'] == "group_calculation") {
        $subTitle = xl("Group Calculation Method");
    } else if (is_numeric($report_view['provider'])) {
        // grab the provider
        $userService = new \OpenEMR\Services\UserService();
        $provider = $userService->getUser($report_view['provider']);
        $providerTitle = ($provider['lname'] ?? '') . ',' . ($provider['fname'] ?? '')
            . ' (' . xl('NPI') . ':' . ($provider['npi'] ?? '') . ')';
        $subTitle = xl("Individual Calculation Method") . ' - ' . trim($providerTitle);
    }
    $title = $amc_report_types[$type_report]['title'];
    $data = [
        'report_id' => $report_id
        , 'collate_outer' => $form_provider == 'collate_outer'
        , 'datasheet' => $dataSheet
        , 'title' => $title
        , 'subTitle' => $subTitle
        , 'reportDate' => $report_view['date_report'] ?? ''
    ];

    echo $twig->render('reports/cqm/amc-full-report.html.twig', $data);
} else {
    echo $twig->render('error/404.html.twig', ['statusCode' => 404]);
}
