<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirMedicationStatementService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirMedicationStatementRestController
{
    private $fhirMedicationStatementService;
    private $fhirService;
    private $fhirValidationService;

    public function __construct($id)
    {
        $this->fhirMedicationStatementService = new FhirMedicationStatementService();
        $this->fhirMedicationStatementService->setId($id);
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }
    
    public function getAll($search)
    {
        $result = $this->fhirMedicationStatementService->getAll(array('patient' => $search['patient']));
        if (!$result) {
            $statusCode = 400;
            $result = $this->fhirValidationService->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Invalid Parameter"
            );
        } else {
            $statusCode = 200;
            $entries = array();
            $resourceURL = \RestConfig::$REST_FULL_URL;
            foreach ($result as $statement) {
                $entryResource = $this->fhirMedicationStatementService->createMedicationStatementResource(
                    $statement['id'],
                    $statement,
                    false
                );
                $entry = array(
                    'fullUrl' => $resourceURL . "/" . $statement['id'],
                    'resource' => $entryResource
                );
                $entries[] = new FHIRBundleEntry($entry);
            }
            $result = $this->fhirService->createBundle('MedicationStatement', $entries, false);
        }
        return RestControllerHelper::responseHandler($result, null, $statusCode);
    }
    
    public function getOne($id)
    {
        $result = $this->fhirMedicationStatementService->getOne($id);
        if ($result) {
            $resource = $this->fhirMedicationStatementService->createMedicationStatementResource(
                $result['id'],
                $result,
                false
            );
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $resource = $this->fhirValidationService->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Resource Id $id does not exist"
            );
        }

        return RestControllerHelper::responseHandler($resource, null, $statusCode);
    }
}
