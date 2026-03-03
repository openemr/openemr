<?php

namespace OpenEMR\Services\FHIR;


class FhirValidationService
{
    public function validate($data)
    {
        if (!array_key_exists('resourceType', $data)) {
            return $this->operationOutcomeResourceService('error', 'invalid', 'resourceType Not Found');
        }
        if ($data['resourceType']) {
            $class = 'OpenEMR\FHIR\R4\FHIRDomainResource\FHIR' . $data['resourceType'];
            unset($data['resourceType']);
            try {
                $patientResource = new $class($data);
            } catch (\InvalidArgumentException $e) {
                return $this->
                operationOutcomeResourceService('fatal', 'invalid', $e->getMessage());
            } catch (\Error) {
                return $this->
                operationOutcomeResourceService('fatal', 'invalid', 'resourceType Not Found');
            }
            $diff = array_diff_key($data, (array) $patientResource);
            if ($diff) {
                return $this->operationOutcomeResourceService(
                    'error',
                    'invalid',
                    "Invalid content " . array_key_first($diff) . " Found",
                );
            }
        }
    }

    public function operationOutcomeResourceService(
        $severity_value,
        $code_value,
        $details_value
    ) {
        return UtilsService::createOperationOutcomeResource($severity_value, $code_value, $details_value);
    }
}
