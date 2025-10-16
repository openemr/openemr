<?php

/**
 * Encounter Report
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Start timing the script execution
$startTime = microtime(true);

// Enable output buffering for better performance with large datasets
ob_start();

// Disable script time limit for large reports
@set_time_limit(0);

// Disable output buffering for streaming responses
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
@ob_implicit_flush(true);

// Increase memory limit for large datasets
@ini_set('memory_limit', '512M');

// Disable session write for read-only operations
if (!isset($GLOBALS['encounter'])) {
    session_write_close();
}

require_once dirname(__DIR__) . '/globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Reports\Encounter\Form\EncounterReportFormHandler;
use OpenEMR\Reports\Encounter\Service\EncounterReportService;
use OpenEMR\Reports\Encounter\Formatter\EncounterReportFormatter;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;
use OpenEMR\Common\Logging\SystemLogger;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

// Initialize logger
$logger = new SystemLogger();

try {
    // Check user access
    if (!AclMain::aclCheckCore('reports', 'encounters')) {
        throw new \RuntimeException('Access denied to encounters report');
    }

    // Initialize services
    $formHandler = new EncounterReportFormHandler();
    $reportService = new EncounterReportService();
    $formatter = new EncounterReportFormatter();

    // Process form submission
    $formData = [];
    $errors = [];
    $showReport = false;
    $export = $_GET['export'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $formData = $formHandler->processForm($_POST);
            $showReport = true;
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            $logger->error('Form processing error: ' . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
        }
    } elseif (!empty($_GET)) {
        // Handle GET parameters for filtering
        $formData = array_filter([
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'facility' => $_GET['facility'] ?? null,
            'provider' => $_GET['provider'] ?? null,
            'details' => $_GET['details'] ?? null,
            'page' => $_GET['page'] ?? 1,
            'per_page' => $_GET['per_page'] ?? 25
        ]);
        $showReport = true;
    }

    // Get facilities and providers for the form
    $facilityService = new FacilityService();
    $userService = new UserService();

    $facilities = $facilityService->getAll(['active' => 1]);
    $providers = $userService->getAll(['active' => 1, 'authorized' => 1]);

    // Process report data if needed
    $reportData = [];
    $pagination = [];
    $stats = [];

    if ($showReport) {
        try {
            // Get statistics
            $stats = $reportService->getEncounterStatistics($formData);

            // Handle export
            if ($export) {
                $exportData = $reportService->exportEncounters($formData, $export);
                $formattedExport = $formatter->formatForExport($exportData['data'], $export);

                switch ($export) {
                    case 'csv':
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="encounter_report_' . date('Y-m-d') . '.csv"');
                        $output = fopen('php://output', 'w');
                        foreach ($formattedExport as $row) {
                            fputcsv($output, $row);
                        }
                        fclose($output);
                        exit;

                    case 'pdf':
                        // PDF generation would go here
                        break;

                    case 'excel':
                        // Excel generation would go here
                        break;
                }
            }

            // Get paginated data
            $result = $reportService->getEncounters($formData);
            $reportData = $formatter->formatEncounters($result['data'] ?? []);

            // Set up pagination
            $pagination = [
                'current_page' => $result['page'] ?? 1,
                'total_pages' => $result['total_pages'] ?? 1,
                'total_items' => $result['total'] ?? 0,
                'per_page' => $result['per_page'] ?? 25
            ];

        } catch (\Exception $e) {
            $errors[] = 'Error generating report: ' . $e->getMessage();
            $logger->error('Report generation error: ' . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
        }
    }

    // Prepare data for the template
    $twig = new TwigContainer(null, $GLOBALS['kernel']);
    $templateVars = [
        'formData' => $formData,
        'reportData' => $reportData,
        'pagination' => $pagination,
        'stats' => $formatter->formatStatistics($stats),
        'facilities' => $facilities,
        'providers' => $providers,
        'errors' => $errors,
        'showReport' => $showReport,
        'export' => $export,
        'csrf_token_form' => CsrfUtils::collectCsrfToken('encounters_report'),
        'performance' => [
            'execution_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4) . 's',
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
        ]
    ];

    // Get the Twig environment
    $twigEnv = $twig->getTwig();

    // Start output buffering
    ob_start();

    try {
        // Render the template
        $output = $twigEnv->render('reports/encounters/encounters_report.twig', $templateVars);

        // Get any output that might have been generated during rendering
        $output = ob_get_clean() . $output;

        // Minify HTML output in production
        if ($GLOBALS['production_env'] ?? false) {
            $output = preg_replace(
                ['/\s{2,}/', '/\s*([{}|:;,[\]<>])\s*/', '/;}/'],
                [' ', '\1', '}'],
                $output
            );
        }

        echo $output;

    } catch (\Exception $e) {
        // Clean the output buffer if there was an error
        ob_end_clean();
        throw $e;
    }

} catch (\Exception $e) {
    // Log the error
    $logger->error('Fatal error in encounters report: ' . $e->getMessage(),
        ['trace' => $e->getTraceAsString()]);

    // Start a new output buffer
    ob_start();

    try {
        // Try to render the error page
        $twig = new TwigContainer(null, $GLOBALS['kernel']);
        $errorOutput = $twig->getTwig()->render('error/general_error.html.twig', [
            'errorMessage' => 'An error occurred while generating the report.',
            'errorDetails' => $GLOBALS['debug'] ? $e->getMessage() : ''
        ]);

        // Clean any previous output and display the error
        ob_end_clean();
        echo $errorOutput;

    } catch (\Exception $errorException) {
        // If there's an error rendering the error page, show a simple message
        ob_end_clean();
        echo 'An error occurred while generating the report. Please try again later.';
        if ($GLOBALS['debug'] ?? false) {
            echo ' Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
    exit;
}

// Log performance metrics at the end of the script
if (!empty($GLOBALS['debug'])) {
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // Convert to MB

    error_log(sprintf(
        'Encounter Report - Time: %.4fs, Memory: %.2fMB',
        $executionTime,
        $memoryUsage
    ));
}

// Output the rendered content
echo $output;
