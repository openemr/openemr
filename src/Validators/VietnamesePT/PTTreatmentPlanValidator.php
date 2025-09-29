<?php
namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTTreatmentPlanValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['plan_name'])) {
                $processingResult->addValidationMessage('plan_name', 'required');
            }
            if (empty($dataFields['diagnosis_primary'])) {
                $processingResult->addValidationMessage('diagnosis_primary', 'required');
            }
            if (empty($dataFields['start_date'])) {
                $processingResult->addValidationMessage('start_date', 'required');
            }
        }

        return $processingResult;
    }
}
