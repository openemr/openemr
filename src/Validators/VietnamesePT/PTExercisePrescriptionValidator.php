<?php

namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTExercisePrescriptionValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['exercise_name'])) {
                $processingResult->addValidationMessage('exercise_name', 'required');
            }
            if (empty($dataFields['start_date'])) {
                $processingResult->addValidationMessage('start_date', 'required');
            }
            if (empty($dataFields['prescribed_by'])) {
                $processingResult->addValidationMessage('prescribed_by', 'required');
            }
        }

        if (isset($dataFields['sets_prescribed']) && $dataFields['sets_prescribed'] < 0) {
            $processingResult->addValidationMessage('sets_prescribed', 'must be positive');
        }

        if (isset($dataFields['duration_minutes']) && $dataFields['duration_minutes'] < 0) {
            $processingResult->addValidationMessage('duration_minutes', 'must be positive');
        }

        if (isset($dataFields['frequency_per_week'])) {
            if ($dataFields['frequency_per_week'] < 1 || $dataFields['frequency_per_week'] > 7) {
                $processingResult->addValidationMessage('frequency_per_week', 'must be between 1 and 7');
            }
        }

        return $processingResult;
    }
}
