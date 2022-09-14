<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\Services\QuestionnaireTraits;

class FhirValidationService
{
    use QuestionnaireTraits;

    public function validate($data)
    {
        if (!array_key_exists('resourceType', $data)) {
            return $this->operationOutcomeResourceService('error', 'invalid', false, 'resourceType Not Found');
        }
        if ($data['resourceType']) {
            $class = 'OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIR' . $data['resourceType'];
            unset($data['resourceType']);
            try {
                $patientResource = new $class($data);
            } catch (\InvalidArgumentException $e) {
                return $this->
                operationOutcomeResourceService('fatal', 'invalid', false, $e->getMessage());
            } catch (\Error $e) {
                return $this->operationOutcomeResourceService('fatal', 'invalid', false, 'resourceType Not Found');
            }
            $diff = array_diff_key($data, $this->fhirObjectToArray($patientResource));
            if ($diff) {
                return $this->operationOutcomeResourceService(
                    'error',
                    'invalid',
                    "Invalid content " . array_key_first($diff) . " Found",
                    false
                );
            }
        }
    }

    public function operationOutcomeResourceService(
        $severity_value,
        $code_value,
        $encode = true,
        $details_value = '',
        $diagnostics_value = '',
        $expression = null
    ) {
        $resource = new FHIROperationOutcome();
        $issue = new FHIROperationOutcomeIssue();
        $severity = new FHIRIssueSeverity();
        $severity->setValue($severity_value);
        $issue->setSeverity($severity);
        $code = new FHIRIssueType();
        $code->setValue($code_value);
        $issue->setCode($code);
        if ($details_value) {
            $details = new FHIRCodeableConcept();
            $details->setText($details_value);
            $issue->setDetails($details);
        }
        if ($diagnostics_value) {
            $diagnostics = new FHIRString();
            $diagnostics->setValue($diagnostics_value);
            $issue->setDiagnostics($diagnostics);
        }
        if ($expression_value ?? null) {
            $expression = new FHIRCodeableConcept();
            $expression->setText($expression_value);
            $issue->setExpression($expression);
        }
        $resource->addIssue($issue);
        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
