<?php

namespace GeneralDermatology\Utils;

use Mpdf\Mpdf;

/**
 * PDF Layout Helper Utility Class
 *
 * Provides helper methods for PDF layout and formatting
 */
class PDFLayoutHelper
{
    /**
     * @var array PDF configuration settings
     */
    private $config;

    /**
     * @var Mpdf The mPDF instance
     */
    private $mpdf;

    /**
     * Constructor
     *
     * @param Mpdf $mpdf The mPDF instance
     */
    public function __construct(Mpdf $mpdf)
    {
        $this->mpdf = $mpdf;
        $this->config = require dirname(dirname(__DIR__)) . '/config/pdf_settings.php';
    }

    /**
     * Check if content will fit on current page, add new page if needed
     *
     * @param string $content The content to check
     * @param float $contentHeight The height of the content
     * @param float $availableSpace The available space on the current page
     * @return float The remaining available space after adding content
     */
    public function checkPageBreak(string $content, float $contentHeight, float $availableSpace): float
    {
        if ($availableSpace < $contentHeight) {
            $this->mpdf->AddPage();
            return $this->config['layout']['available_space'];
        }

        return $availableSpace - $contentHeight;
    }

    /**
     * Generate the header HTML
     *
     * @param array $data The data for the header
     * @return string The HTML for the header
     */
    public function generateHeader(array $data): string
    {
        // Extract data for the template
        $facilityName = $data['facility_name'] ?? '';
        $visitDescription = $data['visit_description'] ?? '';
        $noteDate = $data['note_date'] ?? '';
        $providerName = $data['provider_name'] ?? '';
        $patientName = $data['patient_name'] ?? '';
        $dob = $data['dob'] ?? '';
        $age = $data['age'] ?? '';
        $gender = $data['gender'] ?? '';
        $pid = $data['pid'] ?? '';

        // Start output buffering
        ob_start();

        // Include the header template
        include dirname(dirname(__DIR__)) . '/templates/header.php';

        // Get the buffer contents and clean the buffer
        return ob_get_clean();
    }

    /**
     * Generate the footer HTML
     *
     * @return string The HTML for the footer
     */
    public function generateFooter(): string
    {
        // Get facility data from config
        $website = $this->config['facility']['website'] ?? '';
        $phone = $this->config['facility']['phone'] ?? '';
        $fax = $this->config['facility']['fax'] ?? '';
        $address = $this->config['facility']['address'] ?? '';

        // Start output buffering
        ob_start();

        // Include the footer template
        include dirname(dirname(__DIR__)) . '/templates/footer.php';

        // Get the buffer contents and clean the buffer
        return ob_get_clean();
    }

    /**
     * Create a plan block with consistent formatting
     *
     * @param array $planData The plan data
     * @param int $planCounter The plan number counter
     * @return array [html, height] The HTML content and its calculated height
     */
    public function createPlanBlock(array $planData, int $planCounter): array
    {
        // Extract plan data
        $findingLocation = $planData['finding_location'] ?? '';
        $diagnosisDescription = $planData['diagnosis_description'] ?? '';
        $icdAlt = $planData['icd_alt'] ?? '';
        $diagnosisAlt = $planData['diagnosis_alt'] ?? '';
        $severity = $planData['severity'] ?? '';
        $procedurePlans = $planData['procedure_plans'] ?? '';
        $planComplete = $planData['plan_complete'] ?? '';
        $planDescription = $planData['plan_description'] ?? '';
        $biopsyLocationTitle = $planData['biopsy_location_title'] ?? '';
        $biopsyAreas = $planData['biopsy_areas'] ?? '';
        $ruleOutText = $planData['rule_out_text'] ?? '';
        $biopsyMethods = $planData['biopsy_methods'] ?? [];

        // Start output buffering
        ob_start();

        // Include the plan section template
        include dirname(dirname(__DIR__)) . '/templates/plan_section.php';

        // Get the buffer contents and clean the buffer
        $html = ob_get_clean();

        // Calculate approximate height
        $height = $this->calculatePlanHeight($planData);

        return [
            'html' => $html,
            'height' => $height
        ];
    }

    /**
     * Calculate plan height
     *
     * @param array $planData Plan data
     * @return float Approximate height in mm
     */
    private function calculatePlanHeight(array $planData): float
    {
        // Base height for the plan heading
        $height = 10;

        // Add height for each component based on content length
        $components = [
            'finding_location',
            'diagnosis_description',
            'icd_alt',
            'diagnosis_alt',
            'severity',
            'procedure_plans',
            'plan_complete',
            'plan_description',
            'biopsy_location_title',
            'biopsy_areas',
            'rule_out_text'
        ];

        foreach ($components as $component) {
            if (!empty($planData[$component])) {
                // Calculate based on text length
                $text = $planData[$component];
                $lines = TextFormatter::calculateTextHeight($text, $this->config['layout']['plan_width']);
                $height += $lines * $this->config['layout']['line_height'];
            }
        }

        // Add height for biopsy methods
        if (!empty($planData['biopsy_methods']) && is_array($planData['biopsy_methods'])) {
            $height += 5; // Base height for the label
            $methodsText = implode(', ', $planData['biopsy_methods']);
            $lines = TextFormatter::calculateTextHeight($methodsText, $this->config['layout']['plan_width']);
            $height += $lines * $this->config['layout']['line_height'];
        }

        // Add some padding
        $height += 10;

        return $height;
    }
}
