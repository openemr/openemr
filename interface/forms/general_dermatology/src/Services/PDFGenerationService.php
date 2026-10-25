<?php

namespace GeneralDermatology\Services;

use OpenEMR\Core\OEGlobalsBag;
use GeneralDermatology\Exception\GeneralDermatologyException;

use GeneralDermatology\App\SeverityList;
use GeneralDermatology\Models\MedicalData;
use GeneralDermatology\Utils\PDFLayoutHelper;
use GeneralDermatology\Models\MedicalHistory;
use GeneralDermatology\Utils\TextFormatter;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use GeneralDermatology\Services\MedicalHistoryService;
use GeneralDermatology\Services\TreatmentPlanService;

/**
 * PDF Generation Service Class
 *
 * Handles generation of PDF documents for dermatology reports
 */
class PDFGenerationService
{
    /**
     * @var Mpdf The mPDF instance
     */
    private $mpdf;

    /**
     * @var PDFLayoutHelper The PDF layout helper
     */
    private $layoutHelper;

    /**
     * @var array PDF configuration settings
     */
    private $config;

    public $entireNoteData;

    /**
     * @var MedicalHistoryService The medical history service
     */
    private $medicalHistoryService;

    /**
     * @var TreatmentPlanService The treatment plan service
     */
    private $treatmentPlanService;
    private $subjectiveBuilder;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load configuration
        $this->config = require dirname(dirname(__DIR__)) . '/config/pdf_settings.php';

        // Create a custom temp directory that's writable by the web server
        $moduleDir = dirname(dirname(__DIR__));
        $tempDir = $moduleDir . '/tmp';

        // Try to create the temp directory if it doesn't exist
        if (!is_dir($tempDir)) {
            if (!@mkdir($tempDir, 0777, true)) {
                // If we can't create the main temp directory, use the system temp directory
                $tempDir = sys_get_temp_dir() . '/openemr_dermatology_' . md5($moduleDir);
                if (!is_dir($tempDir)) {
                    @mkdir($tempDir, 0777, true);
                }
            }
        }

        // Ensure the directory is writable
        if (!is_writable($tempDir)) {
            // If the directory is not writable, use the system temp directory
            $tempDir = sys_get_temp_dir() . '/openemr_dermatology_' . md5($moduleDir);
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }
        }

        // Initialize mPDF with custom configuration
        try {
            // Define a completely new configuration with explicit values
            $mpdfConfig = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 30,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 10,
                'tempDir' => $tempDir,

                // Critical settings to prevent content overflow
                'autoPageBreak' => true,
                'setAutoTopMargin' => 'stretch',
                'setAutoBottomMargin' => 'stretch',
                'adjustFontDescLineheight' => 1.2,
                'shrink_tables_to_fit' => 1,
                'use_kwt' => true,
                'useSubstitutions' => true,
                'keep_table_proportions' => true,
                'allow_charset_conversion' => true,
                'biDirectional' => false,
                'showWatermarkImage' => false
            ];

            $this->mpdf = new Mpdf($mpdfConfig);

            // Set default font and styling
            $this->mpdf->SetDefaultBodyCSS('font-family', 'Arial, Helvetica, sans-serif');
            $this->mpdf->SetDefaultBodyCSS('font-size', '10pt');
            $this->mpdf->SetDefaultBodyCSS('line-height', '1.4');

            // Enable automatic page breaks
            $this->mpdf->SetAutoPageBreak(true, 25);

            // Set maximum width for text wrapping
            $this->mpdf->SetDisplayMode('fullpage');

            // Initialize PDF layout helper
            $this->layoutHelper = new PDFLayoutHelper($this->mpdf);

            // Set base path for images
            $webroot = OEGlobalsBag::getInstance()->getString('webroot');
            $this->mpdf->imageVars['webroot'] = $webroot;
        } catch (\Exception $e) {
            // Log the error and provide more helpful information
            error_log("mPDF initialization error: " . $e->getMessage());
            throw new GeneralDermatologyException("Failed to initialize PDF generator. Please check that the temporary directory ($tempDir) is writable by the web server.");
        }
        $this->subjectiveBuilder = new SubjectiveService();
        $this->medicalHistoryService = new MedicalHistoryService();
        $this->treatmentPlanService = new TreatmentPlanService();

    }

    /**
     * Generate PDF from data
     *
     * @param array $data The data for PDF generation
     * @param bool $output Whether to output the PDF or return it
     * @return mixed The PDF content if $output is false, otherwise outputs to browser
     */
    public function generatePDF(array $data, bool $output = true)
    {
        // Set header and footer
        $this->setHeaderAndFooter($data);

        // Generate main content
        $mainContent = $this->generateCombinedContent($data);
        $this->mpdf->WriteHTML($mainContent);

        // Output or return the PDF
        if ($output) {
            $this->mpdf->Output('Visit_Note.pdf', 'I');
            return null;
        } else {
            return $this->mpdf->Output('', 'S');
        }
    }

    /**
     * Set header and footer
     *
     * @param array $data The data for header and footer
     */
    private function setHeaderAndFooter(array $data): void
    {
        // Generate header
        $headerData = [
            'facility_name' => $data['facility']['facility_name'] ?? '',
            'visit_description' => $data['encounter']['visit_description'] ?? '',
            'note_date' => $data['encounter']['note_date'] ?? '',
            'provider_name' => $data['encounter']['provider_name'] ?? '',
            'patient_name' => $data['patient']->getFullName(),
            'dob' => $data['patient']->getDOB(),
            'age' => $data['patient']->getAge(),
            'gender' => $data['patient']->getGender(),
            'pid' => $data['patient']->getPID(),
        ];

        $header = $this->layoutHelper->generateHeader($headerData);
        $this->mpdf->SetHTMLHeader($header);

        // Generate footer
        $footer = $this->layoutHelper->generateFooter();
        $this->mpdf->SetHTMLFooter($footer);
    }

    /**
     * Generate main content
     *
     * @param array $data The data for main content
     * @return string The HTML for main content
     */
    private function generateCombinedContent(array $data): string
    {
        // Start with main content
        $mainContent = $this->generateMainContent($data);

        // Combine all into a single HTML document with proper structure
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        /* Global page break settings */
        .plan-item {
            page-break-inside: avoid;
        }
        /* Other styles... */
    </style>
</head>
<body>
    {$mainContent}
</body>
</html>
HTML;
    }

    /**
     * Generate main content HTML (inner content only)
     *
     * @param array $data The data for main content
     * @return string The HTML for main content
     */
    private function generateMainContent(array $data): string
    {
        // Use medical history service to get data
        $pid = $data['patient']->getPID() ?? null;
        $encounter = $data['encounter']['id'] ?? null;

        // If medical_history isn't set in data but we have pid and encounter, get it from service
        if (!isset($data['medical_history']) && $pid && $encounter) {
            $data['medical_history'] = $this->medicalHistoryService->getMedicalHistory($pid, $encounter);
        }
        $subjectiveSelections = $this->subjectiveBuilder->buildSubjectiveSection($data['form_data']);
        $medicalHistoryItems = isset($data['medical_history']) ? 'Available' : 'None';
        $examinations = $this->medicalHistoryService->buildExaminationSection($data['examination'], $data['form_data']);

        $vitals = $data['examination']['vitals'] ?? [];
        $generalHistory = $data['examination']['general_history'] ?? [];
        $patient = $data['patient'] ?? [];
        $hpi = $data['form_data']['subjective_text'] ?? 'empty';

        // Get treatment plans
        $treatmentPlans = [];
        if (isset($data['plans']) && !empty($data['plans'])) {
            // Use pre-fetched plans if available
            $treatmentPlans = $data['plans'];
        }
        // Generate plan HTML using the TreatmentPlanService
        $planResult = $this->treatmentPlanService->generatePlanHtml($treatmentPlans);
        $planHtml = $planResult['html'] ?? '';
        $planCount = $planResult['planCounter'] ?? 0;

        // Start building HTML content
        $html = '';

        // History of Present Illness (HPI) section
        $html .= "<div class='section-title' style='padding-top: 5px; padding-bottom: 5px;'>";
        $html .= "<h3>History of Present Illness:</h3>";

        // Get patient age and gender if available
        $age = $patient->getAge() ?? '';
        $gender = strtolower($patient->getGender()) ?? '';

        $html .= "<p>This is a {$age} year old {$gender} who ";
        $html .= nl2br(htmlspecialchars($hpi));
        $html .= "</p></div>";
        //Subjective section
//        $html .= "<div class='section-title' style='padding-top: 5px; padding-bottom: 5px;'>";
//        $html .= "<h3>Subjective</h3>";
//        $html .= "<p>" . nl2br(htmlspecialchars($data['form_data']['subjective_text'])) . "</p>";
//        if (!empty($subjectiveSelection['activeConditionsLists'])) {
//        $html .= "<p> <strong>Skin Conditions:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['activeConditionsList'])) . "</p>";
//        }
//        if (!empty($subjectiveSelections['listedLocations'])) {
//            $html .= "<p><strong>Locations:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['listedLocations'])) . "</p>";
//        }
//        if (!empty($subjectiveSelections['onset'])) {
//            $html .= "<p><strong>Onset:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['onset'])) . "</p>";
//        }
//        if (!empty($subjectiveSelections['weeks'])) {
//            $html .= "<p><strong>Onset Weeks:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['weeks'])) . "</p>";
//        }
//        if (!empty($subjectiveSelections['priorTreatmentText1']) || !empty($subjectiveSelections['priorTreatmentText2'])) {
//            $html .= "<p><strong>Prior Treatment:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['priorTreatmentText1'])) . "Helped? " . $subjectiveSelections['priorTreatmentHelped'];
//            $html .= "<br>" . nl2br(htmlspecialchars($subjectiveSelections['priorTreatmentText2'])) . "Helped? " . $subjectiveSelections['priorTreatmentHelped2'] .  "</p>";
//        }
//        if (!empty($subjectiveSelections['listedSymptoms'])) {
//            $html .= "<p><strong>Symptoms:</strong> " . nl2br(htmlspecialchars($subjectiveSelections['listedSymptoms'])) ;
//            if (!empty($subjectiveSelections['symptomsSeverity'])) {
//            $html .= "<br>Symptom Severity: " . nl2br(htmlspecialchars($subjectiveSelections['symptomsSeverity'])) . "/10 </p>";
//            }
//        }
//        $html .= "</div>";

        //Examinations section
        $html .= "<div class='section-title' style='padding-top: 5px; padding-bottom: 5px;'>";
        $html .= "<h3>Examinations</h3>";
        $html .= "<p>An examination was performed including:</p>";
        $html .= "<p><strong>Sites Examined:</strong> " . nl2br(htmlspecialchars($examinations['exams'])) . "</p>";
        $html .= "<p><strong>Appearance: </strong>" . nl2br(htmlspecialchars($examinations['exams_observation_1'])) . "</p>";
        $html .= "<p><strong>Orientation:</strong> " . nl2br(htmlspecialchars($examinations['orientation'])) . "</p>";
        $html .= "<p><strong>Mood and Affect:</strong> " . nl2br(htmlspecialchars($examinations['mood'])) . "</p>";
        if (!empty($examinations['lymph_nodes'])) {
        $html .= "<p><strong>Lymph Nodes:</strong> " . $examinations['lymph_nodes'] . "</p>";
        }
        $html .= "<p>" . nl2br(htmlspecialchars($examinations['exams_observation_text'])) . "</p>";
        $html .= "<p>" . nl2br(htmlspecialchars($examinations['exams_observation_2'])) . "</p>";
        if (!empty($examinations['exams_observation_3'])) {
        $html .= "<p><strong>Appearance:</strong> " . nl2br(htmlspecialchars($examinations['exams_observation_3'])) . "</p>";
        }
        $html .= "</div>";
        // Medical History
//        if (!empty($medicalHistoryItems)) {
//            $html .= "<div class='medical-history' >";
//            $html .= "<h2>Medical History</h2>";
//
//            // General Medical History
//
//            $html .= "<div class='general-medical' >";
//            $html .= "<h3>Family History</h3>";
//            $html .= "<p>" . nl2br($this->medicalHistoryService->getFamilyHistory($pid)) ?? 'None' . "</p>";
//            $html .= "</div>";
//
//            // Surgical History
//            $surgicalHistory = $data['medical_history']->getSurgicalHistory() ?? 'None';
//            if ($surgicalHistory !== 'None') {
//                $html .= "<div class='surgical-history' >";
//                $html .= "<h3>Surgical History</h3>";
//                $html .= "<p>" . nl2br($surgicalHistory) . "</p>";
//                $html .= "</div>";
//            }
//
//            // Skin Disease History
//            $skinDiseaseHistory = 'None';
//            if (isset($data['medical_history']) && method_exists($data['medical_history'], 'getSkinDiseaseHistory')) {
//                $skinDiseaseHistory = $data['medical_history']->getSkinDiseaseHistory();
//            } elseif ($pid) {
//                // Fallback to directly using the service if we have pid
//                $skinDiseaseHistory = $this->medicalHistoryService->getSkinDiseaseHistory($pid);
//            }
//
//            if ($skinDiseaseHistory !== 'None') {
//                $html .= "<div class='skin-disease-history' >";
//                $html .= "<h3>Skin Disease History</h3>";
//                $html .= "<p>" . nl2br($skinDiseaseHistory) . "</p>";
//                $html .= "</div>";
//            }
//
//            // Skin Cancer History
//            $skinCancerHistory = 'None';
//            if (isset($data['medical_history']) && method_exists($data['medical_history'], 'getSkinCancerHistory')) {
//                $skinCancerHistory = $data['medical_history']->getSkinCancerHistory();
//            } elseif ($pid) {
//                // Fallback to directly using the service if we have pid
//                $skinCancerHistory = $this->medicalHistoryService->getSkinCancerHistory($pid);
//            }
//
//            $html .= "<div class='skin-cancer-history' >";
//            $html .= "<h3>Skin Cancer History</h3>";
//            $html .= "<p>" . nl2br($skinCancerHistory) . "</p>";
//            $html .= "</div>";
//
//
//            // Medications
//            $medications = 'None';
//            if (isset($data['medical_history']) && method_exists($data['medical_history'], 'getMedications')) {
//                $medications = $data['medical_history']->getMedications();
//            } elseif ($pid) {
//                // Fallback to directly using the service if we have pid
//                $medications = $this->medicalHistoryService->getMedications($pid);
//            }
//
//            $html .= "<div class='medications' >";
//            $html .= "<h3>Current Medications</h3>";
//            $html .= "<p>" . nl2br($medications) . "</p>";
//            $html .= "</div>";
//
//
            // Alerts (allergies)
            $alerts = 'None';
            if (isset($data['medical_history']) && method_exists($data['medical_history'], 'getAlerts')) {
               $alerts = $data['medical_history']->getAlerts();
            } elseif ($pid) {
                // Fallback to directly using the service if we have pid
                $alerts = $this->medicalHistoryService->getAlerts($pid);
            }


            $html .= "<div class='allergies' >";
            $html .= "<h3>Allergies & Alerts</h3>";
            $html .= "<p>" . nl2br($alerts) . "</p>";
            $html .= "</div>";
//
//
//            // Review of Systems
//            $reviewOfSystems = 'None';
//            if (isset($data['medical_history']) && method_exists($data['medical_history'], 'getReviewOfSystems')) {
//                $reviewOfSystems = $data['medical_history']->getReviewOfSystems();
//            } elseif ($encounter) {
//                // Fallback to directly using the service if we have encounter ID
//                $reviewOfSystems = $this->medicalHistoryService->getReviewOfSystems($encounter);
//            }
//
//            if ($reviewOfSystems !== 'None' && $reviewOfSystems !== 'none') {
//                $html .= "<div class='review-systems' >";
//                $html .= "<h3>Review of Systems</h3>";
//                $html .= "<p>" . $reviewOfSystems . "</p>";
//                $html .= "</div>";
//            }
//
//            $html .= "</div>";
//        }

        // Vitals if available
        if (!empty($vitals)) {
            $html .= "<div class='vitals' >";
            $html .= "<h2>Vitals</h2>";
            $html .= "<table style='width: 50%; border-collapse: collapse;'>";
            $html .= "<tr>";
            $html .= "<th style='border: 1px solid #ddd; padding: 4px;'>Blood Pressure</th>";
            $html .= "<th style='border: 1px solid #ddd; padding: 4px;'>Heart Rate</th>";
            $html .= "<th style='border: 1px solid #ddd; padding: 4px;'>Respiration Rate</th>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<td style='border: 1px solid #ddd; padding: 4px; text-align: center'>" . ($vitals['bp'] ?? '') . "</td>";
            $html .= "<td style='border: 1px solid #ddd; padding: 4px; text-align: center'>" . ($vitals['hr'] ?? '') . "</td>";
            $html .= "<td style='border: 1px solid #ddd; padding: 4px; text-align: center'>" . ($vitals['rr'] ?? '') . "</td>";
            $html .= "</tr>";
            $html .= "</table>";
            $html .= "</div>";
        }


        // Treatment Plan
        $html .= "<div class='section' id='treatment-plan-section'>";
        $html .= "<div class='section-header'>";
        $html .= "<h2>Treatment Plan(s)</h2>";
        $html .= "</div>";
        $html .= "<div class='section-content'>";
        $html .= $planHtml;
        $html .= "</div>";
        $html .= "</div>";

        // Final Section - Assessment Summary
        $html .= $this->generateFinalSection($data['form_data'] ?? []);

        return $html;
    }

    /**
     * Generate final section with time spent, review notes, severity counts, follow-up, and provider note
     *
     * @param array $formData The form data containing final section information
     * @return string The HTML for the final section
     */
    private function generateFinalSection(array $formData): string
    {
        $html = '';

        // Start final section
        $html .= "<div class='section' id='final-section'>";
        $html .= "<div class='section-header'>";
        $html .= "<h2>Assessment Summary</h2>";
        $html .= "</div>";
        $html .= "<div class='section-content'>";

        // Time Spent
        if (!empty($formData['total_time_spent'])) {
            $html .= "<p><strong>Time Spent:</strong> " . htmlspecialchars($formData['total_time_spent']) . " minutes</p>";
        }

        // Review Notes (Clinical Activities)
        $reviewActivities = [];
        $lNumber = 0;
        if (isset($formData['review_external_notes']) && $formData['review_external_notes'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Review External Notes";
        }
        if (isset($formData['review_test_results']) && $formData['review_test_results'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Review Test Results";
        }
        if (isset($formData['order_tests']) && $formData['order_tests'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Ordering Tests";
        }

        if (isset($formData['obtain_hx']) && $formData['obtain_hx'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Obtaining History from independent historian(s)";
        }
        if (isset($formData['independent_test_another_hcp']) && $formData['independent_test_another_hcp'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Independent interpretation of a test performed by another healthcare provider";
        }
        if (isset($formData['discuss_test_outside_hcp']) && $formData['discuss_test_outside_hcp'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Discussion of management or test interpretation with outside healthcare provider";
        }
        if (isset($formData['document_time_hcp']) && $formData['document_time_hcp'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Documentation time by this Healthcare Provider";
        }
        if (isset($formData['consult_family']) && $formData['consult_family'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Counseling patient or family member";
        }
        if (isset($formData['perform_diag_procedure']) && $formData['perform_diag_procedure'] == 1) {
            $reviewActivities[] = ++$lNumber . ". Performing diagnostic procedure";
        }
        $html .= "<p><strong>Clinical Activities:</strong> <br>" . implode("<br> ", $reviewActivities) . "</p>";


        // Severity Counts Summary
        if (!empty($formData['total_severity'])) {
            $severityData = json_decode($formData['total_severity'], true);
            if ($severityData && is_array($severityData)) {
                $severityArray = new SeverityList();
                $severityText = $severityArray->severityArray();
                $severitySummary = [];

                foreach ($severityData as $type => $count) {
                    if ($count > 0) {
                        if (empty($type)) {
                            $severitySummary[] = $count . " other findings";
                        } else {
                            $severitySummary[] = $count . " " . $severityText[$type] . ($count > 1 ? "s" : "");
                        }
                    }
                }

                if (!empty($severitySummary)) {
                    $html .= "<p><strong>Condition Severity Summary:</strong> <br>" . implode(" <br>", $severitySummary) . "</p>";
                }
            }
        }

        // Follow-up Plan
        if (!empty($formData['general_derm_patient_follow_up'])) {
            $html .= "<p><strong>Follow-up Plan:</strong> " . nl2br(htmlspecialchars($formData['general_derm_patient_follow_up'])) . "</p>";
        }

        // Provider Note
        if (!empty($formData['general_derm_note'])) {
            $html .= "<p><strong>Provider Note:</strong> " . nl2br(htmlspecialchars($formData['general_derm_note'])) . "</p>";
        }

        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }
}
