<?php
namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTOutcomeMeasuresValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['measure_name'])) {
                $processingResult->addValidationMessage('measure_name', 'required');
            }
            if (empty($dataFields['measurement_date'])) {
                $processingResult->addValidationMessage('measurement_date', 'required');
            }
        }

        return $processingResult;
    }
}
