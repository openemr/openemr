<?php

/**
 * This is a report of Financial Summary by Service Code.
 *
 * This is a summary of service code charge/pay/adjust and balance,
 * with the ability to pick "important" codes to either highlight or
 * limit to list to. Important codes can be configured in
 * Administration->Service section by assigning code with
 * 'Service Reporting'.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Visolve
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (C) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Reports\SvcCodeFinancialReport\SvcCodeFinancialReportService;
use OpenEMR\Services\FacilityService;
use League\Csv\Writer;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Financial Summary by Service Code")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'] ?? null;
$form_provider  = $_POST['form_provider'] ?? null;
$form_details   = $_POST['form_details'] ?? 0;

// Handle CSV export using League CSV component
if (!empty($_POST['form_csvexport'])) {
    // Get data for CSV
    $service = new SvcCodeFinancialReportService();
    $procedureCodes = $service->getProcedureCodeFinancials(
        $form_from_date,
        $form_to_date,
        $form_facility ? (int)$form_facility : null,
        $form_provider ? (int)$form_provider : null,
        !empty($form_details)
    );

    // Create CSV writer in memory
    $csv = Writer::createFromString('');
    $csv->setOutputBOM(Writer::BOM_UTF8);

    // Insert header row
    $csv->insertOne([
        'Procedure Code',
        'Units',
        'Amount Billed',
        'Paid Amount',
        'Adjustment Amount',
        'Balance Amount',
        'Collection Rate %',
        'Revenue Per Unit'
    ]);

    // Insert data rows
    foreach ($procedureCodes as $code) {
        $collectionRate = ($code['billed'] ?? 0) > 0
            ? (($code['paid_amount'] ?? 0) / ($code['billed'] ?? 0)) * 100
            : 0;
        $revenuePerUnit = ($code['units'] ?? 0) > 0
            ? ($code['billed'] ?? 0) / ($code['units'] ?? 0)
            : 0;

        $csv->insertOne([
            $code['code'],
            $code['units'],
            number_format($code['billed'], 2),
            number_format($code['paid_amount'], 2),
            number_format($code['adjust_amount'], 2),
            number_format($code['balance'], 2),
            number_format($collectionRate, 2),
            number_format($revenuePerUnit, 2)
        ]);
    }

    // Output HTTP headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="svc_financial_report_' . $form_from_date . '--' . $form_to_date . '.csv"');
    header('Content-Description: File Transfer');
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    // Output the CSV
    echo $csv->getContent();
    exit;
}

// Initialize service and prepare data
$service = new SvcCodeFinancialReportService();
$facilityService = new FacilityService();
$report_data = null;
$chart_data = null;
$summary_metrics = null;
$formatted_codes = null;

if (!empty($_POST['form_refresh'])) {
    // Get procedure codes
    $procedureCodes = $service->getProcedureCodeFinancials(
        $form_from_date,
        $form_to_date,
        $form_facility ? (int)$form_facility : null,
        $form_provider ? (int)$form_provider : null,
        !empty($form_details)
    );

    if (!empty($procedureCodes)) {
        $report_data = $procedureCodes;
        $summary_metrics = $service->calculateSummaryMetrics($procedureCodes);
        $chart_data = $service->prepareChartData($procedureCodes);
        $formatted_codes = $service->formatForDisplay($procedureCodes);
    }
}

// Get facilities for dropdown
$facilities = [];
$facilityRecords = $facilityService->getAllFacility();
foreach ($facilityRecords as $facility) {
    $facilities[] = ['id' => $facility['id'], 'name' => $facility['name']];
}

// Get providers for dropdown
$providers = [];
$query = "SELECT id, lname, fname FROM users WHERE authorized = 1 ORDER BY lname, fname";
$ures = sqlStatement($query);
while ($urow = sqlFetchArray($ures)) {
    $providers[] = $urow;
}

// Prepare template variables
$twigVariables = [
    'form_from_date' => $form_from_date,
    'form_to_date' => $form_to_date,
    'form_facility' => $form_facility,
    'form_provider' => $form_provider,
    'form_details' => $form_details,
    'facilities' => $facilities,
    'providers' => $providers,
    'report_data' => $report_data,
    'chart_data' => $chart_data,
    'summary_metrics' => $summary_metrics,
    'formatted_codes' => $formatted_codes,
    'webroot' => $GLOBALS['webroot'],
];

// Render Twig template
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
echo $twig->render('reports/svc_code_financial_report/report.html.twig', $twigVariables);
?>
