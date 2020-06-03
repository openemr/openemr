<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirMedicationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirMedicationRestController
{
    private $fhirMedicationService;
    private $fhirService;
    private $fhirValidationService;
    
    public function __construct($id)
    {
        $this->fhirMedicationService = new FhirMedicationService();
        $this->fhirMedicationService->setId($id);
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }
    
    public function getAll()
    {
        $result = $this->fhirMedicationService->getAll();
        if ($result === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        $resourceURL = \RestConfig::$REST_FULL_URL;
        foreach ($result as $condition) {
            $entryResource = $this->fhirMedicationService->createMedicationResource(
                $condition['id'],
                $condition,
                false
            );
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $condition['id'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $result = $this->fhirService->createBundle('Medication', $entries, false);
        return RestControllerHelper::responseHandler($result, null, 200);
    }
    
    public function getOne($id)
    {
        $result = $this->fhirMedicationService->getOne($id);
        if ($result) {
            $resource = $this->fhirMedicationService->createMedicationResource(
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
