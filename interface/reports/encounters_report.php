<?php

/**
 * package   OpenEMR
 * Written with Warp Terminal
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * author-AI Gemini, Cascade, and ChatGPT
 * All rights reserved
 * Copyright (c) 2025.
 */

require_once dirname(__DIR__) . '/globals.php'; // Include globals.php

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Reports\Encounter\EncounterReportData;
use OpenEMR\Reports\Encounter\EncounterReportFormatter;
use OpenEMR\Reports\Encounter\EncounterReportFormHandler;
use OpenEMR\Common\Twig\TwigContainer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


if (!AclMain::aclCheckCore('encounters', 'coding_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Encounters Report")]);
    exit;
}

if (!empty($_GET['csrf_token_form'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Handle CSV export request
if (isset($_GET['export_csv']) && $_GET['export_csv'] === 'true') {
    // Add logging for debugging
    error_log("CSV export requested with parameters: " . json_encode($_GET));

    try {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=encounter_report_' . date('Y-m-d') . '.csv');

        $formHandler = new EncounterReportFormHandler();
        $filters = $formHandler->processForm($_GET);
        error_log("Filters processed: " . json_encode($filters));

        $data = new EncounterReportData();
        $formatter = new EncounterReportFormatter();

        // Create output handle
        $output = fopen('php://output', 'w');

        if ($output === false) {
            error_log("Failed to open output stream");
            echo "Error: Failed to open output stream";
            exit;
        }

        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Export based on report type (summary or detail)
        if (empty($filters['details'])) {
            // Summary export
            $summaryData = $data->getEncounterSummary($filters);
            error_log("Summary data retrieved: " . json_encode($summaryData));

            $formattedSummary = $formatter->formatSummary($summaryData);

            // Write headers
            fputcsv($output, [xl('Provider'), xl('Encounters')]);

            // Write provider rows
            foreach ($formattedSummary['providers'] as $provider) {
                fputcsv($output, [$provider['provider_name'], $provider['encounter_count']]);
            }

            // Write total row
            fputcsv($output, [xl('Total'), $formattedSummary['total_encounters']]);
        } else {
            // Detailed export - get all encounters without pagination limits
            error_log("Retrieving detailed encounter data");
            $encounters = $data->getEncounters($filters, false); // Pass false to disable pagination
            error_log("Encounters retrieved: " . count($encounters));

            $formattedEncounters = $formatter->formatEncounters($encounters);

            // Write headers
            fputcsv($output, [
                xl('ID'),
                xl('Date'),
                xl('Patient'),
                xl('Provider'),
                xl('Visit Type'),
                xl('Enc#'),
                xl('Forms'),
                xl('Coding'),
                xl('Signed By')
            ]);

            // Write encounter rows
            foreach ($formattedEncounters as $encounter) {
                // Build signed by field: show both signers if available
                $encounterSigner = !empty($encounter['encounter_signer']) ? $encounter['encounter_signer'] : '';
                $formSigner = !empty($encounter['form_signer']) ? $encounter['form_signer'] : '';

                if ($encounterSigner && $formSigner) {
                    $signedBy = $encounterSigner . ' / ' . $formSigner;
                } elseif ($encounterSigner) {
                    $signedBy = $encounterSigner;
                } elseif ($formSigner) {
                    $signedBy = $formSigner;
                } else {
                    $signedBy = xl('Not signed');
                }

                fputcsv($output, [
                    $encounter['id'] ?? '',
                    $encounter['date'] ? date('Y-m-d', strtotime((string) $encounter['date'])) : '',
                    $encounter['patient'] ?? '',
                    $encounter['provider'] ?? '',
                    $encounter['category'] ?? '',
                    $encounter['encounter'] . '-' . $encounter['pid'],
                    $encounter['forms'] ?? '',
                    $encounter['coding'] ?? '',
                    $signedBy
                ]);
            }
        }

        fclose($output);
    } catch (Exception $e) {
        error_log("Exception in CSV export: " . $e->getMessage());
        // Return a plain text error message
        header('Content-Type: text/plain');
        echo "Error exporting CSV: " . $e->getMessage();
    }
    exit;
}

// Initialize Twig
$loader = new TwigContainer(dirname(__FILE__, 3) . '/templates', $GLOBALS['kernel']);
$twig = $loader->getTwig();

global $formattedEncounters, $encounterCount, $filters, $errors;

// Initialize variables
$encounters = [];
$encounterCount = 0;
$filters = [];
$errors = [];
$summary = [];

// Fetch facilities from the database
$facilities = QueryUtils::fetchRecords("SELECT id, name FROM facility");

// Fetch providers from the database
$providers = QueryUtils::fetchRecords("SELECT id, CONCAT(lname,' ', fname) AS name FROM users WHERE authorized = 1");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['date_from']) && isset($_GET['date_to'])) {
    $formHandler = new EncounterReportFormHandler();
    $filters = $formHandler->processForm($_GET); // Use $_REQUEST to handle GET/POST

    if (empty($filters['date_from']) || empty($filters['date_to'])) {
        $errors[] = "Please select both 'Date From' and 'Date To'.";
    } else {
        $data = new EncounterReportData();
        $formatter = new EncounterReportFormatter();

        $formattedData = []; // Initialize formatted data

        if (empty($filters['details'])) {
            // Get summary data
            $summaryData = $data->getEncounterSummary($filters);
            $formattedEncounters = $formatter->formatSummary($summaryData);
        } else {
            // Get detailed encounter data
            $encounters = $data->getEncounters($filters);
            $encounterCount = $data->getEncounterCount($filters);
            $formatter = new EncounterReportFormatter();
            $formattedEncounters = $formatter->formatEncounters($encounters);
        }
    }
}

$twigData = [
    'encounters' => $formattedEncounters,
    'encounterCount' => $encounterCount['encounter_count'] ?? 0,
    'filters' => $filters,
    'errors' => $errors,
    'facilities' => $facilities, // Pass facilities to the template
    'providers' => $providers,   // Pass providers to the template
];

// Render the Twig template
try {
    echo $twig->render('reports/encounters/encounters_report.twig', $twigData);
} catch (LoaderError | RuntimeError | SyntaxError $e) {
    error_log('Twig template error: ' . $e->getMessage());
    echo 'Template error: ' . $e->getMessage();
}
