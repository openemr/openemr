<?php

/**
 * Sales by Item Report - Modernized Controller
 *
 * This is a report of sales by item description.
 * Modernized version using service layer and Twig templates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015-2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025      Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2025      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Reports\SalesByItems\Services\DataPreparationService;
use OpenEMR\Reports\SalesByItems\Services\CsvExportService;
use OpenEMR\Reports\SalesByItems\Services\ChartDataService;
use OpenEMR\Reports\SalesByItems\Repository\SalesByItemsRepository;

// Check ACL permissions
if (!AclMain::aclCheckCore('acct', 'rep') && !AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render(
        'core/unauthorized.html.twig',
        ['pageTitle' => xl("Sales by Item")]
    );
    exit;
}

// CSRF protection
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }
}

// Get filter parameters
$form_provider = $_POST['form_provider'] ?? null;
if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    // Only allow user to see their own data
    $form_provider = $_SESSION['authUserID'] ?? null;
}

$form_facility = $_POST['form_facility'] ?? null;
$form_from_date = isset($_POST['form_from_date'])
    ? DateToYYYYMMDD($_POST['form_from_date'])
    : date('Y-m-d');
$form_to_date = isset($_POST['form_to_date'])
    ? DateToYYYYMMDD($_POST['form_to_date'])
    : date('Y-m-d');
$form_details = !empty($_POST['form_details']) ? true : false;
$form_csvexport = !empty($_POST['form_csvexport']) ? true : false;

// Initialize services
$repository = new SalesByItemsRepository();
$dataService = new DataPreparationService($repository);
$csvService = new CsvExportService();
$chartService = new ChartDataService();

// Handle CSV export
if ($form_csvexport) {
    // Prepare data
    $reportData = $form_details
        ? $dataService->prepareDetailedReport($form_from_date, $form_to_date, $form_facility, $form_provider)
        : $dataService->prepareSummaryReport($form_from_date, $form_to_date, $form_facility, $form_provider);

    // Build CSV using service
    $csv = $form_details
        ? $csvService->buildDetailedCsv($reportData['rows'])
        : $csvService->buildSummaryCsv($reportData['rows']);

    // Send CSV response
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=sales_by_item.csv");
    header("Content-Description: File Transfer");

    echo $csv;
    exit;
}

// Prepare report data for HTML rendering
$reportData = null;
$chartCategory = null;
$chartTopItems = null;
if (!empty($_POST['form_refresh'])) {
    $reportData = $form_details
        ? $dataService->prepareDetailedReport($form_from_date, $form_to_date, $form_facility, $form_provider)
        : $dataService->prepareSummaryReport($form_from_date, $form_to_date, $form_facility, $form_provider);
    
    $reportData['show_details'] = $form_details;

    // Build charts using service
    if (!empty($reportData['rows']) && is_array($reportData['rows'])) {
        $chartCategory = $chartService->buildCategoryPieData($reportData['rows'], $form_details);
        $chartTopItems = $chartService->buildTopItemsBarData($reportData['rows'], 10, $form_details);
    }
}

// Render the template
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();

echo $twig->render('reports/sales_by_item/sales_by_item.twig', [
    'from_date' => $form_from_date,
    'to_date' => $form_to_date,
    'report_data' => $reportData,
    'chart_category' => $chartCategory,
    'chart_top_items' => $chartTopItems,
]);
