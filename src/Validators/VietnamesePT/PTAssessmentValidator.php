<?php

namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTAssessmentValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['assessment_date'])) {
                $processingResult->addValidationMessage('assessment_date', 'required');
            }
        }

        if (isset($dataFields['pain_level'])) {
            if (!is_numeric($dataFields['pain_level']) || $dataFields['pain_level'] < 0 || $dataFields['pain_level'] > 10) {
                $processingResult->addValidationMessage('pain_level', 'must be between 0 and 10');
            }
        }

        if (isset($dataFields['chief_complaint_vi']) && !empty($dataFields['chief_complaint_vi'])) {
            if (!mb_check_encoding($dataFields['chief_complaint_vi'], 'UTF-8')) {
                $processingResult->addValidationMessage('chief_complaint_vi', 'invalid UTF-8 encoding');
            }
        }

        if (isset($dataFields['status'])) {
            $validStatuses = ['draft', 'completed', 'reviewed', 'cancelled'];
            if (!in_array($dataFields['status'], $validStatuses)) {
                $processingResult->addValidationMessage('status', 'must be one of: ' . implode(', ', $validStatuses));
            }
        }

        return $processingResult;
    }
}
