<?php

/**
 * General Dermatology Printable Report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../globals.php';
require_once __DIR__ . '/autoload.php';

use GeneralDermatology\Exception\GeneralDermatologyException;
use GeneralDermatology\Services\MedicalHistoryService;
use GeneralDermatology\Services\PatientDataService;
use GeneralDermatology\Services\PDFGenerationService;
use GeneralDermatology\Services\TreatmentPlanService;
use GeneralDermatology\Support\FormContext;
use GeneralDermatology\Support\NoteAccessService;
use OpenEMR\Common\Http\CurrentRequest;

define('MODULE_NAME', 'general_dermatology');

$context = FormContext::fromRuntime();
$access = NoteAccessService::fromContext($context);
$request = CurrentRequest::get();

$formid = $request->query->getInt('formid');
$visitid = $request->query->getInt('visitid');

if ($formid <= 0 || $visitid <= 0) {
    http_response_code(400);
    echo text('Missing or invalid required parameters.');
    exit;
}

$tempDir = __DIR__ . '/tmp';
if (!is_dir($tempDir)) {
    if (!@mkdir($tempDir, 0750, true) && !is_dir($tempDir)) {
        error_log('Failed to create temporary directory: ' . $tempDir);
        http_response_code(500);
        echo text('Unable to prepare report output.');
        exit;
    }
} elseif (!is_writable($tempDir)) {
    @chmod($tempDir, 0750);
    if (!is_writable($tempDir)) {
        error_log('Temporary directory is not writable: ' . $tempDir);
        http_response_code(500);
        echo text('Unable to prepare report output.');
        exit;
    }
}

try {
    $generalDermData = $access->requirePatientNoteForVisit($formid, $visitid);
    $pid = (int) ($generalDermData['pid'] ?? 0);

    $patientDataService = new PatientDataService();
    $medicalHistoryService = new MedicalHistoryService();
    $treatmentPlanService = new TreatmentPlanService();
    $pdfService = new PDFGenerationService();

    $patient = $patientDataService->getPatientData($pid);
    $encounter = $patientDataService->getEncounterData($visitid);
    $facility = $patientDataService->getFacilityData();
    $medicalHistory = $medicalHistoryService->getMedicalHistory($pid, $visitid);
    $plans = $treatmentPlanService->getTreatmentPlans($formid);
    $examinationData = $treatmentPlanService->getExaminationData($formid);

    $pdfData = [
        'patient' => $patient,
        'encounter' => $encounter,
        'facility' => $facility,
        'medical_history' => $medicalHistory,
        'examination' => $examinationData,
        'plans' => $plans,
        'form_data' => $generalDermData,
        'formid' => $formid,
    ];

    $pdfService->generatePDF($pdfData);
} catch (GeneralDermatologyException $e) {
    error_log('PDF Generation Authorization/Data Error: ' . $e->getMessage());
    http_response_code(403);
    echo '<h1>' . text('Unable to generate PDF') . '</h1>';
    echo '<p>' . text('You do not have access to this note, or the note could not be found.') . '</p>';
    echo '<p><a href="javascript:history.back()">' . text('Go Back') . '</a></p>';
} catch (Throwable $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    http_response_code(500);
    echo '<h1>' . text('Error Generating PDF') . '</h1>';
    echo '<p>' . text('An error occurred while generating the PDF report.') . '</p>';
    if ($context->isDevelopment()) {
        echo '<h2>' . text('Debug Information') . '</h2>';
        echo '<pre>' . text($e->getMessage()) . "\n" . text($e->getTraceAsString()) . '</pre>';
    }
    echo '<p><a href="javascript:history.back()">' . text('Go Back') . '</a></p>';
}
