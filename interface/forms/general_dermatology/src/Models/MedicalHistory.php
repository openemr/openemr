<?php

namespace GeneralDermatology\Models;

use GeneralDermatology\Utils\TextFormatter;

/**
 * Medical History Model Class
 * 
 * Represents a patient's medical history data
 */
class MedicalHistory
{
    /**
     * @var array Medical history data
     */
    private $data;
    
    /**
     * Constructor
     * 
     * @param array $historyData The medical history data
     */
    public function __construct(array $historyData)
    {
        $this->data = $historyData;
    }
    
    /**
     * Get general medical history
     * 
     * @return string The medical history
     */
    public function getGeneralHistory(): string
    {
        return TextFormatter::safeGetValue($this->data, 'Check', 'None');
    }
    
    /**
     * Get surgical history
     * 
     * @return string The surgical history
     */
    public function getSurgicalHistory(): string
    {
        return TextFormatter::safeGetValue($this->data, 'surgical_history', 'None');
    }
    
    /**
     * Get skin disease history
     * 
     * @return string The skin disease history
     */
    public function getSkinDiseaseHistory(): string
    {
        return TextFormatter::safeGetValue($this->data, 'skin_disease_history', 'None');
    }
    
    /**
     * Get skin cancer history
     * 
     * @return string The skin cancer history
     */
    public function getSkinCancerHistory(): string
    {
        return TextFormatter::safeGetValue($this->data, 'skin_cancer_history', 'None');
    }
    
    /**
     * Get alerts
     * 
     * @return string The alerts
     */
    public function getAlerts(): string
    {
        return TextFormatter::safeGetValue($this->data, 'alerts', 'None');
    }
    
    /**
     * Get medications
     * 
     * @return string The medications
     */
    public function getMedications(): string
    {
        return TextFormatter::safeGetValue($this->data, 'medications', 'None');
    }
    
    /**
     * Get review of systems
     * 
     * @return string The review of systems
     */
    public function getReviewOfSystems(): string
    {
        return TextFormatter::safeGetValue($this->data, 'review_of_systems', 'None');
    }
    
    /**
     * Get previous diagnoses
     * 
     * @return string The previous diagnoses
     */
    public function getPreviousDiagnoses(): string
    {
        return TextFormatter::safeGetValue($this->data, 'previous_diagnoses', 'None');
    }
    
    /**
     * Get a specific history data value
     * 
     * @param string $key The key to retrieve
     * @param mixed $default The default value if not found
     * @return mixed The value or default
     */
    public function getValue(string $key, $default = 'None')
    {
        return TextFormatter::safeGetValue($this->data, $key, $default);
    }
    
    /**
     * Get all history data
     * 
     * @return array The history data
     */
    public function getAllData(): array
    {
        return $this->data;
    }
}
