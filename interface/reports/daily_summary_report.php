<?php

/**
 * Daily Summary Report (Modernized)
 *
 * This report shows date-wise statistics including appointments scheduled,
 * new patients, visited patients, total charges, total co-pay, balance amount,
 * collection rates, no-show rates, provider metrics, and aging analysis
 * for selected facility and providers.
 *
 * @package   H.E.Project_v3
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Reports\DailySummary\DailySummaryService;
use OpenEMR\Services\FacilityService;
use League\Csv\Writer;

// ACL check
if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', [
        'pageTitle' => xl("Daily Summary Report")
    ]);
    exit;
}

// Verify CSRF token
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }
}

// Initialize services
$facilityService = new FacilityService();
$reportService = new DailySummaryService();

// Get form parameters
$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = $_POST['form_facility'] ?? '';
$form_provider = $_POST['form_provider'] ?? '';
$form_refresh = $_POST['form_refresh'] ?? '';
$form_csvexport = $_POST['form_csvexport'] ?? '';

// Convert facility and provider to integers if set
$facilityId = !empty($form_facility) ? (int)$form_facility : null;
$providerId = !empty($form_provider) ? (int)$form_provider : null;

// Get facilities list
$allFacilities = $facilityService->getAllFacility();
$facilities = [];
foreach ($allFacilities as $facility) {
    $facilities[] = [
        'id' => $facility['id'],
        'name' => $facility['name']
    ];
}

// Get providers list - always load for dropdown
$providersQuery = "SELECT id, fname, lname FROM users WHERE authorized = 1 ORDER BY lname, fname";
$providers = [];
$providerRecords = QueryUtils::fetchRecords($providersQuery, []);
if (!empty($providerRecords)) {
    foreach ($providerRecords as $provider) {
        $providers[] = [
            'id' => $provider['id'],
            'fname' => $provider['fname'],
            'lname' => $provider['lname']
        ];
    }
}

$report_data = null;
$summary_metrics = [];
$provider_metrics = [];
$aging_analysis = [];
$error_message = '';

// Generate report if refresh button clicked
if (!empty($form_refresh)) {
    try {
        // Fetch data from service
        $appointments = $reportService->fetchAppointmentsSummary($form_from_date, $form_to_date, $facilityId, $providerId);
        $newPatients = $reportService->fetchNewPatientsSummary($form_from_date, $form_to_date, $facilityId, $providerId);
        $visits = $reportService->fetchVisitsSummary($form_from_date, $form_to_date, $facilityId, $providerId);
        $financial = $reportService->fetchFinancialSummary($form_from_date, $form_to_date, $facilityId, $providerId);
        $payments = $reportService->fetchPaymentsSummary($form_from_date, $form_to_date, $facilityId, $providerId);

        // Calculate metrics
        $summary_metrics = $reportService->calculateMetrics($appointments, $visits, $financial, $payments);
        $provider_metrics = $reportService->calculateProviderMetrics($appointments, $visits, $financial, $payments);
        $aging_analysis = $reportService->calculateAgingAnalysis($facilityId, $providerId);

        // Merge data by dimensions
        $report_data = $reportService->mergeDataByDimensions($appointments, $newPatients, $visits, $financial, $payments);

        // Handle CSV export if requested
        if (!empty($form_csvexport)) {
            exportToCSV($report_data, $summary_metrics, $provider_metrics, $form_from_date, $form_to_date);
            exit;
        }
    } catch (Exception $e) {
        error_log("Daily Summary Report Error: " . $e->getMessage());
        $error_message = xl("An error occurred while generating the report. Please try again.");
    }
}


$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();

echo $twig->render('reports/daily_summary/report.html.twig', [
    'pageTitle' => xl("Daily Summary Report"),
    'form_from_date' => $form_from_date,
    'form_to_date' => $form_to_date,
    'form_facility' => $form_facility,
    'form_provider' => $form_provider,
    'facilities' => $facilities,
    'providers' => $providers,
    'report_data' => $report_data,
    'summary_metrics' => $summary_metrics,
    'provider_metrics' => array_values($provider_metrics),
    'aging_analysis' => $aging_analysis,
    'csrf_token' => CsrfUtils::collectCsrfToken(),
    'error_message' => $error_message,
]);

/**
 * Export report data to CSV
 *
 * @param array $reportData The merged report data
 * @param array $summaryMetrics Summary metrics
 * @param array $providerMetrics Provider-specific metrics
 * @param string $fromDate Start date
 * @param string $toDate End date
 * @return void
 */
function exportToCSV(array $reportData, array $summaryMetrics, array $providerMetrics, string $fromDate, string $toDate): void
{
    $csv = Writer::createFromString('');
    $csv->setOutputBOM(Writer::BOM_UTF8);

    // Summary metrics header
    $csv->insertOne(['Daily Summary Report']);
    $csv->insertOne(['Report Period', oeFormatShortDate($fromDate) . ' to ' . oeFormatShortDate($toDate)]);
    $csv->insertOne([]);

    // Summary metrics
    $csv->insertOne(['SUMMARY METRICS']);
    $csv->insertOne([
        'Total Appointments',
        'Total Visits',
        'Total Charges',
        'Total Paid',
        'Outstanding Balance',
        'Collection Rate %',
        'No-Show Rate %'
    ]);
    $csv->insertOne([
        $summaryMetrics['total_appointments'] ?? 0,
        $summaryMetrics['total_visits'] ?? 0,
        $summaryMetrics['total_charges'] ?? 0,
        $summaryMetrics['total_paid'] ?? 0,
        $summaryMetrics['total_balance'] ?? 0,
        $summaryMetrics['collection_rate'] ?? 0,
        $summaryMetrics['no_show_rate'] ?? 0,
    ]);
    $csv->insertOne([]);

    // Provider metrics
    $csv->insertOne(['PROVIDER PRODUCTIVITY']);
    $csv->insertOne([
        'Provider',
        'Appointments',
        'Visits',
        'No-Show %',
        'Charges',
        'Paid',
        'Balance',
        'Collection %'
    ]);

    foreach ($providerMetrics as $provider) {
        $csv->insertOne([
            $provider['name'] ?? '',
            $provider['appointments'] ?? 0,
            $provider['visits'] ?? 0,
            round($provider['no_show_rate'] ?? 0, 2),
            $provider['charges'] ?? 0,
            $provider['paid'] ?? 0,
            $provider['balance'] ?? 0,
            round($provider['collection_rate'] ?? 0, 2),
        ]);
    }
    $csv->insertOne([]);

    // Daily detail
    $csv->insertOne(['DAILY DETAIL']);
    $csv->insertOne([
        'Date',
        'Facility',
        'Provider',
        'Appointments',
        'New Patients',
        'Visits',
        'Charges',
        'Paid',
        'Balance',
        'Collection %'
    ]);

    foreach ($reportData as $date => $dateData) {
        foreach ($dateData as $facility => $facilityData) {
            foreach ($facilityData as $provider => $metrics) {
                $csv->insertOne([
                    $date,
                    $facility,
                    $provider,
                    $metrics['appointments'] ?? 0,
                    $metrics['newPatients'] ?? 0,
                    $metrics['visits'] ?? 0,
                    $metrics['charges'] ?? 0,
                    $metrics['paid'] ?? 0,
                    $metrics['balance'] ?? 0,
                    round($metrics['collection_rate'] ?? 0, 2),
                ]);
            }
        }
    }

    // Send as download
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="daily_summary_' . date('Y-m-d_H-i-s') . '.csv"');

    echo $csv->getContent();
}
