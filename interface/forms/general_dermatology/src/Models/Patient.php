<?php

namespace GeneralDermatology\Models;

use GeneralDermatology\Utils\TextFormatter;

/**
 * Patient Model Class
 * 
 * Represents patient data and provides methods to access patient information
 */
class Patient
{
    /**
     * @var array Patient data
     */
    private $data;
    
    /**
     * Constructor
     * 
     * @param array $patientData The patient data
     */
    public function __construct(array $patientData)
    {
        $this->data = $patientData;
    }
    
    /**
     * Get patient's full name
     * 
     * @return string The patient's full name
     */
    public function getFullName(): string
    {
        $fname = TextFormatter::safeGetValue($this->data, 'fname', '');
        $lname = TextFormatter::safeGetValue($this->data, 'lname', '');
        
        return trim("$fname $lname");
    }
    
    /**
     * Get patient's date of birth
     * 
     * @param string $format The date format
     * @return string The formatted date of birth
     */
    public function getDOB(string $format = 'F j, Y'): string
    {
        $dob = TextFormatter::safeGetValue($this->data, 'DOB', '');
        
        if (empty($dob)) {
            return '';
        }
        
        return TextFormatter::formatDate($dob, $format);
    }
    
    /**
     * Get patient's age
     * 
     * @return string The patient's age
     */
    public function getAge(): string
    {
        return TextFormatter::safeGetValue($this->data, 'age', '');
    }
    
    /**
     * Get patient's gender
     * 
     * @return string The patient's gender
     */
    public function getGender(): string
    {
        return TextFormatter::safeGetValue($this->data, 'sex', '');
    }
    
    /**
     * Get patient's ID
     * 
     * @return string The patient's ID
     */
    public function getPID(): string
    {
        return TextFormatter::safeGetValue($this->data, 'pid', '');
    }
    
    /**
     * Get patient's allergies
     * 
     * @return string The patient's allergies
     */
    public function getAllergies(): string
    {
        return TextFormatter::safeGetValue($this->data, 'allergies', 'None');
    }
    
    /**
     * Get a specific patient data value
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
     * Get all patient data
     * 
     * @return array The patient data
     */
    public function getAllData(): array
    {
        return $this->data;
    }
}
