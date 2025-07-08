<?php

/**
 * package   OpenEMR
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * author-AI Gemini, Cascade, and ChatGPT
 * All rights reserved
 * Copyright (c) 2025.
 */

namespace OpenEMR\Reports\Encounter;

class EncounterReportFormHandler
{
    public function processForm(array $formData)
    {
        $filters = [];

        if (isset($formData['date_from']) && $this->validateDate($formData['date_from'])) {
            $filters['date_from'] = $formData['date_from'];
        }

        if (isset($formData['date_to']) && $this->validateDate($formData['date_to'])) {
            $filters['date_to'] = $formData['date_to'];
        }

        if (isset($formData['facility']) && is_numeric($formData['facility'])) {
            $filters['facility'] = (int) $formData['facility'];
        } elseif (isset($formData['facility']) && $formData['facility'] === 'all') {
            $filters['facility'] = 'all';
        }

        if (isset($formData['provider']) && is_numeric($formData['provider'])) {
            $filters['provider'] = (int) $formData['provider'];
        } elseif (isset($formData['provider']) && $formData['provider'] === 'all') {
            $filters['provider'] = 'all';
        }

        if (isset($formData['patient_id']) && is_numeric($formData['patient_id'])) {
            $filters['patient_id'] = (int) $formData['patient_id'];
        }

        if (isset($formData['details']) && !empty($formData['details'])) {
            $filters['details'] = $formData['details'];
        }

        // ... Add other form fields and validation ...

        return $filters;
    }

    private function validateDate($date): bool
    {
        if ($GLOBALS['date_display_format'] == 0) {
            return checkdate(substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
        } elseif ($GLOBALS['date_display_format'] == 1) {
            return checkdate(substr($date, 0, 2), substr($date, 3, 2), substr($date, 6, 4));
        } else {
            return checkdate(substr($date, 3, 2), substr($date, 0, 2), substr($date, 6, 4));
        }
    }

    // Add other validation methods as needed.
}
