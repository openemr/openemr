<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirImmunizationRestController
{
    private $fhirImmunizationService;
    private $fhirService;
    private $fhirValidationService;
    
    public function __construct($id)
    {
        $this->fhirImmunizationService = new FhirImmunizationService();
        $this->fhirImmunizationService->setId($id);
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }
    
    public function getAll($search)
    {
        $result = $this->fhirImmunizationService->getAll(array('patient' => $search['patient']));
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
            foreach ($result as $immunization) {
                $entryResource = $this->fhirImmunizationService->createImmunizationResource(
                    $immunization['id'],
                    $immunization,
                    false
                );
                $entry = array(
                    'fullUrl' => $resourceURL . "/" . $immunization['id'],
                    'resource' => $entryResource
                );
                $entries[] = new FHIRBundleEntry($entry);
            }

            $result = $this->fhirService->createBundle('Immunization', $entries, false);
        }
        return RestControllerHelper::responseHandler($result, null, $statusCode);
    }
    
    public function getOne($id)
    {
        $result = $this->fhirImmunizationService->getOne($id);
        if ($result) {
            $resource = $this->fhirImmunizationService->createImmunizationResource(
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
