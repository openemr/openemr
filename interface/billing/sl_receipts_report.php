<?php

/**
 * Report - Cash Receipts by Provider (Modernized)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once('../forms/fee_sheet/codes.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\FormatMoney;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;
use OpenEMR\Reports\CashReceipts\Services\CopayDataService;
use OpenEMR\Reports\CashReceipts\Services\ArActivityDataService;
use OpenEMR\Reports\CashReceipts\Services\ProviderGroupingService;
use OpenEMR\Reports\CashReceipts\Services\TotalsService;
use OpenEMR\Reports\CashReceipts\Services\MetricsService;
use OpenEMR\Reports\CashReceipts\Services\ChartDataService;
use OpenEMR\Reports\CashReceipts\Services\CsvExportService;

// ACL check
if (!AclMain::aclCheckCore('acct', 'rep') && !AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render(
        'core/unauthorized.html.twig',
        ['pageTitle' => xl("Cash Receipts by Provider")]
    );
    exit;
}

/**
 * Determine if a procedure code corresponds to clinic receipts
 */
function is_clinic(string $code): bool
{
    global $bcodes;
    $i = strpos($code, ':');
    if ($i) {
        $code = substr($code, 0, $i);
    }

    return (
        !empty($bcodes['CPT4'][xl('Lab')][$code]) ||
        !empty($bcodes['CPT4'][xl('Immunizations')][$code]) ||
        !empty($bcodes['HCPCS'][xl('Therapeutic Injections')][$code])
    );
}

// Parse form inputs
$formRefresh = !empty($_POST['form_refresh']);
$formDoctor = $_POST['form_doctor'] ?? null;
$formFacility = $_POST['form_facility'] ?? null;
$formUseEdate = intval($_POST['form_use_edate'] ?? 0);
$formDetails = !empty($_POST['form_details']);
$formProcedures = !empty($_POST['form_procedures']);

// Date handling
$formFromDate = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-01');
$formToDate = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

// Procedure code parsing
$formProcCodefull = trim($_POST['form_proc_codefull'] ?? '');
$tmpCodeArray = explode(':', $formProcCodefull);
$formProcCodetype = $tmpCodeArray[0] ?? '';
$formProcCode = $tmpCodeArray[1] ?? null;

// Diagnosis code parsing
$formDxCodefull = trim($_POST['form_dx_codefull'] ?? '');
$tmpCodeArray = explode(':', $formDxCodefull);
$formDxCodetype = $tmpCodeArray[0] ?? '';
$formDxCode = $tmpCodeArray[1] ?? null;

// ACL: Restrict to user's own data if not rep_a
if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    $formDoctor = $_SESSION['authUserID'];
}

// Initialize template variables
$templateVars = [
    'csrf_token' => CsrfUtils::collectCsrfToken(),
    'show_results' => $formRefresh,
    'simplified_demographics' => !empty($GLOBALS['simplified_demographics']),
    'invoice_display_mode' => ($GLOBALS['cash_receipts_report_invoice'] ?? '0') != '0',
    'webroot' => $GLOBALS['webroot'],
    'procedure_codetypes' => js_escape(collect_codetypes("procedure", "csv")),
    'diagnosis_codetypes' => js_escape(collect_codetypes("diagnosis", "csv")),
    'filters' => [
        'from_date' => oeFormatShortDate($formFromDate),
        'to_date' => oeFormatShortDate($formToDate),
        'use_edate' => $formUseEdate,
        'details' => $formDetails,
        'procedures' => $formProcedures,
        'proc_codefull' => $formProcCodefull,
        'dx_codefull' => $formDxCodefull,
    ],
];

// Generate facility dropdown
ob_start();
dropdown_facility($formFacility, 'form_facility');
$templateVars['facility_dropdown'] = ob_get_clean();

// Generate provider dropdown
ob_start();
if (AclMain::aclCheckCore('acct', 'rep_a')) {
    $repository = new CashReceiptsRepository();
    $providers = $repository->getAuthorizedProviders();
    echo "<select name='form_doctor' class='form-control'>\n";
    echo "    <option value=''>-- " . xlt('All Providers') . " --\n";
    foreach ($providers as $provider) {
        $selected = ($formDoctor == $provider['id']) ? ' selected' : '';
        echo "    <option value='" . attr($provider['id']) . "'$selected>";
        echo text($provider['lname']) . ", " . text($provider['fname']) . "\n";
    }
    echo "</select>\n";
} else {
    echo "<input type='hidden' name='form_doctor' value='" . attr($_SESSION['authUserID']) . "'>";
}
$templateVars['provider_dropdown'] = ob_get_clean();

// Handle CSV export request
if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
    // Validate CSRF token
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }

    // Parse GET parameters (same as POST for form submission)
    $formDoctor = $_GET['form_doctor'] ?? null;
    $formFacility = $_GET['form_facility'] ?? null;
    $formUseEdate = intval($_GET['form_use_edate'] ?? 0);
    $formDetails = !empty($_GET['form_details']);
    $formFromDate = isset($_GET['form_from_date']) ? DateToYYYYMMDD($_GET['form_from_date']) : date('Y-m-01');
    $formToDate = isset($_GET['form_to_date']) ? DateToYYYYMMDD($_GET['form_to_date']) : date('Y-m-d');

    // ACL: Restrict to user's own data if not rep_a
    if (!AclMain::aclCheckCore('acct', 'rep_a')) {
        $formDoctor = $_SESSION['authUserID'];
    }

    try {
        // Initialize services (same as report generation)
        $repository = new CashReceiptsRepository();
        $isClinicCallback = is_clinic(...);

        $copayService = new CopayDataService($repository, ($GLOBALS['cash_receipts_report_invoice'] ?? '0') != '0');
        $arService = new ArActivityDataService($repository, ($GLOBALS['cash_receipts_report_invoice'] ?? '0') != '0', $isClinicCallback);
        $groupingService = new ProviderGroupingService($repository);

        $filters = [
            'from_date' => $formFromDate,
            'to_date' => $formToDate,
            'date_mode' => $formUseEdate,
            'facility_id' => $formFacility,
            'provider_id' => $formDoctor,
        ];

        // Fetch data
        $copayReceipts = $copayService->processReceipts($filters);
        $arReceipts = $arService->processReceipts($filters);
        $allReceipts = array_merge($copayReceipts, $arReceipts);
        $providerSummaries = $groupingService->getSortedProviderSummaries($allReceipts);

        // Export CSV
        $csvService = new CsvExportService();
        $exportOptions = [
            'show_details' => $formDetails,
            'show_procedures' => !empty($_GET['form_procedures']),
            'invoice_display_mode' => ($GLOBALS['cash_receipts_report_invoice'] ?? '0') != '0',
        ];
        $response = $csvService->exportToResponse($providerSummaries, $filters, $exportOptions);
        $response->send();
        exit;
    } catch (Exception $e) {
        error_log("CSV Export Error: " . $e->getMessage());
        die("Error generating CSV export. Please try again.");
    }
}

// Process report if form submitted
if ($formRefresh && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

if ($formRefresh) {
    try {
        // Initialize services
        $repository = new CashReceiptsRepository();
        $isClinicCallback = is_clinic(...);

        $copayService = new CopayDataService($repository, $templateVars['invoice_display_mode']);
        $arService = new ArActivityDataService($repository, $templateVars['invoice_display_mode'], $isClinicCallback);
        $groupingService = new ProviderGroupingService($repository);
        $totalsService = new TotalsService();
        $metricsService = new MetricsService();
        $chartService = new ChartDataService();

        // Build filter array for services
        $filters = [
            'from_date' => $formFromDate,
            'to_date' => $formToDate,
            'date_mode' => $formUseEdate,
            'facility_id' => $formFacility,
            'provider_id' => $formDoctor,
        ];

        // Add procedure code filter if specified
        if (!empty($formProcCode) && !empty($formProcCodetype)) {
            $filters['procedure_code'] = $formProcCode;
            $filters['procedure_code_type'] = $formProcCodetype;
        }

        // Add diagnosis code filter if specified
        if (!empty($formDxCode) && !empty($formDxCodetype)) {
            $filters['diagnosis_code'] = $formDxCode;
            $filters['diagnosis_code_type'] = $formDxCodetype;
        }

        // Fetch data
        $copayReceipts = [];
        if (empty($formProcCode) || empty($formProcCodetype)) {
            // Only get copays if no procedure code specified
            $copayReceipts = $copayService->processReceipts($filters);
        }

        $arReceipts = $arService->processReceipts($filters);

        // Combine receipts
        $allReceipts = array_merge($copayReceipts, $arReceipts);

        // Group by provider
        $providerSummaries = $groupingService->getSortedProviderSummaries($allReceipts);

        // Calculate totals
        $grandTotals = $totalsService->calculateGrandTotals($providerSummaries);

        // Format currency
        foreach ($providerSummaries as $provider) {
            $providerArray = $provider->toArray();
            $providerArray['professional_total'] = FormatMoney::getBucks($provider->getProfessionalTotal());
            $providerArray['clinic_total'] = FormatMoney::getBucks($provider->getClinicTotal());
            $providerArray['grand_total'] = FormatMoney::getBucks($provider->getGrandTotal());

            // Format receipt amounts
            foreach ($providerArray['receipts'] as &$receipt) {
                $receipt['amount'] = FormatMoney::getBucks($receipt['amount']);
            }

            $templateVars['provider_summaries'][] = $providerArray;
        }

        // Format grand totals
        $templateVars['grand_totals'] = [
            'professional' => FormatMoney::getBucks($grandTotals['professional']),
            'clinic' => FormatMoney::getBucks($grandTotals['clinic']),
            'grand' => FormatMoney::getBucks($grandTotals['grand']),
        ];

        // Generate charts
        if (!empty($providerSummaries)) {
            $templateVars['charts'] = [
                'provider_revenue' => $chartService->buildProviderRevenueChart($providerSummaries),
                'daily_cashflow' => $chartService->buildDailyCashFlowChart(
                    $metricsService->getDailyCashFlow($allReceipts)
                ),
            ];
        }
    } catch (Exception $e) {
        error_log("Cash Receipts Report Error: " . $e->getMessage());
        $templateVars['error'] = "An error occurred while generating the report. Please try again.";
    }
}

// Render template
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
echo $twig->render('reports/cash_receipts/cash_receipts.twig', $templateVars);
