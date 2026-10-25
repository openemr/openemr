<?php

namespace GeneralDermatology\Services;

use GeneralDermatology\Catalog\ProcedureCatalog;
use GeneralDermatology\Repository\GeneralDermatologyRepository;
use OpenEMR\Common\Database\QueryUtils;

use GeneralDermatology\Utils\TextFormatter;
use GeneralDermatology\Models\TreatmentPlan;
use GeneralDermatology\App\FavoriteDiagnosisCodes;
use GeneralDermatology\App\ProcedurePlans;
use GeneralDermatology\App\SeverityList;
use GeneralDermatology\Services\Info\ProcedureInfoProcessorFactory;

/**
 * Treatment Plan Service Class
 *
 * Handles retrieval and processing of treatment plan data
 */
class TreatmentPlanService
{
    /**
     * @var array PDF configuration settings
     */
    private $config;

    /**
     * @var array Diagnosis codes mapping
     */
    private $diagnosisCodes;

    /**
     * @var array Severity display mapping
     */
    private $severityArray;

    /**
     * @var array Procedure plan key-to-display-name mapping
     */
    private $procedurePlanNames;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load configuration if needed
        $this->config = [
            'fontHeight' => 4,  // Approximate height of one line of text
            'planSpacing' => 8, // Approximate spacing after each plan
            'textMaxWidth' => 100, // Maximum width for text wrapping
            'pageHeight' => 297, // A4 page height in mm (approximate)
            'pageWidth' => 210, // A4 page width in mm
            'marginSpace' => 80  // Space used by margins, headers, etc.
        ];

        // Initialize diagnosis codes if available
        if (class_exists('GeneralDermatology\App\FavoriteDiagnosisCodes')) {
            $code = new FavoriteDiagnosisCodes();
            $this->diagnosisCodes = $code->codeArray();
        } else {
            $this->diagnosisCodes = [];
        }

        // Initialize severity array if available
        if (class_exists('GeneralDermatology\App\SeverityList')) {
            $severityList = new SeverityList();
            $this->severityArray = $severityList->severityArray();
        } else {
            $this->severityArray = [];
        }

        // Initialize procedure plan names for key-to-display lookup
        $procedurePlansObj = new ProcedurePlans();
        $this->procedurePlanNames = $procedurePlansObj->getPlans();
    }

    /**
     * Get treatment plans from form data
     *
     * @param int $formId The form ID
     * @return array Treatment plans
     */
    public function getTreatmentPlans(int $formId): array
    {
        $repository = new GeneralDermatologyRepository();
        // Return UI/PDF-facing findings list (supports legacy v1 and v2 storage).
        return $repository->getTreatmentFindings($formId);
    }

    /**
     * Process plans for PDF generation
     *
     * @param array $plans Array of TreatmentPlan objects
     * @return array Formatted plans for PDF
     */
    public function processPlansForPDF(array $plans): array
    {
        $formattedPlans = [];
        $planCounter = 1;

        foreach ($plans as $plan) {
            // Skip empty plans
            if ($plan->isEmpty()) {
                continue;
            }

            // Format plan data for PDF
            $formattedPlans[] = [
                'plan_number' => $planCounter++,
                'plan_data' => [
                    'finding_location' => $plan->getFindingLocation(),
                    'diagnosis_description' => $plan->getDiagnosisDescription(),
                    'icd_alt' => $plan->getICDAlt(),
                    'diagnosis_alt' => $plan->getDiagnosisAlt(),
                    'severity' => $plan->getSeverity(),
                    'procedure_plans' => $plan->getProcedurePlans(),
                    'plan_complete' => $plan->getPlanComplete(),
                    'plan_description' => $plan->getPlanDescription(),
                    'rule_out_text' => $plan->getRuleOutText(),
                    'biopsy_location_title' => $plan->getBiopsyLocationTitle(),
                    'biopsy_areas' => $plan->getBiopsyAreas(),
                    'biopsy_methods' => $plan->getBiopsyMethods(),
                ]
            ];
        }

        return $formattedPlans;
    }

    /**
     * Get examination data from form data
     *
     * @param int $formId The form ID
     * @return array The examination data
     */
    public function getExaminationData(int $formId): array
    {
        // Canonical table only (form_general_dermatology is deprecated).
        $repository = new GeneralDermatologyRepository();
        $formData = $repository->findById($formId) ?? [];

        // Initialize examination data
        $examinationData = [
            'exams' => '',
            'exams_observation_1' => '',
            'exams_observation_2' => '',
            'exams_observation_3' => '',
            'exams_observation_text' => '',
            'lymph_nodes' => [],
            'vitals' => [],
            'orientation' => [],
            'effect' => ''
        ];

        // Process examination fields from the form data

        // Sites examined
        $siteFields = [
            'scalp', 'hair', 'face', 'externalears', 'eyelids', 'lips', 'nose',
            'neck', 'chest', 'shoulders', 'back', 'arms', 'forearms', 'hands',
            'fingernails', 'legs', 'feet', 'toenails', 'groin', 'externalgenitalia',
            'genitalmucosa', 'conjunctivalmucosa', 'oralmucosa', 'abdomen',
            'buttocks', 'axillae'
        ];

        $sites = [];
        foreach ($siteFields as $field) {
            if (!empty($formData[$field])) {
                $sites[] = ucfirst(str_replace('_', ' ', $field));
            }
        }

        $examinationData['exams'] = !empty($sites) ? implode(', ', $sites) : 'None';

        // Lymph nodes
        $lymphNodeFields = [
            'cervical', 'postauricular', 'occipital', 'submandibular',
            'supraclavicular', 'axillary', 'inguinal', 'popliteal'
        ];

        $lymphNodes = [];
        foreach ($lymphNodeFields as $field) {
            if (!empty($formData[$field])) {
                $lymphNodes[] = ucfirst($field);
            }
        }

        $examinationData['lymph_nodes'] = $lymphNodes;

        // Vitals
        if (!empty($formData['vital_bp'])) {
            $examinationData['vitals']['bp'] = $formData['vital_bp'];
        }

        if (!empty($formData['vital_hr'])) {
            $examinationData['vitals']['hr'] = $formData['vital_hr'];
        }

        if (!empty($formData['vital_rr'])) {
            $examinationData['vitals']['rr'] = $formData['vital_rr'];
        }

        // Orientation
        $orientationFields = ['time', 'place', 'person'];
        $orientation = [];

        foreach ($orientationFields as $field) {
            if (!empty($formData[$field])) {
                $orientation[] = ucfirst($field);
            }
        }

        $examinationData['orientation'] = $orientation;

        // Effect
        $effectFields = ['alert', 'uncomfortable', 'anxious', 'flat'];
        $effects = [];

        foreach ($effectFields as $field) {
            if (!empty($formData[$field])) {
                $effects[] = ucfirst($field);
            }
        }

        $examinationData['effect'] = !empty($effects) ? implode(', ', $effects) : '';

        // Observations
//        if (!empty($formData['general_derm_note'])) {
//            $examinationData['exams_observation_text'] = $formData['general_derm_note'];
//        }

        return $examinationData;
    }

    /**
     * Generate HTML for treatment plans with proper page breaking
     *
     * @param array $plans Array of treatment plan data
     * @param array $options Optional configuration options
     * @return array Array containing HTML blocks for plans and any additional data
     */
    public function generatePlanHtml(array $plans, array $options = []): array
    {
        // Merge default options with provided options
        $config = array_merge($this->config, $options);

        // Initialize variables
        $planHtmlBlocks = [];
        $currentPlanBlock = "";
        $planCounter = 1;
        $availableSpace = $config['pageHeight'] - $config['marginSpace']; // Initial available space
        $fontHeight = $config['fontHeight'];
        $planSpacing = $config['planSpacing'];
        $textMaxWidth = $config['textMaxWidth'];


        // Return empty array if no plans
        if (empty($plans)) {
            return ['planHtmlBlocks' => [], 'planCounter' => 0];
        }

        // Process each plan
        foreach ($plans as $treatment) {
            // Skip if plan is not an array
            if (!is_array($treatment)) {
                continue;
            }

            // Initialize variables for this plan
            $findingLocation = "";
            $dxDesc = "";
            $dxOther = "";
            $icdAlt = "";
            $dxAlt = "";
            $severity = "";
            $planComplete = "";
            $procedurePlans = "";
            $planDesc = "";
            $biopsyLocationTitle = "";

            // Extract field values using exact same approach as printable_.php
            if (isset($treatment['finding_location'])) {
                $findingLocation = text($treatment['finding_location']);
            } else if (isset($treatment['Finding_location'])) {
                $findingLocation = text($treatment['Finding_location']);
            }

            // Handle diagnosis code lookup
            if (isset($treatment['dx_s']) || isset($treatment['Dx_s'])) {
                $dxCode = isset($treatment['dx_s']) ? $treatment['dx_s'] : $treatment['Dx_s'];
                if (!empty($this->diagnosisCodes) && isset($this->diagnosisCodes[$dxCode])) {
                    $dxDesc = text($this->diagnosisCodes[$dxCode]);
                } else {
                    // Fallback if code not found
                    $dxDesc = text($dxCode);
                }
            }

            // Get ICD alternative text
            if (isset($treatment['icd_alt'])) {
                $icdAlt = text($treatment['icd_alt']);
            } else if (isset($treatment['Icd_alt'])) {
                $icdAlt = text($treatment['Icd_alt']);
            }

            // Get diagnosis description
            if (isset($treatment['dx_desc'])) {
                $dxOther = text($treatment['dx_desc']);
            } else if (isset($treatment['Dx_desc'])) {
                $dxOther = text($treatment['Dx_desc']);
            }

            // Get diagnosis alternative
            if (!empty($treatment['dx_alt'])) {
                $dxAlt = "<b>Description: </b>" . text($treatment['dx_alt']);
            } else if (!empty($treatment['Dx_alt'])) {
                $dxAlt = "<b>Description: </b>" . text($treatment['Dx_alt']);
            }

            // Get severity
            if (isset($treatment['severity']) || isset($treatment['severity_drop']) || isset($treatment['Severity_drop'])) {
                $key = $treatment['severity_drop'] ?? $treatment['Severity_drop'] ?? '';
                $severityKeyIndex = new SeverityList();
                $severities = $severityKeyIndex->severityArray();
                $selectedSeverity = $severities[$key];
                $severity = text($selectedSeverity);
            }

            // Get treatment plan
            if (isset($treatment['plan'])) {
                $planComplete = text($treatment['plan']);
            } else if (isset($treatment['Plan'])) {
                $planComplete = text($treatment['Plan']);
            }

            // Get procedure plans
            // Keep a clean procedure type for factory lookup (no HTML)
            $procedureType = '';
            if (isset($treatment['procedure_plans'])) {
                $procedureType = $treatment['procedure_plans'];
            } else if (isset($treatment['Procedure_plans'])) {
                $procedureType = $treatment['Procedure_plans'];
            }

            // Resolve the display name from the key (e.g. "Electro" → "Electrodesiccation and Curettage (ED&C)")
            if (!empty($procedureType)) {
                $displayName = ProcedureCatalog::labelFor((string) $procedureType);
                $procedurePlans = "<br>" . text($displayName);
            }

            // Get plan description
            if (isset($treatment['plan_desc'])) {
                $planDesc = text($treatment['plan_desc']); // - Right
            } else if (isset($treatment['Plan_desc'])) {
                $planDesc = text($treatment['Plan_desc']); // . " - Left";
            }



            // Get biopsy location title
//            if (isset($treatment['biopsy_bio_location_title'])) {
//                $biopsyLocationTitle = text($treatment['biopsy_bio_location_title']);
//            } else if (isset($treatment['Biopsy_bio_location_title'])) {
//                $biopsyLocationTitle = text($treatment['Biopsy_bio_location_title']);
//            }

            // Start building the plan HTML
            // Use div with page-break-inside: avoid for proper page breaking
            $planItemHtml = "<div class='plan-item' style='page-break-inside: avoid;'>";
            $planItemHtml .= "<h4>Plan #" . text($planCounter) . ":</h4><p>";
            $planItemHeight = $fontHeight; // Start with the height of the heading

            // Add finding location if available
            if (!empty($findingLocation)) {
                $planItemHtml .= "<b>Location: </b>" . $findingLocation . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($findingLocation, $fontHeight, $textMaxWidth);
            }

            // Add diagnosis description if available
            if (!empty($dxDesc)) {
                $planItemHtml .= "<b>Diagnosis: </b>" . ucfirst($dxDesc) . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($dxDesc, $fontHeight, $textMaxWidth);
            }

            // Add ICD alternative if available
            if (!empty($icdAlt)) {
                $planItemHtml .= ucfirst($icdAlt) . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($icdAlt, $fontHeight, $textMaxWidth);
            }

            // Add other diagnosis info if available
            if (!empty($dxOther)) {
                $planItemHtml .= "Other Diagnosis: " . ucfirst($dxOther) . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($dxOther, $fontHeight, $textMaxWidth);
            }

            // Add alternative diagnosis if available
            if (!empty($dxAlt)) {
                $planItemHtml .= ucfirst($dxAlt) . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($dxAlt, $fontHeight, $textMaxWidth);
            }

            // Add severity if available
            if (!empty($severity)) {
                // Check if we have the severity display array
                if (!empty($this->severityArray) && isset($this->severityArray[$severity])) {
                    $severityDisplay = $this->severityArray[$severity];
                } else {
                    $severityDisplay = $severity;
                }
                $planItemHtml .= $severityDisplay . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($severityDisplay, $fontHeight, $textMaxWidth);
            }

            // Close paragraph if plan complete is available
            if (!empty($planComplete) && empty($procedurePlans)) {
                $planItemHtml .= nl2br($planComplete);
                $planItemHeight += TextFormatter::calculateTextHeight($planComplete, $fontHeight, $textMaxWidth);
            }
            $planItemHtml .= "</p>";

            // Add procedure plans if available
            if (!empty($procedurePlans)) {
                $planItemHtml .= "<b>" . $procedurePlans . "</b><br>"; //Joker markers to see where the procedure plans start in the printout
                if (!empty($planComplete)) {
                    $planItemHtml .= nl2br($planComplete) . "<br>";
                }
                $planItemHeight += TextFormatter::calculateTextHeight($procedurePlans . " " . $planComplete, $fontHeight, $textMaxWidth);
            }
            // Add plan description if available
            if (!empty($planDesc)) {
                $planItemHtml .= $planDesc . "<br>";
                $planItemHeight += TextFormatter::calculateTextHeight($planDesc, $fontHeight, $textMaxWidth);
            }
            // Add biopsy location title if available
            if (!empty($biopsyLocationTitle)) {
                $planItemHtml .= "<b> Location: " . $biopsyLocationTitle . "</b><br>";
                $planItemHeight += TextFormatter::calculateTextHeight($biopsyLocationTitle, $fontHeight, $textMaxWidth);
            }

            // POLYMORPHIC PROCESSOR INTEGRATION
            // Use factory to create the appropriate processor for this procedure type
            if (!empty($procedureType)) {
                try {
                    $processor = ProcedureInfoProcessorFactory::create($procedureType);
                    $procedureFields = $processor->extractFields($treatment);
                    $procedureHtml = $processor->formatForDisplay($procedureFields);
                    if (!empty($procedureHtml)) {
                        $planItemHtml .= $procedureHtml . "<br>";
                    }
                } catch (\Throwable $e) {
                    // Log error but continue processing — catch Throwable to handle TypeError/Error too
                    error_log("Error processing procedure details for {$procedureType}: " . $e->getMessage());
                }
            }

            // Close the plan item div
            $planItemHtml .= "</div><br>";

            // Add spacing after the plan
            $planItemHeight += $planSpacing;

            // Check if we need a new page
            if ($availableSpace < $planItemHeight && $currentPlanBlock != "") {
                $planHtmlBlocks[] = $currentPlanBlock;
                $availableSpace = $config['pageHeight'] - $config['marginSpace']; // Reset available space
                $currentPlanBlock = "";
            }

            // Add this plan to the current block
            $currentPlanBlock .= $planItemHtml;
            $availableSpace -= $planItemHeight;
            $planCounter++;
        }

        // If we have any remaining plan blocks, add them
        if ($currentPlanBlock != "") {
            $planHtmlBlocks[] = $currentPlanBlock;
        }

        return [
            'planHtmlBlocks' => $planHtmlBlocks,
            'planCounter' => $planCounter - 1, // Return the actual number of plans processed
            'html' => implode("\n", $planHtmlBlocks) // Return combined HTML for convenience
        ];
    }

    /**
     * Estimate the rendered height of a text string in millimetres.
     *
     * Calculates the number of lines needed to display the text within
     * the given column width and multiplies by the per-line font height.
     *
     * @param string $text        The text to measure (HTML tags are stripped).
     * @param float  $fontHeight  Height of a single line of text in mm.
     * @param float  $textMaxWidth Maximum column width in mm.
     * @return float              Estimated height in mm.
     */
    private function calculateTextHeight(string $text, float $fontHeight, float $textMaxWidth): float
    {
        $plain = strip_tags($text);
        if ($plain === '') {
            return 0.0;
        }

        // Approximate: 1 character ≈ 2 mm wide at default PDF font size.
        $charsPerLine = max(1, (int) ($textMaxWidth / 2));
        $lines = (int) ceil(mb_strlen($plain) / $charsPerLine);

        return $lines * $fontHeight;
    }
}
