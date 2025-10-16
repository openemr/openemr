<?php

/**
 * Encounter Report Form Handler
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Encounter\Form;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class EncounterReportFormHandler
{
    private $logger;
    private $facilityService;
    private $userService;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->facilityService = new FacilityService();
        $this->userService = new UserService();
    }

    /**
     * Process and validate form input
     *
     * @param array $postData
     * @return array
     */
    public function processForm(array $postData): array
    {
        $this->logger->debug('EncounterReportFormHandler: Processing form data', ['post' => $postData]);

        try {
            // Validate CSRF token
            if (!CsrfUtils::verifyCsrfToken($postData['csrf_token_form'] ?? '')) {
                throw new \RuntimeException('Invalid CSRF token');
            }

            // Process and validate date range
            $formData = $this->processDateRange($postData);
            
            // Process and validate filters
            $formData = array_merge($formData, $this->processFilters($postData));
            
            // Process export settings if present
            if (isset($postData['export'])) {
                $formData['export'] = $this->processExportSettings($postData);
            }

            $this->logger->debug('EncounterReportFormHandler: Form data processed successfully', $formData);
            return $formData;
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error processing encounter report form: ' . $e->getMessage(), 
                ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Process and validate date range
     *
     * @param array $data
     * @return array
     */
    private function processDateRange(array $data): array
    {
        $formData = [];
        
        // Set default date range if not provided
        $formData['date_from'] = $data['date_from'] ?? date('Y-m-d', strtotime('-1 month'));
        $formData['date_to'] = $data['date_to'] ?? date('Y-m-d');
        
        // Validate dates
        $this->validateDate($formData['date_from'], 'date_from');
        $this->validateDate($formData['date_to'], 'date_to');
        
        // Ensure from date is before to date
        if (strtotime($formData['date_from']) > strtotime($formData['date_to'])) {
            throw new \InvalidArgumentException('Start date cannot be after end date');
        }
        
        return $formData;
    }

    /**
     * Process and validate filters
     *
     * @param array $data
     * @return array
     */
    private function processFilters(array $data): array
    {
        $filters = [];
        
        // Process facility filter
        if (!empty($data['facility'])) {
            $filters['facility_id'] = (int)$data['facility'];
            
            // Validate facility exists
            $facility = $this->facilityService->getById($filters['facility_id']);
            if (empty($facility)) {
                throw new \InvalidArgumentException('Invalid facility selected');
            }
        }
        
        // Process provider filter
        if (!empty($data['provider'])) {
            $filters['provider_id'] = (int)$data['provider'];
            
            // Validate provider exists and is authorized
            $provider = $this->userService->getUser($filters['provider_id']);
            if (empty($provider) || !$provider['authorized']) {
                throw new \InvalidArgumentException('Invalid provider selected');
            }
        }
        
        // Process detail level
        $filters['details'] = !empty($data['details']) ? 1 : 0;
        
        return $filters;
    }

    /**
     * Process export settings
     *
     * @param array $data
     * @return array
     */
    private function processExportSettings(array $data): array
    {
        $export = [
            'format' => $data['export'] ?? 'csv',
            'include_sensitive' => !empty($data['include_sensitive']),
            'columns' => $data['columns'] ?? []
        ];
        
        // Validate export format
        $validFormats = ['csv', 'pdf', 'excel'];
        if (!in_array($export['format'], $validFormats)) {
            throw new \InvalidArgumentException('Invalid export format');
        }
        
        return $export;
    }

    /**
     * Validate date string
     *
     * @param string $date
     * @param string $fieldName
     * @throws \InvalidArgumentException
     */
    private function validateDate(string $date, string $fieldName): void
    {
        if (!strtotime($date)) {
            throw new \InvalidArgumentException("Invalid date format for {$fieldName}");
        }
    }

    /**
     * Get validation rules for client-side validation
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'date_from' => [
                'required' => true,
                'date' => true,
                'messages' => [
                    'required' => 'Start date is required',
                    'date' => 'Please enter a valid start date'
                ]
            ],
            'date_to' => [
                'required' => true,
                'date' => true,
                'after_or_equal' => 'date_from',
                'messages' => [
                    'required' => 'End date is required',
                    'date' => 'Please enter a valid end date',
                    'after_or_equal' => 'End date must be after or equal to start date'
                ]
            ],
            'facility' => [
                'required' => false,
                'numeric' => true
            ],
            'provider' => [
                'required' => false,
                'numeric' => true
            ]
        ];
    }
}
