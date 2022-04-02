<?php

require_once("../globals.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/report_database.inc";
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
            // we don't display the links here
            $row['patients_pass'] = collectItemizedPatientData($report_id, $row['itemized_test_id'], 'all');
            if (!empty($row['patients_pass'])) {
                $row['patients_target'] = collectItemizedPatientData($report_id, $row['itemized_test_id'], 'pass');
            } else {
                $row['patients_target'] = [];
            }

            if ($failed_items > 0) {
                $row['patients_failed'] = collectItemizedPatientData($report_id, $row['itemized_test_id'], 'fail');
            } else {
                $row['patients_failed'] = [];
            }
        }

        $row['failed_items'] = $failed_items;
        $formatted[] = $row;
    }


    return $formatted;
}

function collectItemizedPatientData($report_id, $itemized_test_id, $pass = 'all', $numerator_label = '', $sqllimit = 'all', $fstart = 0)
{

    // we need to return an array of the following
    // we have details in case we need to display any additional detail information or for more rule parsing from the numerator
    // allows us to be open ended here if we have to add stuff.
    // [[ 'patient' => [patientData], 'actions' => ['label' => ['value' => true, 'details' => mixed], label => [...],...]]

    $ruleObjectHash = [];
    $reportData = [];

    $sql = "SELECT * FROM `report_itemized`";
    $sqlParameters = array($report_id,$itemized_test_id,$numerator_label);
    $reportDataByPid = [];
    $actionHeaders = [];

    $processActions = true;
    if ($pass == "all") {
        $processActions = false;
        $sqlWhere = " WHERE `report_itemized`.`pass` != 3 AND `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? ";
    } elseif ($pass == "fail") {
        $sqlWhere = " WHERE `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? AND `report_itemized`.`pass` = ? ";
        array_push($sqlParameters, 0);
    } else {
        $sqlWhere = " WHERE `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? AND `report_itemized`.`pass` = ? ";
        array_push($sqlParameters, 1);
    }

    $rez = sqlStatementCdrEngine($sql . $sqlWhere, $sqlParameters);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $pid = $row['pid'];
        if (!isset($reportDataByPid[$pid])) {
            $reportDataByPid[$pid] = [];
        }
        $reportDataByPid[$pid][] = $iter;// need to keep the indexes so we can populate patients later
        $hydratedRecord = ['patient' => $pid, 'actions' => []];
        $ruleId = $row['rule_id'] ?? null;
        if ($processActions && !empty($ruleId) && !empty($row['item_details'])) {
            if (empty($ruleObjectHash[$ruleId])) {
                $ruleObjectHash[$ruleId] = getRuleObjectForId($ruleId) ?? new AMC_Unimplemented();
            }
            $data = json_decode($row['item_details'], true) ?? null;
            if (!empty($data)) {
                $actionData = $ruleObjectHash[$ruleId]->hydrateItemizedDataFromRecord($data)->getActionData();
                $hydratedRecord['actions'] = $actionData;
                // grab our headers
                foreach ($actionData as $key => $item) {
                    if (empty($actionHeaders[$key])) {
                        $actionHeaders[$key] = $item['label'];
                    }
                }
            }
        }
        $reportData[$iter] = $hydratedRecord;
    }

    // now grab all the patients and let's populate here
    $sanitizedPids = array_map('intval', array_keys($reportDataByPid));
    if (!empty($sanitizedPids)) {
        $sql = "SELECT `patient_data`.*, DATE_FORMAT(`patient_data`.`DOB`,'%m/%d/%Y') as DOB_TS FROM patient_data WHERE pid "
            . " IN (" . implode(",", $sanitizedPids) . ")";

        $rez = sqlStatementCdrEngine($sql, []);
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $pid = $row['pid'];
            // do we want to make this an object to preserve memory?  Lot of duplicate counting
            foreach ($reportDataByPid[$pid] as $index) {
                $reportData[$index]['patient'] = $row;
            }
            unset($reportDataByPid[$pid]);
        }
        // report data had mismatched keys or old pids... we've got to clean up the item data here and remove the patients
        // that don't match here...
        if (!empty($reportDataByPid)) {
            foreach ($reportDataByPid as $pid => $index) {
                unset($reportData[$index]['patient']);
            }
        }
    }

    return ['itemizedPatients' => $reportData, 'actionLabels' => $actionHeaders];
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

    // TODO: @adunsulag look at using collectItemizedPatientsCdrReport() per itemized report for each item...
    $itemIdsSql = "SELECT itemized_test_id, `pid` FROM `report_itemized` WHERE report_id = ? GROUP BY itemized_test_id,pid";
    $itemIds = \OpenEMR\Common\Database\QueryUtils::fetchRecords($itemIdsSql, [$report_id]);
    $reportPatientMap = [];
    foreach ($itemIds as $result) {
        $item_id = $result['itemized_test_id'];
        if (empty($reportPatientMap[$item_id])) {
            $reportPatientMap[$item_id] = [];
        }
        $reportPatientMap[$item_id][] = $result['pid'];
    }

    $sql = "SELECT patient_data.* FROM patient_data WHERE pid IN (select DISTINCT pid FROM `report_itemized` WHERE report_id = ?)";
    $patients = \OpenEMR\Common\Database\QueryUtils::fetchRecords($sql, [$report_id]);
    $patientsById = [];
    foreach ($patients as $patient) {
        $patientsById[$patient['pid']] = $patient;
    }

    $subTitle = '';
    if ($report_view['provider'] == "group_calculation") {
        $subTitle = xl("Group Calculation Method");
    } else if (is_numeric($report_view['provider'])) {
        // grab the provider
        $userService = new \OpenEMR\Services\UserService();
        $provider = $userService->getUser($report_view['provider']);
        $providerTitle = ($provider['fname'] ?? '') . ' ' . ($provider['lname'] ?? '')
            . ' (' . xl('NPI') . ':' . ($provider['npi'] ?? '') . ')';
        $subTitle = xl("Individual Calculation Method") . ' - ' . trim($providerTitle);
    }
    $title = $amc_report_types[$type_report]['title'];
    $data = [
        'report_id' => $report_id
        , 'collate_outer' => $form_provider == 'collate_outer'
        , 'datasheet' => $dataSheet
        , 'reportPatientMap' => $reportPatientMap
        , 'patients' => $patientsById
        , 'title' => $title
        , 'subTitle' => $subTitle
        , 'reportDate' => $report_view['date_report'] ?? ''
    ];

    echo $twig->render('reports/cqm/amc-full-report.html.twig', $data);
} else {
    echo $twig->render('error/404.html.twig', ['statusCode' => 404]);
}
