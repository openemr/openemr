<?php
// interface/reports/encounters_report.php

require_once dirname(__DIR__) . '/globals.php'; // Include globals.php

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Reports\Encounter\EncounterReportData;
use OpenEMR\Reports\Encounter\EncounterReportFormatter;
use OpenEMR\Reports\Encounter\EncounterReportGenerator;
use OpenEMR\Reports\Encounter\EncounterReportFormHandler;
use OpenEMR\Common\Twig\TwigContainer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

if (!empty($_GET['csrf_token_form'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}
// Initialize Twig
$loader = new TwigContainer(dirname(__FILE__, 3) . '/templates', $GLOBALS['kernel']);
$twig = $loader->getTwig();

global $formattedEncounters, $reportOutput, $encounterCount, $filters, $errors;

// Initialize variables
$encounters = [];
$encounterCount = 0;
$filters = [];
$errors = [];
$summary = [];

// Fetch facilities from the database
$facilitiesResult = sqlStatement("SELECT id, name FROM facility");
$facilities = [];
if ($facilitiesResult) {
    while ($facility = sqlFetchArray($facilitiesResult)) {
        $facilities[] = $facility;
    }
}

// Fetch providers from the database
$providersResult = sqlStatement("SELECT id, CONCAT(lname,' ', fname) AS name FROM users WHERE authorized = 1");
$providers = [];
if ($providersResult) {
    while ($provider = sqlFetchArray($providersResult)) {
        $providers[] = $provider;
    }
}

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
            $generator = new EncounterReportGenerator();
            $reportOutput = $generator->generateReport($formattedEncounters);
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
