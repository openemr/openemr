<?php

/**
 *  Daily Summary Report. (/interface/reports/daily_summary_report.php)
 *
 *  This report shows date-wise statistics including appointments scheduled,
 *  new patients, visited patients, total charges, total co-pay, balance amount,
 *  collection rates, no-show rates, provider metrics, and aging analysis
 *  for selected facility and providers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rishabh Software
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016 Rishabh Software
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../globals.php");
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php";

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Reports\DailySummary\DailySummaryService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\Utils\DateFormatterUtils;

// ACL check
if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/rep_a: Daily Summary Report",
        xl("Daily Summary Report")
    );
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();

// Verify CSRF token on POST
if ($request->getMethod() === 'POST' || $request->request->count() > 0) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}

// Initialize services
$facilityService = new FacilityService();
$reportService = new DailySummaryService();

// Get form parameters via Symfony request bags
$fromDateRaw = trim($request->request->getString('form_from_date'));
$toDateRaw = trim($request->request->getString('form_to_date'));
$fromConverted = $fromDateRaw !== '' ? DateFormatterUtils::DateToYYYYMMDD($fromDateRaw) : date('Y-m-d');
$toConverted = $toDateRaw !== '' ? DateFormatterUtils::DateToYYYYMMDD($toDateRaw) : date('Y-m-d');
$formFromDate = is_string($fromConverted) && $fromConverted !== '' ? $fromConverted : date('Y-m-d');
$formToDate = is_string($toConverted) && $toConverted !== '' ? $toConverted : date('Y-m-d');

$formFacility = trim($request->request->getString('form_facility'));
$formProvider = trim($request->request->getString('form_provider'));
$formRefresh = trim($request->request->getString('form_refresh'));
$formCsvExport = trim($request->request->getString('form_csvexport'));

$facilityId = $formFacility !== '' && ctype_digit($formFacility) ? (int) $formFacility : null;
$providerId = $formProvider !== '' && ctype_digit($formProvider) ? (int) $formProvider : null;

// Get facilities list
$allFacilities = $facilityService->getAllFacility();
$facilities = [];
if (is_array($allFacilities)) {
    foreach ($allFacilities as $facility) {
        if (!is_array($facility)) {
            continue;
        }
        $id = $facility['id'] ?? null;
        $name = $facility['name'] ?? null;
        $facilities[] = [
            'id' => is_int($id) || is_string($id) ? $id : '',
            'name' => is_string($name) ? $name : '',
        ];
    }
}

// Get providers list - always load for dropdown
$providersQuery = "SELECT id, fname, lname FROM users WHERE authorized = 1 ORDER BY lname, fname";
$providers = [];
$providerRecords = QueryUtils::fetchRecords($providersQuery, []);
foreach ($providerRecords as $provider) {
    $id = $provider['id'] ?? null;
    $fname = $provider['fname'] ?? null;
    $lname = $provider['lname'] ?? null;
    $providers[] = [
        'id' => is_int($id) || is_string($id) ? $id : '',
        'fname' => is_string($fname) ? $fname : '',
        'lname' => is_string($lname) ? $lname : '',
    ];
}

$reportData = null;
$summaryMetrics = [
    'total_appointments' => 0,
    'total_visits' => 0,
    'total_charges' => 0.0,
    'total_paid' => 0.0,
    'total_balance' => 0.0,
    'collection_rate' => 0.0,
    'no_show_rate' => 0.0,
    'average_charge_per_visit' => 0,
    'average_payment_per_visit' => 0,
];
/** @var list<array<string, float|int|string>> $providerMetrics */
$providerMetrics = [];
/** @var list<array<string, mixed>> $agingAnalysis */
$agingAnalysis = [];
$errorMessage = '';

// Generate report if refresh button clicked
if ($formRefresh !== '') {
    try {
        $appointments = $reportService->fetchAppointmentsSummary($formFromDate, $formToDate, $facilityId, $providerId);
        $newPatients = $reportService->fetchNewPatientsSummary($formFromDate, $formToDate, $facilityId, $providerId);
        $visits = $reportService->fetchVisitsSummary($formFromDate, $formToDate, $facilityId, $providerId);
        $financial = $reportService->fetchFinancialSummary($formFromDate, $formToDate, $facilityId, $providerId);
        $payments = $reportService->fetchPaymentsSummary($formFromDate, $formToDate, $facilityId, $providerId);

        $summaryMetrics = $reportService->calculateMetrics($appointments, $visits, $financial, $payments);
        $providerMetrics = array_values($reportService->calculateProviderMetrics($appointments, $visits, $financial, $payments));
        $agingAnalysis = $reportService->calculateAgingAnalysis($facilityId, $providerId);
        $reportData = $reportService->mergeDataByDimensions($appointments, $newPatients, $visits, $financial, $payments);

        if ($formCsvExport !== '') {
            $reportService->exportToCsv($reportData, $summaryMetrics, $providerMetrics, $formFromDate, $formToDate);
            exit;
        }
    } catch (\RuntimeException $e) {
        ServiceContainer::getLogger()->error('Daily Summary Report Error: ' . $e->getMessage(), ['exception' => $e]);
        $errorMessage = xl("An error occurred while generating the report. Please try again.");
    }
}

echo ServiceContainer::getTwig()->render('reports/daily_summary/report.html.twig', [
    'pageTitle' => xl("Daily Summary Report"),
    'form_from_date' => $formFromDate,
    'form_to_date' => $formToDate,
    'form_facility' => $formFacility,
    'form_provider' => $formProvider,
    'facilities' => $facilities,
    'providers' => $providers,
    'report_data' => $reportData,
    'summary_metrics' => $summaryMetrics,
    'provider_metrics' => $providerMetrics,
    'aging_analysis' => $agingAnalysis,
    'csrf_token' => CsrfUtils::collectCsrfToken($session),
    'error_message' => $errorMessage,
]);
