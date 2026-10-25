<?php

namespace GeneralDermatology\Models;

use GeneralDermatology\Utils\TextFormatter;

/**
 * Treatment Plan Model Class
 * 
 * Represents a treatment plan with diagnosis, severity, and plan details
 */
class TreatmentPlan
{
    /**
     * @var array The raw plan data
     */
    private $planData;
    
    /**
     * Constructor
     * 
     * @param array $planData The raw plan data
     */
    public function __construct(array $planData)
    {
        $this->planData = $planData;
    }
    
    /**
     * Check if the plan is empty
     * 
     * @return bool True if the plan is empty
     */
    public function isEmpty(): bool
    {
        // Check if we have any core plan data
        return empty($this->planData['finding_location']) && 
               empty($this->planData['diagnosis_code']) && 
               empty($this->planData['diagnosis_description']) && 
               empty($this->planData['plan_complete']);
    }
    
    /**
     * Get finding location
     * 
     * @return string The finding location
     */
    public function getFindingLocation(): string
    {
        return TextFormatter::cleanText($this->planData['finding_location'] ?? '');
    }
    
    /**
     * Get diagnosis description
     * 
     * @return string The diagnosis description
     */
    public function getDiagnosisDescription(): string
    {
        return TextFormatter::cleanText($this->planData['diagnosis_description'] ?? '');
    }
    
    /**
     * Get diagnosis code
     * 
     * @return string The diagnosis code
     */
    public function getDiagnosisCode(): string
    {
        return TextFormatter::cleanText($this->planData['diagnosis_code'] ?? '');
    }
    
    /**
     * Get ICD alternate code
     * 
     * @return string The ICD alternate code
     */
    public function getICDAlt(): string
    {
        return TextFormatter::cleanText($this->planData['icd_alt'] ?? '');
    }
    
    /**
     * Get diagnosis alternate
     * 
     * @return string The diagnosis alternate
     */
    public function getDiagnosisAlt(): string
    {
        return TextFormatter::cleanText($this->planData['diagnosis_alt'] ?? '');
    }
    
    /**
     * Get severity
     * 
     * @return string The severity
     */
    public function getSeverity(): string
    {
        $severity = '';
        
        // Get severity text if available
        if (!empty($this->planData['severity'])) {
            $severity = TextFormatter::cleanText($this->planData['severity']);
        }
        
        return $severity;
    }
    
    /**
     * Get procedure plans
     * 
     * @return string The procedure plans
     */
    public function getProcedurePlans(): string
    {
        return TextFormatter::cleanText($this->planData['procedure_plans'] ?? '');
    }
    
    /**
     * Get plan complete
     * 
     * @return string The plan complete
     */
    public function getPlanComplete(): string
    {
        return TextFormatter::cleanText($this->planData['plan_complete'] ?? '');
    }
    
    /**
     * Get plan description
     * 
     * @return string The plan description
     */
    public function getPlanDescription(): string
    {
        return TextFormatter::cleanText($this->planData['plan_description'] ?? '');
    }
    
    /**
     * Get medication plans
     * 
     * @return string The medication plans
     */
    public function getMedicationPlans(): string
    {
        return TextFormatter::cleanText($this->planData['medication_plans'] ?? $this->planData['medication'] ?? '');
    }
    
    /**
     * Get patient instructions
     * 
     * @return string The patient instructions
     */
    public function getPatientInstructions(): string
    {
        return TextFormatter::cleanText($this->planData['patient_instructions'] ?? '');
    }
    
    /**
     * Get notes
     * 
     * @return string The notes
     */
    public function getNotes(): string
    {
        return TextFormatter::cleanText($this->planData['notes'] ?? '');
    }
    
    /**
     * Get biopsy location title
     * 
     * @return string The biopsy location title
     */
    public function getBiopsyLocationTitle(): string
    {
        return TextFormatter::cleanText($this->planData['biopsy_location_title'] ?? '');
    }
    
    /**
     * Get biopsy areas
     * 
     * @return string The biopsy areas
     */
    public function getBiopsyAreas(): string
    {
        return TextFormatter::cleanText($this->planData['biopsy_areas'] ?? '');
    }
    
    /**
     * Get rule out text
     * 
     * @return string The rule out text
     */
    public function getRuleOutText(): string
    {
        return TextFormatter::cleanText($this->planData['rule_out_text'] ?? '');
    }
    
    /**
     * Get biopsy methods
     * 
     * @return array The biopsy methods
     */
    public function getBiopsyMethods(): array
    {
        $biopsyMethods = [];
        
        // Check for biopsy methods
        if (!empty($this->planData['biopsy_shave']) && $this->planData['biopsy_shave'] == 1) {
            $biopsyMethods[] = 'Shave';
        }
        
        if (!empty($this->planData['biopsy_punch']) && $this->planData['biopsy_punch'] == 1) {
            $biopsyMethods[] = 'Punch';
        }
        
        if (!empty($this->planData['biopsy_excisional']) && $this->planData['biopsy_excisional'] == 1) {
            $biopsyMethods[] = 'Excisional';
        }
        
        if (!empty($this->planData['biopsy_incisional']) && $this->planData['biopsy_incisional'] == 1) {
            $biopsyMethods[] = 'Incisional';
        }
        
        return $biopsyMethods;
    }
    
    /**
     * Get all plan data
     * 
     * @return array All plan data
     */
    public function getAllData(): array
    {
        return $this->planData;
    }
}
